<?php /** Vista: Formulario de Cliente (crear/editar) */ ?>

<div class="page-header">
    <h2>
        <i class="fas fa-user-tie"></i>
        <?= $cliente ? 'Editar Cliente' : 'Nuevo Cliente' ?>
    </h2>
    <a href="<?= url('cliente/index') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST" id="clienteForm">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="nombre"><i class="fas fa-user"></i> Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       value="<?= e($cliente['nombre'] ?? '') ?>" required maxlength="90"
                       placeholder="Nombre del cliente">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dui"><i class="fas fa-id-card"></i> DUI</label>
                    <input type="text" id="dui" name="dui" class="form-control" 
                           value="<?= e($cliente['dui'] ?? '') ?>" required maxlength="10"
                           placeholder="00000000-0">
                </div>
                <div class="form-group">
                    <label for="telefono"><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" 
                           value="<?= e($cliente['telefono'] ?? '') ?>" maxlength="9"
                           placeholder="0000-0000">
                </div>
            </div>

            <div class="form-group">
                <label for="correo_electronico"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                <input type="email" id="correo_electronico" name="correo_electronico" class="form-control" 
                       value="<?= e($cliente['correo_electronico'] ?? '') ?>" maxlength="50"
                       placeholder="correo@ejemplo.com">
            </div>

            <div class="form-group">
                <label for="direccion"><i class="fas fa-map-marker-alt"></i> Dirección</label>
                <textarea id="direccion" name="direccion" class="form-control" 
                          rows="3" placeholder="Dirección completa"><?= e($cliente['direccion'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: var(--space-3); justify-content: flex-end; margin-top: var(--space-6);">
                <a href="<?= url('cliente/index') ?>" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $cliente ? 'Actualizar' : 'Guardar' ?>
                </button>
            </div>
        </form>
    </div>
</div>
