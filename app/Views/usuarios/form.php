<?php /** Vista: Formulario de Usuario */ 
$formState = \Core\FormState::take($action);
$formValues = $formState['values'] ?? ($usuario ?? []);
$formErrors = $formState['errors'] ?? [];
?>

<div class="page-header">
    <h2><i class="fas fa-user-shield"></i> <?= $usuario ? 'Editar Usuario' : 'Nuevo Usuario' ?></h2>
    <a href="<?= url('usuario/index') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="form-card-clean">
    <div class="form-card-header">
        <h3><i class="fas fa-user-lock"></i> <?= $usuario ? 'Actualizar Usuario' : 'Datos del Usuario' ?></h3>
    </div>

    <form action="<?= $action ?>" method="POST">
        <?= csrf_field() ?>
        <?php require dirname(__DIR__) . '/partials/form_errors.php'; ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="id_empleado">
                    <i class="fas fa-users-cog"></i> Empleado Asociado
                </label>
                <select id="id_empleado" name="id_empleado" class="form-control" required>
                    <option value="">Seleccione un empleado</option>
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id_empleado'] ?>" <?= ($formValues['id_empleado'] ?? ($seleccionEmpleado ?? '')) == $emp['id_empleado'] ? 'selected' : '' ?>>
                            <?= e($emp['nombre_completo']) ?> — <?= e($emp['cargo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">
                        <i class="fas fa-user"></i> Nombre de Usuario
                    </label>
                    <input type="text" id="nombre" name="nombre" class="form-control" 
                           value="<?= e($formValues['nombre'] ?? '') ?>" required maxlength="60"
                           placeholder="Nombre de usuario">
                </div>
                <div class="form-group">
                    <label for="correo">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                    <input type="email" id="correo" name="correo" class="form-control" 
                           value="<?= e($formValues['correo'] ?? '') ?>" required maxlength="50"
                           placeholder="correo@topsa.com">
                </div>
            </div>

            <p class="field-hint" style="margin-bottom: 1rem;"><?= $usuario ? 'La edición de datos conserva la contraseña actual.' : 'Se generará una contraseña temporal automáticamente al guardar. Podrá compartirla por correo o WhatsApp.' ?></p>

            <div class="form-row">
                <div class="form-group">
                    <label for="rol">
                        <i class="fas fa-crown"></i> Rol
                    </label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="Empleado" <?= ($formValues['rol'] ?? 'Empleado') === 'Empleado' ? 'selected' : '' ?>>Empleado</option>
                        <option value="Administrador" <?= ($formValues['rol'] ?? '') === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estado_de_cuenta">
                        <i class="fas fa-toggle-on"></i> Estado
                    </label>
                    <select id="estado_de_cuenta" name="estado_de_cuenta" class="form-control" required>
                        <option value="Activo" <?= ($formValues['estado_de_cuenta'] ?? 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($formValues['estado_de_cuenta'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        <option value="Bloqueado" <?= ($formValues['estado_de_cuenta'] ?? '') === 'Bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions-toolbar">
            <a href="<?= url('usuario/index') ?>" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $usuario ? 'Actualizar' : 'Guardar' ?></button>
        </div>
    </form>
</div>

<?php if ($usuario): ?>
<div class="form-card-clean">
    <div class="form-card-body">
        <h3>Contraseña de la cuenta</h3>
        <p><?= !empty($usuario['requiere_cambio_contrasena']) ? 'Temporal: pendiente de cambio por el usuario.' : 'Contraseña establecida.' ?></p>
        <p>Generar una nueva contraseña temporal invalida la contraseña anterior inmediatamente.</p>
        <form method="POST" action="<?= url("usuario/regenerar/{$usuario['id_usuario']}") ?>">
            <?= csrf_field() ?>
            <button class="btn btn-outline" type="submit">Generar nueva contraseña temporal</button>
            <a class="btn btn-outline" href="<?= url('usuario/cambiarClave') ?>">Cambiar con contraseña actual</a>
        </form>
    </div>
</div>
<?php endif; ?>
