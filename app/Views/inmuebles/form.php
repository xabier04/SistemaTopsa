<?php /** Vista: Formulario de Inmueble */ ?>

<div class="page-header">
    <h2>
        <i class="fas fa-building"></i>
        <?= $inmueble ? 'Editar Inmueble' : 'Nuevo Inmueble' ?>
    </h2>
    <a href="<?= url('inmueble/index') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="form-card-clean">
    <div class="form-card-header">
        <h3>
            <i class="fas fa-map-marked-alt"></i>
            <?= $inmueble ? 'Actualizar Inmueble' : 'Datos del Inmueble' ?>
        </h3>
    </div>

    <form action="<?= $action ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="id_cliente">
                    <i class="fas fa-user-tie"></i> Propietario (Cliente) <span class="required-mark">*</span>
                </label>
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
                    <label for="matricula">
                        <i class="fas fa-hashtag"></i> Matrícula (8 números) <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="matricula" name="matricula" class="form-control" 
                           value="<?= e($inmueble['matricula'] ?? '') ?>" required maxlength="8"
                           placeholder="12345678" data-mask="matricula" inputmode="numeric"
                           pattern="\d{8}" title="La matrícula debe contener exactamente 8 números" autocomplete="off">
                    <small class="field-hint" id="matriculaHint"><i class="fas fa-fingerprint"></i> Solo 8 números (sin letras ni guiones)</small>
                </div>
                <div class="form-group">
                    <label for="tipo_inmueble">
                        <i class="fas fa-home"></i> Tipo de Inmueble <span class="required-mark">*</span>
                    </label>
                    <select id="tipo_inmueble" name="tipo_inmueble" class="form-control" required>
                        <option value="">Seleccione</option>
                        <?php foreach (['Terreno', 'Casa', 'Edificio', 'Local Comercial', 'Bodega', 'Otro'] as $tipo): ?>
                            <option value="<?= $tipo ?>" <?= ($inmueble['tipo_inmueble'] ?? '') === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="area">
                    <i class="fas fa-ruler-combined"></i> Área (m²) <span class="required-mark">*</span>
                </label>
                <div class="input-with-addon">
                    <span class="input-addon">m²</span>
                    <input type="number" id="area" name="area" class="form-control" 
                           value="<?= e($inmueble['area'] ?? '') ?>" required step="0.01" min="0"
                           placeholder="0.00">
                </div>
            </div>

            <div class="form-group">
                <label for="direccion">
                    <i class="fas fa-map-marker-alt"></i> Dirección <span class="required-mark">*</span>
                </label>
                <textarea id="direccion" name="direccion" class="form-control" rows="3" required
                          placeholder="Dirección completa del inmueble"><?= e($inmueble['direccion'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-actions-toolbar">
            <a href="<?= url('inmueble/index') ?>" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= $inmueble ? 'Actualizar' : 'Guardar' ?>
            </button>
        </div>
    </form>
</div>
