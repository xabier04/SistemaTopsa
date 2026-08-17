<?php /** Vista: Formulario de Usuario */ ?>

<div class="page-header">
    <h2><i class="fas fa-user-shield"></i> <?= $usuario ? 'Editar Usuario' : 'Nuevo Usuario' ?></h2>
    <a href="<?= url('usuario/index') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="id_empleado"><i class="fas fa-users-cog"></i> Empleado Asociado</label>
                <select id="id_empleado" name="id_empleado" class="form-control" required>
                    <option value="">Seleccione un empleado</option>
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id_empleado'] ?>" <?= ($usuario['id_empleado'] ?? '') == $emp['id_empleado'] ? 'selected' : '' ?>>
                            <?= e($emp['nombre_completo']) ?> — <?= e($emp['cargo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombre"><i class="fas fa-user"></i> Nombre de Usuario</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" 
                           value="<?= e($usuario['nombre'] ?? '') ?>" required maxlength="60">
                </div>
                <div class="form-group">
                    <label for="correo"><i class="fas fa-envelope"></i> Correo</label>
                    <input type="email" id="correo" name="correo" class="form-control" 
                           value="<?= e($usuario['correo'] ?? '') ?>" required maxlength="50">
                </div>
            </div>

            <div class="form-group">
                <label for="contrasena"><i class="fas fa-lock"></i> Contraseña <?= $usuario ? '(dejar vacío para no cambiar)' : '' ?></label>
                <input type="password" id="contrasena" name="contrasena" class="form-control" 
                       <?= $usuario ? '' : 'required' ?> minlength="6" placeholder="Mínimo 6 caracteres">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="rol"><i class="fas fa-crown"></i> Rol</label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="Empleado" <?= ($usuario['rol'] ?? 'Empleado') === 'Empleado' ? 'selected' : '' ?>>Empleado</option>
                        <option value="Administrador" <?= ($usuario['rol'] ?? '') === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estado_de_cuenta"><i class="fas fa-toggle-on"></i> Estado</label>
                    <select id="estado_de_cuenta" name="estado_de_cuenta" class="form-control" required>
                        <option value="Activo" <?= ($usuario['estado_de_cuenta'] ?? 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($usuario['estado_de_cuenta'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        <option value="Bloqueado" <?= ($usuario['estado_de_cuenta'] ?? '') === 'Bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: var(--space-3); justify-content: flex-end; margin-top: var(--space-6);">
                <a href="<?= url('usuario/index') ?>" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $usuario ? 'Actualizar' : 'Guardar' ?></button>
            </div>
        </form>
    </div>
</div>
