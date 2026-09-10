<?php /** Vista: Formulario de Cliente (crear/editar) */ 
$formState = \Core\FormState::take($action);
$formValues = $formState['values'] ?? ($cliente ?? []);
$formErrors = $formState['errors'] ?? [];
?>

<div class="page-header">
    <h2>
        <i class="fas fa-user-tie"></i>
        <?= $cliente ? 'Editar Cliente' : 'Nuevo Cliente' ?>
    </h2>
    <a href="<?= url('cliente/index') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="form-card-clean">
    <div class="form-card-header">
        <h3>
            <i class="fas fa-user-circle"></i>
            <?= $cliente ? 'Actualizar Cliente' : 'Datos del Cliente' ?>
        </h3>
    </div>

    <form action="<?= $action ?>" method="POST" id="clienteForm">
        <?= csrf_field() ?>
        <?php require dirname(__DIR__) . '/partials/form_errors.php'; ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="nombre">
                    <i class="fas fa-user"></i> Nombre Completo <span class="required-mark">*</span>
                </label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       value="<?= e($formValues['nombre'] ?? '') ?>" required maxlength="90"
                       placeholder="Nombre completo del cliente"
                       data-no-numbers="true" pattern="^[^0-9]+$"
                       title="El nombre no puede contener números" autocomplete="off"
                       oninput="this.value = this.value.replace(/[0-9]/g, '')"
                       onkeydown="if(event.key >= '0' && event.key <= '9' && !event.ctrlKey && !event.metaKey) event.preventDefault();">
                <small class="field-hint" id="nombreHint"><i class="fas fa-info-circle"></i> Solo letras y espacios (no se permiten números)</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dui">
                        <i class="fas fa-id-card"></i> DUI <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="dui" name="dui" class="form-control" 
                           value="<?= e($formValues['dui'] ?? '') ?>" required maxlength="10"
                           placeholder="00000000-0" data-mask="dui" autocomplete="off"
                           pattern="\d{8}-\d" title="DUI salvadoreño (ej. 00000000-0)">
                    <small class="field-hint" id="duiHint"><i class="fas fa-magic"></i> Guion automático (00000000-0)</small>
                </div>
                <div class="form-group">
                    <label for="telefono">
                        <i class="fas fa-phone"></i> Teléfono (opcional)
                    </label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" 
                           value="<?= e($formValues['telefono'] ?? '') ?>" maxlength="9"
                           placeholder="0000-0000" data-mask="phone" autocomplete="off"
                           pattern="\d{4}-\d{4}" title="Teléfono salvadoreño (ej. 0000-0000)">
                    <small class="field-hint" id="telefonoHint"><i class="fas fa-magic"></i> Guion automático (0000-0000)</small>
                </div>
            </div>

            <div class="form-group">
                <label for="correo_electronico">
                    <i class="fas fa-envelope"></i> Correo Electrónico (opcional)
                </label>
                <input type="email" id="correo_electronico" name="correo_electronico" class="form-control" 
                       value="<?= e($formValues['correo_electronico'] ?? '') ?>" maxlength="50"
                       placeholder="usuario@ejemplo.com" data-validate="email" autocomplete="off"
                       pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}">
                <small class="field-hint" id="emailHint"><i class="fas fa-shield-alt"></i> Debe ser un correo real (ej. nombre@empresa.com)</small>
            </div>

            <div class="form-group">
                <label for="direccion">
                    <i class="fas fa-map-marker-alt"></i> Dirección <span class="required-mark">*</span>
                </label>
                <textarea id="direccion" name="direccion" class="form-control" 
                          rows="3" required placeholder="Dirección completa del cliente"><?= e($formValues['direccion'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-actions-toolbar">
            <a href="<?= url('cliente/index') ?>" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= $cliente ? 'Actualizar' : 'Guardar' ?>
            </button>
        </div>
    </form>
</div>
