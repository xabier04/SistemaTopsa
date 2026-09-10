<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Avalúo <?= e($d['referencia']) ?></title><link rel="stylesheet" href="<?= asset('css/valuo-report.css') ?>"></head><body>
<nav class="report-tools"><button type="button" data-print-report>Imprimir / Guardar PDF</button><a href="<?= url('valuo/show/' . $v['id_valuo']) ?>">Volver al expediente</a><p>Los anexos PDF se consultan por separado; las imágenes se incluyen al final.</p></nav>
<main><header><p><?= e($d['referencia']) ?> · <?= formatDate($d['fecha_del_valuo']) ?> · <?= e($d['estado']) ?></p><h1>Informe técnico de avalúo</h1>
<p><?= nl2br(e($d['proposito'])) ?></p><p>Inmueble: <?= e($d['tipo_inmueble']) ?> · <?= e($d['zona']) ?></p><p><?= nl2br(e($d['direccion_actual'])) ?></p></header>
<?php if ($d['estado'] === 'Borrador'): ?><p class="report-notice">BORRADOR - Pendiente de revisión del perito.</p><?php endif; ?>
<?php foreach (\App\Models\ValuoFormulario::GENERALES as $title => $fields): ?><section><h2><?= e($title) ?></h2><dl>
<?php foreach ($fields as $key => $label): if (($d[$key] ?? '') === '') continue; ?><div><dt><?= e($label) ?></dt><dd><?= nl2br(e($d[$key])) ?></dd></div><?php endforeach; ?></dl></section><?php endforeach; ?>
<section><h2>Áreas y cálculo del valor</h2><p>Área según escritura: <?= e((string) $d['area_escritura']) ?> m². Área según inspección: <?= e((string) $d['area_inspeccion']) ?> m².</p>
<?php if ($d['area_escritura'] !== '' && $d['area_inspeccion'] !== ''): ?><p>Diferencia inspección menos escritura: <?= number_format((float) $d['area_inspeccion'] - (float) $d['area_escritura'], 6) ?> m².</p><?php endif; ?>
<?php require __DIR__ . '/results.php'; ?></section>
<section class="report-conclusion"><h2>Análisis y conclusión del perito</h2><p>Método seleccionado: <?= e($d['metodo']) ?>.</p><p><?= nl2br(e($d['conclusion'])) ?></p>
<p>Valor adoptado: <strong><?= $r['adoptado'] === null ? 'Pendiente' : formatMoney($r['adoptado']) ?></strong>.</p>
<p>Derecho valuado: <?= e((string) $d['porcentaje_derecho']) ?>%. Valor del derecho: <strong><?= $r['valor_derecho'] === null ? 'Pendiente' : formatMoney($r['valor_derecho']) ?></strong>.</p>
<p><?= nl2br(e($d['situacion_juridica'])) ?></p><h3>Revisión de criterios de cálculo</h3>
<p>Se conserva el cálculo de la plantilla de Apaneca: comparaciones encadenadas 1/sujeto, 2/1 y 3/2; K2 y Q independientes; residual del 10%; perímetro de homologación = 2 × (frente + fondo).</p>
<p><?= nl2br(e($d['revision_perito'] ?: 'Revisión pendiente.')) ?></p>
<div class="signature"><p>________________________________________</p><p><?= e($d['perito']) ?><br>Registro: <?= e($d['registro_perito']) ?></p><p>Firma del perito</p></div></section>
<?php if ($anexos): ?><section><h2>Anexos</h2><?php foreach ($anexos as $a): ?><figure>
<?php if (str_starts_with($a['tipo'], 'image/')): ?><img src="<?= url('valuo/attachment/' . $a['id_anexo']) ?>" alt="<?= e($a['descripcion']) ?>"><?php else: ?><p>Documento PDF adjunto: <a href="<?= url('valuo/attachment/' . $a['id_anexo']) ?>"><?= e($a['nombre']) ?></a> (archivo separado).</p><?php endif; ?>
<figcaption><?= e($a['descripcion'] ?: $a['nombre']) ?></figcaption></figure><?php endforeach; ?></section><?php endif; ?>
<footer>Expediente <?= e($d['referencia']) ?> · Revisión <?= (int) $v['revision'] ?> · Método de cálculo <?= e($r['version']) ?></footer></main>
<script src="<?= asset('js/modules/valuos.js') ?>"></script></body></html>
