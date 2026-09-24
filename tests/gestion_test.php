<?php
// Prueba de integración local: crea sus propios registros y los elimina en finally.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
spl_autoload_register(function ($class) {
    foreach (['Core\\' => '/core/', 'App\\Models\\' => '/app/Models/'] as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) require __DIR__ . '/..' . $dir . substr($class, strlen($prefix)) . '.php';
    }
});
function check(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
function token(string $html, string $name = '_csrf_token'): string {
    preg_match('/name="' . preg_quote($name, '/') . '" value="([^"]+)"/', $html, $m);
    check(!empty($m[1]), 'Falta campo ' . $name); return html_entity_decode($m[1]);
}
function request($handle, string $path, ?array $data = null): array {
    $base = getenv('TOPSA_TEST_URL') ?: 'http://sistematopsa-new.test';
    curl_setopt_array($handle, [CURLOPT_URL => $base . '/' . $path, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 15,
        CURLOPT_POST => $data !== null, CURLOPT_POSTFIELDS => $data !== null ? http_build_query($data) : null]);
    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $data !== null ? 'POST' : 'GET');
    $result = curl_exec($handle);
    check($result !== false, 'Error HTTP: ' . curl_error($handle));
    check(!str_contains($result, 'Fatal error') && !str_contains($result, '<b>Warning</b>'), 'Error PHP en ' . $path);
    return [curl_getinfo($handle, CURLINFO_RESPONSE_CODE), $result];
}
function login($handle, string $email, string $password): string {
    [, $html] = request($handle, 'auth/login'); $csrf = token($html);
    [$status, $html] = request($handle, 'auth/authenticate', ['_csrf_token' => $csrf, 'correo' => $email, 'contrasena' => $password]);
    check($status === 302 && str_contains($html, 'dashboard/index'), 'Login falló'); return $csrf;
}
$db = Core\Database::getInstance();
$suffix = bin2hex(random_bytes(5));
$password = bin2hex(random_bytes(12));
$admin = curl_init(); $employee = curl_init(); $anon = curl_init();
foreach ([$admin, $employee, $anon] as $h) curl_setopt($h, CURLOPT_COOKIEFILE, '');
$employees = []; $users = []; $projects = []; $client = null;
try {
    $client = (new App\Models\Cliente())->create(['nombre' => 'TEST gestión ' . $suffix, 'dui' => sprintf('%08d-0', random_int(10000000, 99999999))]);
    for ($i = 0; $i < 2; $i++) {
        $employees[] = (new App\Models\Empleado())->create(['nombre_completo' => 'TEST empleado ' . $suffix . $i, 'dui' => sprintf('%08d-0', random_int(10000000, 99999999)), 'cargo' => 'Pruebas', 'estado' => 'Activo']);
        $users[] = (new App\Models\Usuario())->createUser(['id_empleado' => $employees[$i], 'nombre' => 'TEST ' . $suffix . $i, 'correo' => "test-{$suffix}-{$i}@example.invalid", 'contrasena' => $password, 'rol' => $i === 0 ? 'Administrador' : 'Empleado', 'estado_de_cuenta' => 'Activo']);
        $projects[] = (new App\Models\Proyecto())->create(['id_cliente' => $client, 'nombre_del_proyecto' => 'TEST proyecto ' . $suffix . $i, 'fecha_de_inicio' => date('Y-m-d'), 'estado_del_proyecto' => 'En Proceso', 'presupuesto_inicial' => '100.00']);
        (new App\Models\Proyecto())->asignarEmpleado($projects[$i], $employees[$i]);
    }
    [$status] = request($anon, 'transaccion/index'); check($status === 302, 'Acceso anónimo permitido');
    $csrfAdmin = login($admin, "test-{$suffix}-0@example.invalid", $password);
    $csrfEmployee = login($employee, "test-{$suffix}-1@example.invalid", $password);
    foreach (['transaccion/index', 'usuario/index', 'proyecto/index', 'empleado/index'] as $path) {
        [$status] = request($employee, $path); check($status === 403, 'Empleado accedió a ' . $path);
    }
    [$status] = request($admin, 'usuario/rememberCredential'); check($status === 404, 'Método privado accesible');
    [$status] = request($admin, 'proyecto/getRequiredRole'); check($status === 404, 'Método auxiliar accesible');
    [, $html] = request($admin, 'transaccion/create'); $ref = token($html, 'referencia');
    $payment = ['_csrf_token' => $csrfAdmin, 'referencia' => $ref, 'id_proyecto' => $projects[0], 'fecha_de_pago' => date('Y-m-d'), 'monto_abonado' => '30.25', 'tipo_de_transaccion' => 'Abono'];
    foreach (['-1', '0', '100.01', '0.001'] as $amount) {
        request($admin, 'transaccion/store', array_replace($payment, ['monto_abonado' => $amount]));
        check(!(new App\Models\Transaccion())->findBy('referencia', $ref), 'Aceptó pago inválido ' . $amount);
    }
    request($admin, 'transaccion/store', array_replace($payment, ['_csrf_token' => 'invalido']));
    check(!(new App\Models\Transaccion())->findBy('referencia', $ref), 'Pago sin CSRF');
    request($admin, 'transaccion/store', $payment); request($admin, 'transaccion/store', $payment);
    $pago = (new App\Models\Transaccion())->findBy('referencia', $ref);
    check($pago && (float) $pago['saldo_pendiente'] === 69.75, 'Saldo incorrecto');
    check((int) $db->query('SELECT COUNT(*) FROM transacciones WHERE id_proyecto = ?', [$projects[0]])->fetchColumn() === 1, 'Duplicó pago');
    [, $html] = request($admin, 'transaccion/comprobante/' . $pago['id_transaccion']);
    check(str_contains($html, '$30.25') && str_contains($html, '$69.75'), 'Comprobante incorrecto');
    try {
        (new App\Models\Proyecto())->actualizarConPagos($projects[0], ['id_cliente' => $client, 'presupuesto_inicial' => '20.00']);
        throw new RuntimeException('Aceptó presupuesto menor que los abonos');
    } catch (DomainException $e) {
        check((float) (new App\Models\Proyecto())->find($projects[0])['presupuesto_inicial'] === 100.0, 'Alteró presupuesto al rechazar');
    }
    $tareas = new App\Models\Tarea();
    request($admin, 'tarea/store', ['_csrf_token' => $csrfAdmin, 'asignacion' => $projects[1] . ':' . $employees[1], 'titulo' => 'TEST propia ' . $suffix, 'fecha_limite' => date('Y-m-d')]);
    $ownTask = $tareas->findBy('titulo', 'TEST propia ' . $suffix);
    check((bool) $ownTask, 'No creó tarea desde formulario');
    $own = (int) $ownTask['id_tarea'];
    $other = $tareas->asignar(['id_proyecto' => $projects[0], 'id_empleado' => $employees[0], 'titulo' => 'TEST ajena ' . $suffix, 'fecha_limite' => date('Y-m-d')]);
    [, $html] = request($employee, 'tarea/index');
    check(str_contains($html, 'TEST propia ' . $suffix) && !str_contains($html, 'TEST ajena ' . $suffix), 'Filtrado de tareas incorrecto');
    [$status] = request($employee, 'tarea/avance/' . $other, ['_csrf_token' => $csrfEmployee, 'avance' => 75]); check($status === 403, 'Modificó tarea ajena');
    request($employee, 'tarea/avance/' . $own, ['_csrf_token' => $csrfEmployee, 'avance' => 101]); check((int) $tareas->find($own)['avance'] === 0, 'Aceptó avance inválido');
    request($employee, 'tarea/avance/' . $own, ['_csrf_token' => 'invalido', 'avance' => 40]); check((int) $tareas->find($own)['avance'] === 0, 'Avance sin CSRF');
    request($employee, 'tarea/avance/' . $own, ['_csrf_token' => $csrfEmployee, 'avance' => 60, 'observaciones' => '<script>prueba</script>']);
    check((int) $tareas->find($own)['avance'] === 60, 'No guardó avance');
    [, $html] = request($employee, 'tarea/index'); check(str_contains($html, '&lt;script&gt;prueba&lt;/script&gt;'), 'Observaciones sin escape');
    [$status] = request($employee, 'tarea/store', ['_csrf_token' => $csrfEmployee]); check($status === 403, 'Empleado asignó tarea');
    request($admin, 'proyecto/desasignarEmpleado', ['_csrf_token' => $csrfAdmin, 'id_proyecto' => $projects[1], 'id_empleado' => $employees[1]]);
    check(count((new App\Models\Proyecto())->getEmpleados($projects[1])) === 1, 'Eliminó asignación con tareas');
    [, $html] = request($anon, 'auth/recuperar');
    request($anon, 'auth/solicitarRecuperacion', ['_csrf_token' => token($html), 'correo' => "test-{$suffix}-1@example.invalid"]);
    check((bool) $db->query('SELECT 1 FROM recuperaciones WHERE id_usuario = ?', [$users[1]])->fetchColumn(), 'No registró recuperación');
    request($admin, 'usuario/regenerar/' . $users[1], ['_csrf_token' => $csrfAdmin]);
    check(!$db->query('SELECT 1 FROM recuperaciones WHERE id_usuario = ?', [$users[1]])->fetchColumn(), 'No resolvió recuperación');
    [$status, $html] = request($employee, 'tarea/index'); check($status === 302 && str_contains($html, 'auth/login'), 'No revocó sesión tras reset');
    [, $html] = request($admin, 'usuario/credenciales/' . $users[1]);
    check(str_contains($html, 'Contraseña temporal'), 'No mostró credencial temporal');
    preg_match('/id="claveTemporal"[^>]*value="([^"]+)"/', $html, $temporary);
    check(!empty($temporary[1]), 'No se encontró contraseña temporal');
    $csrfEmployee = login($employee, "test-{$suffix}-1@example.invalid", html_entity_decode($temporary[1]));
    [$status, $html] = request($employee, 'tarea/index');
    check($status === 302 && str_contains($html, 'usuario/cambiarClave'), 'No exigió cambiar contraseña temporal');
    $newPassword = bin2hex(random_bytes(12));
    request($employee, 'usuario/guardarClave', ['_csrf_token' => $csrfEmployee,
        'correo' => "test-{$suffix}-0@example.invalid", 'actual' => $temporary[1], 'nueva' => $newPassword, 'confirmacion' => $newPassword]);
    check(password_verify($newPassword, (new App\Models\Usuario())->find($users[1])['contrasena']), 'No cambió contraseña propia');
    check(password_verify($password, (new App\Models\Usuario())->find($users[0])['contrasena']), 'Alteró otra cuenta');
    [$status] = request($employee, 'tarea/index'); check($status === 200, 'No permitió trabajar después del cambio');
    echo "OK: login, roles, rutas privadas, CSRF, montos, saldo, pago duplicado, comprobante, tareas propias, avance, escape HTML y recuperación.\n";
} finally {
    foreach ($projects as $id) {
        $db->query('DELETE FROM tareas WHERE id_proyecto = ?', [$id]);
        $db->query('DELETE FROM transacciones WHERE id_proyecto = ?', [$id]);
        $db->query('DELETE FROM proyecto_empleado WHERE id_proyecto = ?', [$id]);
        $db->query('DELETE FROM proyectos WHERE id_proyecto = ?', [$id]);
    }
    foreach ($users as $id) { $db->query('DELETE FROM bitacora WHERE id_usuario = ?', [$id]); $db->query('DELETE FROM usuarios WHERE id_usuario = ?', [$id]); }
    foreach ($employees as $id) $db->query('DELETE FROM empleados WHERE id_empleado = ?', [$id]);
    if ($client) $db->query('DELETE FROM clientes WHERE id_cliente = ?', [$client]);
    foreach ([$admin, $employee, $anon] as $h) curl_close($h);
}
