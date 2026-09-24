<section class="report-chapter report-cost"><div class="document-heading"><strong>ANÁLISIS ECONÓMICO DEL INMUEBLE</strong><span><?= e($d['referencia']) ?></span></div>
<h2>V. Valor por el método del costo depreciado</h2>
<p>El valor por costo integra el terreno, ajustado por sus factores de corrección, y el valor actual de las construcciones, obtenido a partir de su reposición y depreciación.</p>
<h3>5.1. Valor del terreno</h3>
<table><thead><tr><th>Área valuada (m²)</th><th>Área equivalente (v²)</th><th>Valor base zonal (USD/v²)</th></tr></thead><tbody><tr><td class="numeric"><?= $decimal($d['area_valuada']) ?></td><td class="numeric"><?= $decimal($r['area_varas'], 8) ?></td><td class="numeric"><?= $money($d['valor_vara']) ?></td></tr></tbody></table>
<table class="factor-table"><caption>Factores de corrección del terreno</caption><thead><tr><?php foreach ($d['factores'] as $key => $value): ?><th><?= e(ucfirst($key)) ?></th><?php endforeach; ?></tr></thead><tbody><tr><?php foreach ($d['factores'] as $value): ?><td><?= $decimal($value) ?></td><?php endforeach; ?></tr></tbody></table>
<table><thead><tr><th>Factor total</th><th>Valor corregido (USD/v²)</th><th>Valor del terreno (USD)</th></tr></thead><tbody><tr><td class="numeric"><?= $decimal($r['factor_terreno'], 6) ?></td><td class="numeric"><?= $money($r['unitario_terreno']) ?></td><td class="numeric"><?= $money($r['terreno']) ?></td></tr></tbody></table>
<h3>5.2. Valor actual de las construcciones</h3>
<?php if ($d['construcciones']): ?>
<table class="construction-table"><thead><tr><th>Componente / conservación</th><th>Área (m²)</th><th>Edad / vida útil (años)</th><th>K1 / K2 / K</th><th>Reposición / residual (USD)</th><th>Valor actual (USD)</th></tr></thead><tbody>
<?php foreach ($d['construcciones'] as $i => $c): $cr = $r['construcciones'][$i]; ?><tr><td><?= e($c['descripcion'] ?: 'Componente ' . ($i + 1)) ?><br><span class="muted"><?= e($c['estado']) ?></span></td><td class="numeric"><?= $decimal($c['area'], 4) ?></td><td class="numeric"><?= $display($c['edad']) ?> / <?= $display($c['vida']) ?></td><td class="numeric"><?= $decimal($cr['k1'], 6) ?><br><?= $decimal($c['k2'], 6) ?><br><?= $decimal($cr['k'], 7) ?></td><td class="numeric"><?= $money($c['valor_nuevo']) ?><br><?= $money($cr['residual']) ?></td><td class="numeric"><?= $money($cr['actual']) ?></td></tr><?php endforeach; ?></tbody></table>
<aside class="calculation-note"><strong>Expresión del cálculo:</strong> K1 = edad / vida útil; K = K1 + (1 − K1) × K2.<br>Valor actual = reposición − (reposición − residual) × K. Valor residual = 10% de la reposición.</aside>
<?php else: ?><p>No se registraron componentes de construcción para valorar.</p><?php endif; ?>
<h3>5.3. Integración del valor por costo</h3>
<table class="valuation-summary"><thead><tr><th>Componente</th><th>Participación</th><th>Valor (USD)</th></tr></thead><tbody><tr><th>Terreno</th><td class="numeric"><?= $decimal($r['peso_terreno'] * 100, 4) ?>%</td><td><?= $money($r['terreno']) ?></td></tr><tr><th>Construcción</th><td class="numeric"><?= $decimal($r['peso_construccion'] * 100, 4) ?>%</td><td><?= $money($r['construccion']) ?></td></tr><tr class="total-row"><th colspan="2">Valor por el método del costo</th><td><?= $money($r['costo']) ?></td></tr></tbody></table>
</section>
<section class="report-chapter report-market"><div class="document-heading"><strong>ESTUDIO COMPARATIVO DE MERCADO</strong><span><?= e($d['referencia']) ?></span></div><h2>VI. Valor por el método comparativo</h2>
<?php if ($d['comparables']): ?>
<p>Se contrastan las características del inmueble sujeto con tres referencias registradas en el expediente. Los precios unitarios se ajustan mediante los factores de homologación detallados a continuación.</p>
<h3>6.1. Inmueble sujeto y referencias de mercado</h3>
<?php
$subject = array_merge($d['sujeto'], ['direccion' => $d['direccion_actual'], 'propietario' => $d['propietarios'], 'matricula' => $d['matricula'], 'tipo' => $d['tipo_inmueble'], 'zona' => $d['zona'], 'area_terreno' => $d['area_valuada']]);
// Indicadores descriptivos del sujeto; no sustituyen los resultados guardados.
$subjectIndicators = [
    'perimetro' => 2 * ((float) $subject['frente'] + (float) $subject['fondo']),
    'cus' => (float) $subject['area_construida'] / (float) $d['area_valuada'],
    'depreciacion' => (1 - ((float) $subject['edad'] / (float) $subject['vida']) ** 1.4) * (float) $subject['q'],
];
$marketCell = static function ($value, string $key, string $type) use ($decimal, $display, $money) {
    if ($value === null) return '—';
    if ($value === '') return 'No registrado';
    if (str_starts_with($key, 'precio_')) return $money($value);
    return $type === 'number' && !in_array($key, ['edad', 'vida']) ? $decimal($value, 4) : $display($value);
};
?>
<table class="market-table"><colgroup><col class="market-label"><col><col><col><col></colgroup><thead><tr><th>Descripción</th><th class="subject-col">Sujeto</th><th>Comparable 1</th><th>Comparable 2</th><th>Comparable 3</th></tr></thead><tbody>
<?php foreach (\App\Models\ValuoFormulario::COMPARABLE as $key => [$label, $type]): ?><tr><th><?= e($label) ?></th><td class="subject-col"><?= $marketCell($subject[$key] ?? null, $key, $type) ?></td><?php foreach ($d['comparables'] as $c): ?><td><?= $marketCell($c[$key], $key, $type) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
<?php foreach (['perimetro' => 'Perímetro (m)', 'cus' => 'CUS', 'depreciacion' => 'Factor de depreciación'] as $key => $label): ?><tr><th><?= $label ?></th><td class="subject-col"><?= $decimal($subjectIndicators[$key]) ?></td><?php foreach ($r['comparables'] as $c): ?><td><?= $decimal($c[$key]) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table>
<p class="table-note">—: dato no aplicable al sujeto. Importes expresados en USD.</p>
<div class="report-homologacion"><h3>6.2. Factores parciales de homologación</h3>
<table><thead><tr><th>Factor</th><th>Comparable 1</th><th>Comparable 2</th><th>Comparable 3</th></tr></thead><tbody>
<?php foreach (array_keys($r['comparables'][0]['factores']) as $key): ?><tr><th><?= e(ucfirst($key)) ?></th><?php foreach ($r['comparables'] as $c): ?><td class="numeric"><?= $decimal($c['factores'][$key]) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
<?php foreach (['global' => 'Factor global', 'unitario' => 'Precio actual / m² construido', 'homologado' => 'Valor homologado / m²'] as $key => $label): ?><tr<?= $key === 'homologado' ? ' class="total-row"' : '' ?>><th><?= $label ?></th><?php foreach ($r['comparables'] as $c): ?><td class="numeric"><?= $key === 'global' ? $decimal($c[$key]) : $money($c[$key]) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table>
<p class="table-note">El factor global se redondea a cuatro decimales. Se conserva el orden de las comparaciones del expediente: 1/sujeto, 2/1 y 3/2.</p>
<table class="valuation-summary"><tbody><tr><th>Promedio homologado por m² construido</th><td><?= $money($r['promedio_homologado']) ?></td></tr><tr><th>Área construida del sujeto (m²)</th><td><?= $decimal($d['sujeto']['area_construida']) ?></td></tr><tr class="total-row"><th>Valor calculado por el método comparativo</th><td><?= $money($r['mercado']) ?></td></tr></tbody></table></div>
<?php else: ?><p>No se registraron comparables. El expediente utiliza el método del costo.</p><?php endif; ?></section>

