"""Prueba HTTP local con datos temporales; usa solo la biblioteca estándar de Python."""
import base64
import http.cookiejar
import json
import re
import subprocess
import urllib.error
import urllib.parse
import urllib.request
import uuid
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BASE = 'http://sistematopsa-new.test'
REFERENCE = 'TEST-HTTP-' + uuid.uuid4().hex


def php(code, *args):
    return subprocess.check_output(['php', '-r', code, *map(str, args)], cwd=ROOT, text=True)


def flatten(data, prefix=''):
    pairs = []
    if isinstance(data, dict):
        for key, value in data.items():
            pairs.extend(flatten(value, f'{prefix}[{key}]' if prefix else key))
    elif isinstance(data, list):
        for index, value in enumerate(data):
            pairs.extend(flatten(value, f'{prefix}[{index}]'))
    else:
        pairs.append((prefix, str(data)))
    return pairs


opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))


def request(path, data=None, headers=None):
    try:
        response = opener.open(urllib.request.Request(BASE + path, data=data, headers=headers or {}))
    except urllib.error.HTTPError as error:
        response = error
    return response.status, response.read(), response.url


def post(path, data):
    return request(path, urllib.parse.urlencode(flatten(data)).encode())


fixture = json.loads(php('echo json_encode(require "tests/fixtures/apaneca.php");'))
project = php('require "core/Database.php"; echo Core\\Database::getInstance()->query("SELECT id_proyecto FROM proyectos ORDER BY id_proyecto LIMIT 1")->fetchColumn();').strip()
assert project, 'Se necesita un proyecto local.'
status, content, _ = request('/valuo/create')
assert status == 200
token = re.search(rb'name="_csrf_token" value="([^"]+)"', content).group(1).decode()
data = dict(fixture, _csrf_token=token, referencia=REFERENCE, id_proyecto=project, fecha_del_valuo='2026-05-28', estado='Borrador', revision='0')
created = None
try:
    status, body, _ = post('/valuo/calcular', dict(data, _csrf_token='invalido'))
    assert status == 403
    status, body, _ = post('/valuo/calcular', data)
    assert status == 200 and abs(json.loads(body)['resultados']['mercado'] - 60324.57233706321) < 0.000001
    status, body, _ = post('/valuo/store', dict(data, estado='Revisado'))
    assert status == 422 and REFERENCE.encode() in body
    status, body, url = post('/valuo/store', data)
    assert status == 200 and '/valuo/show/' in url, body[:500]
    created = int(url.rsplit('/', 1)[1])
    assert b'$60,324.57' in body and b'$30,000.00' in body
    status, body, _ = post(f'/valuo/update/{created}', dict(data, revision='1', area_inspeccion='0'))
    assert status == 422 and REFERENCE.encode() in body and b'132.251' in body, 'Se perdieron datos al validar.'
    status, body, _ = post(f'/valuo/update/{created}', dict(data, revision='1', valor_adoptado='62000'))
    assert status == 200 and b'$31,000.00' in body
    status, body, _ = post(f'/valuo/update/{created}', dict(data, revision='1'))
    assert status == 422, 'Se aceptó una revisión antigua.'
    boundary = 'Boundary' + uuid.uuid4().hex
    png = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aMZ0AAAAASUVORK5CYII=')
    multipart = (
        f'--{boundary}\r\nContent-Disposition: form-data; name="_csrf_token"\r\n\r\n{token}\r\n'
        f'--{boundary}\r\nContent-Disposition: form-data; name="descripcion"\r\n\r\nImagen de prueba\r\n'
        f'--{boundary}\r\nContent-Disposition: form-data; name="anexo"; filename="prueba.png"\r\nContent-Type: image/png\r\n\r\n'
    ).encode() + png + f'\r\n--{boundary}--\r\n'.encode()
    status, body, _ = request(f'/valuo/upload/{created}', multipart, {'Content-Type': f'multipart/form-data; boundary={boundary}'})
    assert status == 200 and b'prueba.png' in body, body[:500]
    attachment = re.search(rb'valuo/attachment/(\d+)', body).group(1).decode()
    status, body, _ = request('/valuo/attachment/' + attachment)
    assert status == 200 and body == png
    status, body, _ = request(f'/valuo/report/{created}')
    assert status == 200 and b'Comparable 3' in body and b'$60,324.57' in body and b'Imagen de prueba' in body
    print('OK: CSRF, cálculo HTTP, revisión, alta, validación sin pérdida, edición, concurrencia, anexo y reporte.')
finally:
    if created is not None:
        php(r'''
require "core/Database.php";
$db=Core\Database::getInstance();
$id=(int)$argv[1]; $ref=$argv[2];
if (!$db->query("SELECT id_valuo FROM valuo_expedientes WHERE id_valuo=:id AND referencia=:ref", [":id"=>$id,":ref"=>$ref])->fetch()) { exit(1); }
$root=realpath("storage/valuos");
foreach ($db->query("SELECT archivo FROM valuo_anexos WHERE id_valuo=:id", [":id"=>$id])->fetchAll() as $a) {
    $path=realpath("storage/valuos/".basename($a["archivo"]));
    if ($path && $root && str_starts_with($path, $root.DIRECTORY_SEPARATOR)) { unlink($path); }
}
$db->query("DELETE FROM valuos WHERE id_valuo=:id", [":id"=>$id]);
$db->query("DELETE FROM bitacora WHERE tabla_afectada=:tabla AND id_registro=:id AND accion=:accion", [":tabla"=>"valuos",":id"=>$id,":accion"=>"Guardó expediente de avalúo #".$id]);
''', created, REFERENCE)
