<?php
// Prueba local de transacciones. Solo crea y elimina su propio registro temporal.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
spl_autoload_register(function ($class) {
    $prefixes = ['Core\\' => '/core/', 'App\\Models\\' => '/app/Models/'];
    foreach ($prefixes as $prefix => $path) {
        if (str_starts_with($class, $prefix)) { require __DIR__ . '/..' . $path . substr($class, strlen($prefix)) . '.php'; }
    }
});
require __DIR__ . '/../core/helpers.php';
$db = Core\Database::getInstance();
$project = $db->query('SELECT id_proyecto FROM proyectos ORDER BY id_proyecto LIMIT 1')->fetchColumn();
if (!$project) { throw new RuntimeException('Se requiere al menos un proyecto local para esta prueba.'); }
$fixture = require __DIR__ . '/fixtures/apaneca.php';
$input = array_merge($fixture, ['referencia' => 'TEST-' . bin2hex(random_bytes(8)), 'id_proyecto' => $project,
    'fecha_del_valuo' => '2026-05-28', 'estado' => 'Borrador', 'revision' => '0', 'conclusion' => 'Prueba de persistencia <script>alert(1)</script>']);
$d = App\Models\ValuoFormulario::normalizar($input);
$r = App\Models\ValuoCalculo::calcular($d);
$model = new App\Models\Valuo(); $id = null;
try {
    $id = $model->guardarExpediente($d, $r, null);
    $v = $model->expediente($id);
    if ($v['datos']['area_valuada'] !== '132.251' || abs($v['resultados']['mercado'] - 60324.57233706321) > 0.000001 || (float) $v['monto_estimado'] !== 60000.0) {
        throw new RuntimeException('Se perdió precisión o se guardó un total distinto.');
    }
    $d['revision'] = '1'; $d['valor_adoptado'] = '61000'; $r = App\Models\ValuoCalculo::calcular($d);
    $model->guardarExpediente($d, $r, $id);
    $caught = false;
    try { $model->guardarExpediente($d, $r, $id); } catch (InvalidArgumentException $e) { $caught = true; }
    if (!$caught) { throw new RuntimeException('Una revisión antigua pudo sobrescribir el registro.'); }
    $v = $model->expediente($id);
    if ((int) $v['revision'] !== 2 || (float) $v['monto_estimado'] !== 61000.0) { throw new RuntimeException('Edición o rollback incorrectos.'); }
    $d = $v['datos']; $r = $v['resultados']; $anexos = [];
    ob_start(); require __DIR__ . '/../app/Views/valuos/report.php'; $html = ob_get_clean();
    if (!str_contains($html, '$60,324.57') || str_contains($html, '<script>alert(1)</script>') || !str_contains($html, 'Comparable 3')) {
        throw new RuntimeException('El reporte no muestra los resultados o no escapa texto.');
    }
    foreach (['I. Datos generales', 'II. Entorno del inmueble', 'III. Descripción del terreno',
        'IV. Descripción de la construcción', 'V. Valor por el método del costo',
        'VI. Valor por el método comparativo', 'VII. Análisis y conclusión',
        'Sujeto', '51.9600', 'logo-topsa.jpg', '$61,000.00'] as $expected) {
        if (!str_contains($html, $expected)) throw new RuntimeException('Falta contenido del informe técnico: ' . $expected);
    }
    echo "OK: alta, lectura exacta, edición, conflicto concurrente, rollback y reporte escapado.\n";
} finally {
    if ($id !== null) {
        $db->query('DELETE FROM valuos WHERE id_valuo=:id AND id_valuo IN (SELECT id_valuo FROM valuo_expedientes WHERE referencia=:ref)',
            [':id' => $id, ':ref' => $input['referencia']]);
    }
}
