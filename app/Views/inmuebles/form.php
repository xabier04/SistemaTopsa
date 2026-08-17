<?php /** Vista: Formulario de Inmueble */ ?>

<div class="page-header">
    <h2><i class="fas fa-building"></i> <?= $inmueble ? 'Editar Inmueble' : 'Nuevo Inmueble' ?></h2>
    <a href="<?= url('inmueble/index') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="id_cliente"><i class="fas fa-user-tie"></i> Propietario (Cliente)</label>
                <select id="id_cliente" name="id_cliente" class="form-control" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>" <?= ($inmueble['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>>
                            <?= e($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="matricula"><i class="fas fa-hashtag"></i> Matrícula</label>
                    <input type="text" id="matricula" name="matricula" class="form-control" 
                           value="<?= e($inmueble['matricula'] ?? '') ?>" required maxlength="30">
                </div>
                <div class="form-group">
                    <label for="tipo_inmueble"><i class="fas fa-home"></i> Tipo de Inmueble</label>
                    <select id="tipo_inmueble" name="tipo_inmueble" class="form-control" required>
                        <option value="">Seleccione</option>
                        <?php foreach (['Terreno', 'Casa', 'Edificio', 'Local Comercial', 'Bodega', 'Otro'] as $tipo): ?>
                            <option value="<?= $tipo ?>" <?= ($inmueble['tipo_inmueble'] ?? '') === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="area"><i class="fas fa-ruler-combined"></i> Área (m²)</label>
                <input type="number" id="area" name="area" class="form-control" 
                       value="<?= e($inmueble['area'] ?? '') ?>" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="direccion"><i class="fas fa-map-marker-alt"></i> Dirección</label>
                <textarea id="direccion" name="direccion" class="form-control" rows="3" required><?= e($inmueble['direccion'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: var(--space-3); justify-content: flex-end; margin-top: var(--space-6);">
                <a href="<?= url('inmueble/index') ?>" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $inmueble ? 'Actualizar' : 'Guardar' ?></button>
            </div>
        </form>
    </div>
</div>
