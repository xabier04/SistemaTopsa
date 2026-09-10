<?php
use App\Models\ValuoFormulario as F;
use App\Models\ValuoCalculo as C;
$field = static function (string $name, string $label, mixed $value = '', string $type = 'text', bool $required = false): void { ?>
    <div class="form-group"><label for="<?= e($name) ?>"><?= e($label) ?></label>
        <input class="form-control" id="<?= e($name) ?>" name="<?= e($name) ?>" type="<?= e($type) ?>" value="<?= e((string) $value) ?>"
            <?= $type === 'number' ? 'step="any" min="0"' : '' ?> <?= $required ? 'required' : '' ?>></div>
<?php };
$row = static function ($collection, $i, $data, $spec) use ($field): void { ?>
    <fieldset class="avaluo-component" data-component><legend><?= $collection === 'comparables' ? 'Comparable ' . ($i + 1) : 'Componente de construcción' ?></legend>
        <div class="avaluo-grid"><?php foreach ($spec as $key => [$label, $type, $default]) {
            $field("{$collection}[{$i}][{$key}]", $label, $data[$key] ?? $default, $type);
        } ?></div>
        <?php if ($collection === 'construcciones'): ?><button class="btn btn-outline" type="button" data-remove-component>Quitar componente</button><?php endif; ?>
    </fieldset>
<?php }; ?>
<link rel="stylesheet" href="<?= asset('css/valuos.css') ?>">
<div class="page-header"><h2><?= $id ? 'Editar avalúo' : 'Nuevo avalúo' ?></h2><a href="<?= url('valuo/index') ?>" class="btn btn-outline">Volver</a></div>
<?php if ($error): ?><div class="avaluo-notice" role="alert"><?= e($error) ?></div><?php endif; ?>
<form action="<?= e($action) ?>" method="post" id="avaluo-form" data-calculate-url="<?= url('valuo/calcular') ?>">
    <?= csrf_field() ?><input type="hidden" name="revision" value="<?= (int) ($d['revision'] ?? 0) ?>">
    <div class="form-card-clean"><div class="form-card-body"><div class="avaluo-grid">
        <div class="form-group"><label for="id_proyecto">Proyecto</label><select id="id_proyecto" name="id_proyecto" class="form-control" required>
            <option value="">Seleccione un proyecto</option>
            <?php foreach ($proyectos as $p): ?><option value="<?= (int) $p['id_proyecto'] ?>" <?= ($d['id_proyecto'] ?? '') == $p['id_proyecto'] ? 'selected' : '' ?>><?= e($p['nombre_del_proyecto']) ?></option><?php endforeach; ?>
        </select></div>
        <?php $field('referencia', 'Referencia del expediente', $d['referencia'] ?? '', 'text', true);
        $field('fecha_del_valuo', 'Fecha del avalúo', $d['fecha_del_valuo'] ?? '', 'date', true); ?>
        <div class="form-group"><label for="estado">Estado</label><select id="estado" name="estado" class="form-control">
            <?php foreach (['Borrador', 'Revisado'] as $estado): ?><option <?= ($d['estado'] ?? '') === $estado ? 'selected' : '' ?>><?= $estado ?></option><?php endforeach; ?>
        </select></div>
    </div><p class="avaluo-help">Capture los datos del informe, calcule y revise antes de adoptar un valor. El expediente conserva los datos aunque después cambie el inmueble del proyecto.</p></div></div>
    <?php foreach (F::GENERALES as $title => $fields): ?>
    <details class="avaluo-section" <?= $title === 'Identificación y encargo' ? 'open' : '' ?>><summary><?= e($title) ?></summary><div class="avaluo-grid">
        <?php foreach ($fields as $key => $label): ?><div class="form-group"><label for="avaluo_<?= $key ?>"><?= e($label) ?></label><textarea id="avaluo_<?= $key ?>" name="<?= $key ?>" class="form-control" rows="2"><?= e($d[$key] ?? '') ?></textarea></div><?php endforeach; ?>
    </div></details><?php endforeach; ?>
    <details class="avaluo-section" open><summary>Terreno y método del costo</summary><div class="avaluo-grid">
        <?php foreach (['area_escritura' => 'Área según escritura (m²)', 'area_inspeccion' => 'Área según inspección (m²)', 'area_valuada' => 'Área a valuar (m²)', 'valor_vara' => 'Valor base zonal ($ / v²)'] as $key => $label) {
            $field($key, $label, $d[$key] ?? '', 'number', in_array($key, ['area_valuada', 'valor_vara']));
        } ?></div>
        <p class="avaluo-help">Conversión de la plantilla: 1 m² = 1.431024614 v². Se conservan los decimales durante el cálculo.</p>
        <div class="avaluo-grid"><?php foreach (C::FACTORES as $key) { $field("factores[{$key}]", 'Factor ' . $key, $d['factores'][$key] ?? 1, 'number', true); } ?></div>
        <h3>Construcciones y mejoras</h3><p class="avaluo-help">Reposición es el valor total del componente. Residual: 10%. K1 = edad / vida útil; K = K1 + (1 − K1) × K2. Para terreno sin construcción, quite los componentes.</p>
        <div id="avaluo-construcciones"><?php foreach ($d['construcciones'] ?? [] as $i => $c) { $row('construcciones', $i, $c, F::CONSTRUCCION); } ?></div>
        <button class="btn btn-outline" type="button" id="avaluo-add-construccion">Agregar construcción</button>
    </details>
    <details class="avaluo-section" <?= !empty($d['comparables']) ? 'open' : '' ?>><summary>Método comparativo de mercado</summary>
        <div class="avaluo-notice">Revisión del perito: se mantienen las fórmulas de Apaneca. El comparable 1 se compara con el sujeto; el 2 con el 1 y el 3 con el 2. El orden modifica el resultado. K2 del costo y Q de mercado se capturan por separado.</div>
        <h3>Inmueble sujeto</h3><div class="avaluo-grid"><?php foreach (F::SUJETO as $key => [$label, $type, $default]) { $field("sujeto[{$key}]", $label, $d['sujeto'][$key] ?? $default, $type); } ?></div>
        <p class="avaluo-help">Esta plantilla de mercado corresponde a inmuebles con construcción. Utilice tres comparables; el precio unitario se divide entre el área construida. Q es un coeficiente de 0 a 1 que debe indicar el perito.</p>
        <div id="avaluo-comparables"><?php foreach ($d['comparables'] ?? [] as $i => $c) { $row('comparables', $i, $c, F::COMPARABLE); } ?></div>
        <button class="btn btn-outline" type="button" id="avaluo-enable-comparables">Agregar los tres comparables</button>
        <button class="btn btn-outline" type="button" id="avaluo-clear-comparables">Quitar comparables</button>
    </details>
    <details class="avaluo-section" open><summary>Conclusión y derecho valuado</summary><div class="avaluo-grid">
        <div class="form-group"><label for="metodo">Método seleccionado</label><select name="metodo" id="metodo" class="form-control">
            <option value="costo" <?= ($d['metodo'] ?? '') === 'costo' ? 'selected' : '' ?>>Costo depreciado</option>
            <option value="comparativo" <?= ($d['metodo'] ?? '') === 'comparativo' ? 'selected' : '' ?>>Comparativo de mercado</option></select></div>
        <?php $field('valor_adoptado', 'Valor adoptado por el perito ($)', $d['valor_adoptado'] ?? '', 'number'); $field('porcentaje_derecho', 'Derecho valuado (%)', $d['porcentaje_derecho'] ?? 100, 'number', true); ?>
        <?php foreach (['conclusion' => 'Conclusión y justificación del valor adoptado', 'situacion_juridica' => 'Situación jurídica / titular del derecho', 'revision_perito' => 'Revisión del perito sobre fórmulas y coeficientes'] as $key => $label): ?>
            <div class="form-group"><label for="<?= $key ?>"><?= e($label) ?></label><textarea class="form-control" id="<?= $key ?>" name="<?= $key ?>" rows="4"><?= e($d[$key] ?? '') ?></textarea></div>
        <?php endforeach; ?></div><p class="avaluo-help">El valor adoptado es una decisión explícita del perito. Marcar Revisado no firma el documento. Los mapas, fotografías, planos y PDF se adjuntan después de guardar.</p>
    </details>
    <section class="avaluo-section" aria-label="Resultados del cálculo"><button class="btn btn-outline" type="button" id="avaluo-calculate">Calcular y verificar</button>
        <div id="avaluo-preview" aria-live="polite"><p class="avaluo-help">Complete los datos del costo para calcular. Guardar vuelve a calcular en el servidor.</p></div></section>
    <div class="form-actions-toolbar"><a href="<?= url('valuo/index') ?>" class="btn btn-outline">Cancelar</a><button type="submit" class="btn btn-primary">Guardar avalúo</button></div>
</form>
<template id="avaluo-construccion-template"><?php $row('construcciones', 0, [], F::CONSTRUCCION); ?></template>
<template id="avaluo-comparables-template"><?php for ($i = 0; $i < 3; $i++) { $row('comparables', $i, [], F::COMPARABLE); } ?></template>
