<?php /** Vista: Formulario de Empleado */ 
$formState = \Core\FormState::take($action);
$formValues = $formState['values'] ?? ($empleado ?? []);
$formErrors = $formState['errors'] ?? [];
?>

<div class="page-header">
    <h2>
        <i class="fas fa-users-cog"></i>
        <?= $empleado ? 'Editar Empleado' : 'Nuevo Empleado' ?>
    </h2>
    <a href="<?= url('empleado/index') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="form-card-clean">
    <div class="form-card-header">
        <h3>
            <i class="fas fa-id-badge"></i>
            <?= $empleado ? 'Actualizar Datos' : 'Información del Empleado' ?>
        </h3>
        <?php if ($empleado): ?>
            <span class="badge-dot <?= ($formValues['estado'] === 'Activo') ? 'dot-success' : 'dot-danger' ?>">
                <?= e($formValues['estado']) ?>
            </span>
        <?php endif; ?>
    </div>

    <form action="<?= $action ?>" method="POST">
        <?= csrf_field() ?>
        <?php require dirname(__DIR__) . '/partials/form_errors.php'; ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="nombre_completo">
                    <i class="fas fa-user"></i> Nombre Completo
                </label>
                <input type="text" id="nombre_completo" name="nombre_completo" class="form-control" 
                       value="<?= e($formValues['nombre_completo'] ?? '') ?>" required maxlength="60"
                       placeholder="Nombre completo">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dui">
                        <i class="fas fa-id-card"></i> DUI
                    </label>
                    <input type="text" id="dui" name="dui" class="form-control" 
                           value="<?= e($formValues['dui'] ?? '') ?>" required maxlength="10"
                           placeholder="00000000-0" data-mask="dui" pattern="\d{8}-\d">
                </div>
                <div class="form-group">
                    <label for="telefono">
                        <i class="fas fa-phone"></i> Teléfono
                    </label>
                    <input type="text" id="telefono" name="telefono" class="form-control" 
                           value="<?= e($formValues['telefono'] ?? '') ?>" maxlength="9"
                           placeholder="0000-0000" data-mask="phone" pattern="\d{4}-\d{4}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cargo">
                        <i class="fas fa-briefcase"></i> Cargo
                    </label>
                    <input type="text" id="cargo" name="cargo" class="form-control" 
                           value="<?= e($formValues['cargo'] ?? '') ?>" required maxlength="90"
                           placeholder="Ej. Topógrafo, Ingeniero...">
                </div>
                <div class="form-group">
                    <label for="estado">
                        <i class="fas fa-toggle-on"></i> Estado
                    </label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="Activo" <?= ($formValues['estado'] ?? 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($formValues['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions-toolbar">
            <a href="<?= url('empleado/index') ?>" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= $empleado ? 'Actualizar' : 'Guardar' ?>
            </button>
        </div>
    </form>
</div>
