<?php /** Vista: Formulario de Empleado */ ?>

<div class="page-header">
    <h2>
        <i class="fas fa-users-cog"></i>
        <?= $empleado ? 'Editar Empleado' : 'Nuevo Empleado' ?>
    </h2>
    <a href="<?= url('empleado/index') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="nombre_completo"><i class="fas fa-user"></i> Nombre Completo</label>
                <input type="text" id="nombre_completo" name="nombre_completo" class="form-control" 
                       value="<?= e($empleado['nombre_completo'] ?? '') ?>" required maxlength="60">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dui"><i class="fas fa-id-card"></i> DUI</label>
                    <input type="text" id="dui" name="dui" class="form-control" 
                           value="<?= e($empleado['dui'] ?? '') ?>" required maxlength="10"
                           placeholder="00000000-0">
                </div>
                <div class="form-group">
                    <label for="telefono"><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" 
                           value="<?= e($empleado['telefono'] ?? '') ?>" maxlength="9"
                           placeholder="0000-0000">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cargo"><i class="fas fa-briefcase"></i> Cargo</label>
                    <input type="text" id="cargo" name="cargo" class="form-control" 
                           value="<?= e($empleado['cargo'] ?? '') ?>" required maxlength="90"
                           placeholder="Ej: Ingeniero Civil, Topógrafo...">
                </div>
                <div class="form-group">
                    <label for="estado"><i class="fas fa-toggle-on"></i> Estado</label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="Activo" <?= ($empleado['estado'] ?? 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($empleado['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: var(--space-3); justify-content: flex-end; margin-top: var(--space-6);">
                <a href="<?= url('empleado/index') ?>" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $empleado ? 'Actualizar' : 'Guardar' ?>
                </button>
            </div>
        </form>
    </div>
</div>
