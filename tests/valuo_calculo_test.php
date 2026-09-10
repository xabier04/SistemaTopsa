<?php
require __DIR__ . '/../app/Models/ValuoCalculo.php';
require __DIR__ . '/../app/Models/ValuoFormulario.php';
use App\Models\ValuoCalculo as C;
use App\Models\ValuoFormulario as F;
$fixture = require __DIR__ . '/fixtures/apaneca.php';
$checks = 0;
function near(float $actual, float $expected, float $tolerance = 0.000001): void {
    global $checks; ++$checks;
    if (abs($actual - $expected) > $tolerance) { throw new RuntimeException("Esperado {$expected}, recibido {$actual}"); }
}
function rejected(callable $action): void {
    global $checks; ++$checks;
    try { $action(); } catch (InvalidArgumentException $e) { return; }
    throw new RuntimeException('Se aceptaron datos inválidos.');
}
$r = C::calcular($fixture);
// Valores cacheados del Excel: E147, M147, O128, M154, H207:N207, H209, H211, L232.
near($r['area_varas'], 189.25443622611402);
near($r['terreno'], 35012.07070183109);
near($r['construccion'], 25000.57349);
near($r['costo'], 60012.644191831096);
foreach ([1.0793, 1.1189, 1.0793] as $i => $expected) { near($r['comparables'][$i]['global'], $expected); }
near($r['promedio_homologado'], 467.4511610775917);
near($r['mercado'], 60324.57233706321);
near($r['valor_derecho'], 30000);
near($r['comparables'][1]['factores']['terreno'], 1.1816813365360792);
near($r['comparables'][2]['factores']['superficie'], 1.2761680889276326);
$d = $fixture; $d['comparables'] = []; $d['construcciones'] = []; $d['metodo'] = 'costo'; $d['valor_adoptado'] = '';
$land = C::calcular($d); near($land['costo'], $r['terreno']);
if ($land['mercado'] !== null || $land['adoptado'] !== null || $land['valor_derecho'] !== null) { throw new RuntimeException('No debe inventar valores pendientes.'); }
$d = $fixture; $d['construcciones'][] = $d['construcciones'][0]; near(C::calcular($d)['construccion'], 2 * $r['construccion']);
foreach ([['area_valuada', 0], ['valor_vara', -1], ['porcentaje_derecho', 101], ['valor_adoptado', INF]] as [$key, $value]) {
    $d = $fixture; $d[$key] = $value; rejected(fn () => C::calcular($d));
}
$d = $fixture; $d['construcciones'][0]['vida'] = 0; rejected(fn () => C::calcular($d));
$d = $fixture; $d['comparables'][0]['area_construida'] = 0; rejected(fn () => C::calcular($d));
$d = $fixture; $d['comparables'][0]['edad'] = 60; rejected(fn () => C::calcular($d));
$d = $fixture; $d['comparables'][0]['q'] = 0; rejected(fn () => C::calcular($d));
$d = $fixture; array_pop($d['comparables']); rejected(fn () => C::calcular($d));
$d = $fixture; $d['monto_estimado'] = 1; $normalized = F::normalizar($d);
if (isset($normalized['monto_estimado'])) { throw new RuntimeException('Se aceptó un total enviado por el cliente.'); }
near(C::calcular($normalized)['mercado'], $r['mercado']);
rejected(fn () => F::normalizar(['referencia' => ['x']]));
rejected(fn () => F::normalizar(['comparables' => 'texto']));
$d = F::normalizar(array_merge($fixture, ['area_inspeccion' => '0']));
rejected(fn () => F::validarNumeros($d));
if ($d['area_valuada'] !== '132.251') { throw new RuntimeException('Se perdió la captura al validar.'); }
echo "OK: {$checks} comprobaciones; costo 60,012.64; mercado 60,324.57; derecho 30,000.00.\n";
