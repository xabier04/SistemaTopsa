<link rel="stylesheet" href="<?= asset('css/valuos.css') ?>">
<div class="page-header"><h2><?= e($v['referencia'] ?? ('Avalúo #' . $v['id_valuo'])) ?></h2><a class="btn btn-outline" href="<?= url('valuo/index') ?>">Volver</a></div>
<div class="avaluo-actions"><a class="btn btn-primary" href="<?= url('valuo/edit/' . $v['id_valuo']) ?>">Editar expediente</a>
<?php if ($d): ?><a class="btn btn-outline" href="<?= url('valuo/report/' . $v['id_valuo']) ?>" target="_blank" rel="noopener">Ver informe / Guardar PDF</a><?php endif; ?>
<a class="btn btn-outline" href="<?= url('proyecto/detalle/' . $v['id_proyecto']) ?>">Proyecto</a></div>
<?php if (!$d): ?><section class="avaluo-section"><p>Registro anterior sin desglose. Monto registrado: <?= formatMoney($v['monto_estimado']) ?>. Edite para completar el expediente.</p></section><?php else: ?>
<section class="avaluo-section"><p><strong><?= e($v['estado']) ?></strong> · <?= formatDate($v['fecha_del_valuo']) ?> · Revisión <?= (int) $v['revision'] ?></p>
<p><?= nl2br(e($d['direccion_actual'])) ?></p><p>Perito: <?= e($d['perito']) ?>. Registro: <?= e($d['registro_perito']) ?>.</p>
<?php require __DIR__ . '/results.php'; ?></section>
<section class="avaluo-section"><h3>Conclusión</h3><p>Método seleccionado: <?= e($d['metodo']) ?>. Derecho valuado: <?= e((string) $d['porcentaje_derecho']) ?>%.</p>
<p><?= nl2br(e($d['conclusion'])) ?></p><p><?= nl2br(e($d['situacion_juridica'])) ?></p>
<div class="avaluo-notice">Se conservan las referencias encadenadas del Excel. K2 del costo y Q de mercado son coeficientes diferentes. El perito debe revisar estos criterios antes de emitir el informe.</div>
<h3>Revisión del perito</h3><p><?= nl2br(e($d['revision_perito'] ?: 'Pendiente de documentar.')) ?></p></section>
<?php endif; ?>
<section class="avaluo-section"><h3>Anexos: mapas, fotografías, planos y documentos</h3>
<form action="<?= url('valuo/upload/' . $v['id_valuo']) ?>" method="post" enctype="multipart/form-data">
<?= csrf_field() ?><div class="avaluo-grid"><div class="form-group"><label for="anexo">Archivo (PDF, JPG, PNG o WebP, hasta 10 MB)</label><input class="form-control" type="file" name="anexo" id="anexo" accept="application/pdf,image/jpeg,image/png,image/webp" required></div>
<div class="form-group"><label for="descripcion">Descripción del anexo</label><input class="form-control" name="descripcion" id="descripcion" maxlength="255" placeholder="Fachada, plano, ubicación, respaldo de comparable…"></div></div>
<div class="avaluo-actions"><button class="btn btn-primary" type="submit">Agregar anexo</button></div></form>
<div class="avaluo-attachments"><?php foreach ($anexos as $a): ?><div>
<?php if (str_starts_with($a['tipo'], 'image/')): ?><img src="<?= url('valuo/attachment/' . $a['id_anexo']) ?>" alt="<?= e($a['descripcion']) ?>" loading="lazy"><?php endif; ?>
<p><a href="<?= url('valuo/attachment/' . $a['id_anexo']) ?>" target="_blank" rel="noopener"><?= e($a['nombre']) ?></a></p><p><?= e($a['descripcion']) ?></p></div><?php endforeach; ?></div></section>
