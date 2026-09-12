<?php
use App\Models\ValuoFormulario;
$money = static fn ($n) => $n === null || $n === '' ? 'Pendiente' : formatMoney($n);
$decimal = static fn ($n, $places = 4) => $n === null || $n === '' ? 'No registrado' : number_format((float) $n, $places, '.', ',');
$display = static fn ($value) => $value === '' || $value === null ? 'No registrado' : nl2br(e((string) $value));
$method = $d['metodo'] === 'comparativo' ? 'Comparativo de mercado' : 'Costo de reposición depreciado';
$general = ValuoFormulario::GENERALES['Identificación y encargo'];
$environment = array_slice(ValuoFormulario::GENERALES['Entorno y terreno'], 0, 12, true);
$land = array_slice(ValuoFormulario::GENERALES['Entorno y terreno'], 12, null, true);
$groups = ['I. Datos generales' => $general, 'II. Entorno del inmueble' => $environment,
    'III. Descripción del terreno' => $land, 'IV. Descripción de la construcción' => ValuoFormulario::GENERALES['Descripción de la construcción']];
?>
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Avalúo <?= e($d['referencia']) ?></title><link rel="stylesheet" href="<?= asset('css/valuo-report.css') ?>"></head><body>
<nav class="report-tools"><button type="button" data-print-report>Imprimir / Guardar PDF</button><a href="<?= url('valuo/excel/' . $v['id_valuo']) ?>">Exportar a Excel</a><a href="<?= url('valuo/show/' . $v['id_valuo']) ?>">Volver al expediente</a><p>Seleccione tamaño A4 y desactive los encabezados y pies del navegador. Las imágenes se incluyen en el informe; los anexos PDF se consultan por separado.</p></nav>
<main>
<header class="report-cover">
<div class="report-brand"><img src="<?= asset('img/logo-topsa.jpg') ?>" alt="TOPSA - Topografía y Servicios Anexos" width="1109" height="517"><p>TOPOGRAFÍA Y<br>SERVICIOS ANEXOS</p></div>
<div class="cover-recipient"><span>INFORME PRESENTADO A</span><p><?= $display($d['destinatario']) ?></p></div>
<p class="report-eyebrow">INFORME TÉCNICO DE VALUACIÓN</p><h1>Avalúo de inmueble</h1>
<p class="cover-type"><?= e($d['tipo_inmueble'] ?: 'Inmueble objeto de valuación') ?><?= $d['zona'] ? ' · ' . e($d['zona']) : '' ?></p>
<div class="cover-reference">Referencia: <strong><?= e($d['referencia']) ?></strong></div>
<div class="cover-purpose"><h2>Propósito del informe</h2><p><?= $display($d['proposito']) ?></p><p class="cover-methods">Método del costo de reposición<?= $d['comparables'] ? ' y método comparativo de mercado' : '' ?></p></div>
<dl class="cover-details">
<?php foreach (['numero_informe' => 'Número de avalúo', 'matricula' => 'Matrícula', 'propietarios' => 'Propietarios', 'direccion_actual' => 'Ubicación'] as $key => $label): ?>
<div><dt><?= $label ?></dt><dd><?= $display($d[$key]) ?></dd></div><?php endforeach; ?>
</dl>
<div class="cover-author"><span>PRESENTADO POR EL PERITO VALUADOR</span><p><strong><?= e($d['perito'] ?: 'Pendiente de asignar') ?></strong><br>Registro: <?= e($d['registro_perito'] ?: 'No registrado') ?></p><p><?= formatDate($d['fecha_del_valuo']) ?></p></div>
<p class="cover-status"><?= e($d['estado']) ?> · Revisión <?= (int) $v['revision'] ?><?= $d['estado'] === 'Borrador' ? ' · Pendiente de revisión del perito' : '' ?></p>
</header>
<div class="report-body">
<div class="document-heading"><strong>INFORME TÉCNICO DE AVALÚO</strong><span><?= e($d['referencia']) ?> · <?= formatDate($d['fecha_del_valuo']) ?></span></div>
<?php if ($d['estado'] === 'Borrador'): ?><p class="report-notice">BORRADOR - Pendiente de revisión del perito.</p><?php endif; ?>
<?php foreach ($groups as $title => $fields): ?>
<section class="report-data"><h2><?= e($title) ?></h2><dl class="technical-fields">
<?php $hasFields = false; foreach ($fields as $key => $label): if (($d[$key] ?? '') === '') continue; $hasFields = true; ?>
<div><dt><?= e($label) ?></dt><dd><?= $display($d[$key]) ?></dd></div><?php endforeach; ?>
</dl><?php if (!$hasFields): ?><p class="muted">Sin información registrada en esta sección.</p><?php endif; ?>
<?php if ($title === 'III. Descripción del terreno'): ?>
<table class="area-table"><thead><tr><th>Superficie según escritura</th><th>Superficie según inspección</th><th>Área a valuar</th></tr></thead><tbody><tr><td><?= $decimal($d['area_escritura']) ?><?= $d['area_escritura'] !== '' ? ' m²' : '' ?></td><td><?= $decimal($d['area_inspeccion']) ?><?= $d['area_inspeccion'] !== '' ? ' m²' : '' ?></td><td><?= $decimal($d['area_valuada']) ?> m²</td></tr></tbody></table>
<?php if ($d['area_escritura'] !== '' && $d['area_inspeccion'] !== ''): ?><p class="table-note">Diferencia entre inspección y escritura: <?= $decimal((float) $d['area_inspeccion'] - (float) $d['area_escritura'], 6) ?> m².</p><?php endif; ?>
<?php endif; ?></section><?php endforeach; ?>
<?php require __DIR__ . '/report-calculations.php'; ?>
<section class="report-chapter report-conclusion"><div class="document-heading"><strong>DICTAMEN DE VALUACIÓN</strong><span><?= e($d['referencia']) ?></span></div>
<h2>VII. Análisis y conclusión del valor</h2>
<p><strong>Método seleccionado:</strong> <?= e($method) ?>.</p><div class="conclusion-text"><?= $d['conclusion'] ? $display($d['conclusion']) : 'Conclusión pendiente de documentar por el perito.' ?></div>
<table class="valuation-summary"><thead><tr><th>Resultado de la valuación</th><th>Importe (USD)</th></tr></thead><tbody>
<tr><th>Valor por el método del costo</th><td><?= $money($r['costo']) ?></td></tr>
<tr><th>Valor por el método comparativo</th><td><?= $r['mercado'] === null ? 'No calculado' : $money($r['mercado']) ?></td></tr>
<tr class="total-row"><th>Valor adoptado del inmueble</th><td><?= $money($r['adoptado']) ?></td></tr>
<tr><th>Porcentaje del derecho valuado</th><td><?= $decimal($d['porcentaje_derecho'], 2) ?>%</td></tr>
<tr class="total-row"><th>Valor del derecho valuado</th><td><?= $money($r['valor_derecho']) ?></td></tr></tbody></table>
<h3>Situación jurídica y alcance del derecho</h3><p><?= $display($d['situacion_juridica']) ?></p>
<h3>Revisión del perito</h3><p><?= $d['revision_perito'] ? $display($d['revision_perito']) : 'Revisión pendiente.' ?></p>
<aside class="calculation-note"><strong>Criterios de cálculo del expediente.</strong> Comparaciones encadenadas 1/sujeto, 2/1 y 3/2; K2 y Q independientes; valor residual del 10%; perímetro de homologación = 2 × (frente + fondo). Versión: <?= e($r['version']) ?>.</aside>
<div class="signature"><div class="signature-line"></div><strong><?= e($d['perito'] ?: 'Perito valuador') ?></strong><br>Registro: <?= e($d['registro_perito'] ?: 'Pendiente') ?><p>Firma y sello del perito valuador</p></div></section>
<?php if ($anexos): ?><section class="report-chapter report-annexes"><div class="document-heading"><strong>DOCUMENTACIÓN DE RESPALDO</strong><span><?= e($d['referencia']) ?></span></div><h2>VIII. Anexos del expediente</h2>
<table><thead><tr><th class="annex-number">Anexo</th><th>Descripción / documento</th><th class="annex-format">Formato</th></tr></thead><tbody>
<?php foreach ($anexos as $i => $a): ?><tr><td><?= sprintf('%02d', $i + 1) ?></td><td><?= $display($a['descripcion'] ?: $a['nombre']) ?></td><td><?= str_starts_with($a['tipo'], 'image/') ? 'Imagen' : 'PDF separado' ?></td></tr><?php endforeach; ?></tbody></table>
<?php foreach ($anexos as $i => $a): ?><div class="annex-sheet"><h3>Anexo <?= sprintf('%02d', $i + 1) ?>. <?= e($a['descripcion'] ?: $a['nombre']) ?></h3>
<?php if (str_starts_with($a['tipo'], 'image/')): ?><figure><img src="<?= url('valuo/attachment/' . $a['id_anexo']) ?>" alt="<?= e($a['descripcion'] ?: $a['nombre']) ?>"><figcaption><?= e($a['descripcion'] ?: $a['nombre']) ?></figcaption></figure>
<?php else: ?><p>Documento de respaldo: <a href="<?= url('valuo/attachment/' . $a['id_anexo']) ?>"><?= e($a['nombre']) ?></a>. Se consulta como archivo PDF separado.</p><?php endif; ?></div><?php endforeach; ?></section><?php endif; ?>
<footer><strong>TOPSA · Topografía y Servicios Anexos</strong><br>Referencia <?= e($d['referencia']) ?> · Fecha de valuación <?= formatDate($d['fecha_del_valuo']) ?> · Revisión <?= (int) $v['revision'] ?></footer>
</div></main><script src="<?= asset('js/modules/valuos.js') ?>"></script></body></html>
