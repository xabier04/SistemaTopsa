<?php $money = static fn ($n) => $n === null ? 'Sin definir' : formatMoney($n);
$decimal = static fn ($n, $places = 4) => number_format((float) $n, $places, '.', ','); ?>
<div class="avaluo-results">
<?php foreach (['terreno' => 'Terreno', 'construccion' => 'Construcción', 'costo' => 'Método del costo', 'mercado' => 'Método comparativo', 'adoptado' => 'Valor adoptado', 'valor_derecho' => 'Valor del derecho'] as $key => $label): ?>
    <div><?= $label ?><strong><?= $money($r[$key]) ?></strong></div>
<?php endforeach; ?></div>
<h3>Costo depreciado</h3>
<p>Área a valuar: <?= e((string) $d['area_valuada']) ?> m² = <?= $decimal($r['area_varas'], 8) ?> v².
    Valor base: <?= $money($d['valor_vara']) ?> / v². Factor total: <?= $decimal($r['factor_terreno'], 6) ?>.
    Valor unitario corregido: <?= $money($r['unitario_terreno']) ?> / v².</p>
<p>Factores de terreno:
<?php foreach ($d['factores'] as $key => $value): ?><?= e(ucfirst($key)) ?>: <?= e((string) $value) ?>. <?php endforeach; ?></p>
<?php if ($d['construcciones']): ?>
<div class="table-container"><table><thead><tr><th>Componente / conservación</th><th>m²</th><th>Edad / vida</th><th>K1 / K2 / K</th><th>Reposición / residual</th><th>Valor actual</th></tr></thead><tbody>
<?php foreach ($d['construcciones'] as $i => $c): $cr = $r['construcciones'][$i]; ?>
<tr><td><?= e($c['descripcion']) ?><br><?= e($c['estado']) ?></td><td><?= e((string) $c['area']) ?></td><td><?= e((string) $c['edad']) ?> / <?= e((string) $c['vida']) ?></td>
    <td><?= $decimal($cr['k1'], 6) ?><br><?= e((string) $c['k2']) ?><br><?= $decimal($cr['k'], 7) ?></td><td><?= $money($c['valor_nuevo']) ?><br><?= $money($cr['residual']) ?></td><td><?= $money($cr['actual']) ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php endif; ?>
<p>Participación en el costo: construcción <?= $decimal($r['peso_construccion'] * 100, 6) ?>%; terreno <?= $decimal($r['peso_terreno'] * 100, 6) ?>%.</p>
<?php if ($d['comparables']): ?>
<section class="report-market"><h3>Método comparativo de mercado</h3>
<p>Sujeto: área de terreno <?= e((string) $d['area_valuada']) ?> m².
<?php foreach (\App\Models\ValuoFormulario::SUJETO as $key => [$label]): ?><?= e($label) ?>: <?= e((string) $d['sujeto'][$key]) ?>. <?php endforeach; ?></p>
<div class="table-container"><table><thead><tr><th>Datos de mercado</th><th>Comparable 1</th><th>Comparable 2</th><th>Comparable 3</th></tr></thead><tbody>
<?php foreach (\App\Models\ValuoFormulario::COMPARABLE as $key => [$label]): ?>
<tr><th><?= e($label) ?></th><?php foreach ($d['comparables'] as $c): ?><td><?= nl2br(e((string) $c[$key])) ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
<?php foreach (['perimetro' => 'Perímetro calculado (m)', 'cus' => 'CUS', 'depreciacion' => 'Factor de depreciación'] as $key => $label): ?>
<tr><th><?= $label ?></th><?php foreach ($r['comparables'] as $c): ?><td><?= $decimal($c[$key]) ?></td><?php endforeach; ?></tr>
<?php endforeach; ?></tbody></table></div></section>
<section class="report-homologacion"><h3>Factores parciales de homologación</h3><div class="table-container"><table><thead><tr><th>Factor</th><th>Comparable 1</th><th>Comparable 2</th><th>Comparable 3</th></tr></thead><tbody>
<?php foreach (array_keys($r['comparables'][0]['factores']) as $key): ?><tr><th><?= e(ucfirst($key)) ?></th><?php foreach ($r['comparables'] as $c): ?><td><?= $decimal($c['factores'][$key]) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
<?php foreach (['global' => 'Factor global (redondeado a 4 decimales)', 'unitario' => 'Precio actual / m² construido', 'homologado' => 'Valor homologado / m²'] as $key => $label): ?>
<tr><th><?= $label ?></th><?php foreach ($r['comparables'] as $c): ?><td><?= $key === 'global' ? $decimal($c[$key]) : $money($c[$key]) ?></td><?php endforeach; ?></tr>
<?php endforeach; ?></tbody></table></div>
<p>Promedio homologado: <?= $money($r['promedio_homologado']) ?> / m². Total calculado: <strong><?= $money($r['mercado']) ?></strong>.</p></section>
<?php endif; ?>
