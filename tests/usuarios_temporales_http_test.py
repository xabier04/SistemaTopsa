"""Pruebas locales del ciclo de credenciales, sin enviar correos ni WhatsApp."""
import http.cookiejar
import json
import re
import subprocess
import urllib.parse
import urllib.request
import uuid
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BASE = 'http://sistematopsa-new.test'
REF = 'Prueba ' + uuid.uuid4().hex
CORREO = uuid.uuid4().hex[:20] + '@topsa.com'
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

def php(code, *args):
    return subprocess.check_output(['php', '-r', code, *map(str, args)], cwd=ROOT, text=True)

def request(path, data=None):
    result = opener.open(BASE + path, None if data is None else urllib.parse.urlencode(data).encode())
    body = result.read().decode()
    assert 'Fatal error' not in body and 'Warning:' not in body, 'Error de PHP'
    return body, result.url

def token(body):
    return re.search('name="_csrf_token" value="([^"]+)"', body).group(1)

eid = uid = None
try:
    eid = int(php('require "core/Database.php"; require "core/Model.php"; require "app/Models/Empleado.php"; echo (new App\\Models\\Empleado())->create(["nombre_completo"=>$argv[1],"dui"=>sprintf("%08d-0",random_int(10000000,99999999)),"cargo"=>"Prueba","telefono"=>"7000-0000","estado"=>"Activo"]);', REF))
    body, _ = request('/usuario/create?id_empleado=' + str(eid))
    csrf = token(body)
    assert f'value="{eid}" selected' in body
    data = dict(_csrf_token=csrf, id_empleado=eid, nombre=REF, correo=CORREO, rol='Empleado', estado_de_cuenta='Activo')
    body, _ = request('/usuario/store', dict(data, correo='invalido'))
    assert REF in body and 'Corrija los campos' in body
    body, location = request('/usuario/store', data)
    assert '/usuario/credenciales/' in location
    uid = int(location.rsplit('/', 1)[1])
    temporary = re.search('id="claveTemporal"[^>]+value="([^"]+)"', body).group(1)
    assert len(temporary) == 20 and 'https://wa.me/50370000000?' in body and 'mailto:' in body
    body, _ = request('/usuario/credenciales/' + str(uid))
    assert 'id="claveTemporal"' not in body, 'Se mostró dos veces'
    saved = json.loads(php('require "core/Database.php"; echo json_encode(Core\\Database::getInstance()->query("SELECT contrasena, requiere_cambio_contrasena FROM usuarios WHERE id_usuario = ?",[$argv[1]])->fetch());', uid))
    assert saved['contrasena'] != temporary and saved['requiere_cambio_contrasena'] == 1
    change = dict(_csrf_token=csrf, correo=CORREO, actual=temporary, nueva='Nueva frase segura 2026', confirmacion='Nueva frase segura 2026')
    body, _ = request('/usuario/guardarClave', dict(change, actual='incorrecta'))
    assert 'no son válidos' in body
    body, _ = request('/usuario/guardarClave', dict(change, confirmacion='diferente'))
    assert 'no coincide' in body
    body, _ = request('/usuario/guardarClave', change)
    assert 'Contraseña cambiada correctamente' in body
    body, _ = request('/usuario/guardarClave', change)
    assert 'no son válidos' in body, 'Aceptó la contraseña anterior'
    result = php('require "core/Database.php"; $u=Core\\Database::getInstance()->query("SELECT * FROM usuarios WHERE id_usuario = ?",[$argv[1]])->fetch(); echo password_verify($argv[2],$u["contrasena"]) && !$u["requiere_cambio_contrasena"] ? "OK" : "ERROR";', uid, change['nueva'])
    assert result == 'OK'
    request('/usuario/update/' + str(uid), dict(data, contrasena='inyectada', requiere_cambio_contrasena='1'))
    result = php('require "core/Database.php"; $u=Core\\Database::getInstance()->query("SELECT * FROM usuarios WHERE id_usuario = ?",[$argv[1]])->fetch(); echo password_verify($argv[2],$u["contrasena"]) ? "OK" : "ERROR";', uid, change['nueva'])
    assert result == 'OK', 'La edición cambió la clave'
    body, _ = request('/usuario/regenerar/' + str(uid), dict(_csrf_token='incorrecto'))
    assert 'id="claveTemporal"' not in body
    body, _ = request('/usuario/regenerar/' + str(uid), dict(_csrf_token=csrf))
    regenerated = re.search('id="claveTemporal"[^>]+value="([^"]+)"', body).group(1)
    assert regenerated != temporary
    body, _ = request('/empleado/update/' + str(eid), dict(_csrf_token=csrf, nombre_completo=REF, dui='incorrecto', cargo='Cargo conservado', telefono='', estado='Activo'))
    assert REF in body and 'Cargo conservado' in body and 'Corrija los campos' in body
    print('OK: alta, contraseña temporal, vista única, enlaces, cambio, rechazo de clave anterior, edición, regeneración y CSRF.')
finally:
    php('require "core/Database.php"; $db=Core\\Database::getInstance(); $db->query("DELETE FROM bitacora WHERE (tabla_afectada = ? AND id_registro = ?) OR accion = ? OR accion = ?", ["usuarios", $argv[1], "Creó usuario: ".$argv[3], "Actualizó usuario: ".$argv[3]]); $db->query("DELETE FROM usuarios WHERE id_usuario = ?",[$argv[1]]); $db->query("DELETE FROM empleados WHERE id_empleado = ?",[$argv[2]]);', uid or 0, eid or 0, REF)
