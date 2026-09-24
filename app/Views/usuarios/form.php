<?php /** Vista: Formulario de Usuario */ 
$formState = \Core\FormState::take($action);
$formValues = $formState['values'] ?? ($usuario ?? []);
$formErrors = $formState['errors'] ?? [];
$empleadosConUsuario = $empleadosConUsuario ?? [];
?>

<div class="page-header">
    <h2><i class="fas fa-user-shield"></i> <?= $usuario ? 'Editar Usuario' : 'Nuevo Usuario' ?></h2>
    <a href="<?= url('usuario/index') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="form-card-clean">
    <div class="form-card-header">
        <h3><i class="fas fa-user-lock"></i> <?= $usuario ? 'Actualizar Usuario' : 'Datos del Usuario' ?></h3>
    </div>

    <form action="<?= $action ?>" method="POST" id="usuarioForm">
        <?= csrf_field() ?>
        <?php require dirname(__DIR__) . '/partials/form_errors.php'; ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="id_empleado">
                    <i class="fas fa-users-cog"></i> Empleado Asociado <span class="required-mark">*</span>
                </label>
                <div style="display: flex; gap: 8px; align-items: stretch;">
                    <select id="id_empleado" name="id_empleado" class="form-control" required style="flex: 1;">
                        <option value="">Seleccione un empleado</option>
                        <?php foreach ($empleados as $emp): 
                            $tieneUsuario = in_array((int)$emp['id_empleado'], $empleadosConUsuario, true);
                            $esElMismo = ($usuario && (int)$usuario['id_empleado'] === (int)$emp['id_empleado']);
                            $deshabilitado = $tieneUsuario && !$esElMismo;
                        ?>
                            <option value="<?= $emp['id_empleado'] ?>" 
                                    <?= ($formValues['id_empleado'] ?? ($seleccionEmpleado ?? '')) == $emp['id_empleado'] ? 'selected' : '' ?>
                                    <?= $deshabilitado ? 'disabled style="color: #9ca3af; background-color: #f3f4f6;"' : '' ?>>
                                <?= e($emp['nombre_completo']) ?> — <?= e($emp['cargo']) ?><?= $deshabilitado ? ' (Ya tiene usuario)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-primary" id="btnAbrirModalEmpleado" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-search"></i> Buscar Empleado
                    </button>
                </div>
                <small class="field-hint"><i class="fas fa-info-circle"></i> Seleccione un empleado de la lista o use "Buscar Empleado" para buscarlo en la ventana flotante. Cada empleado solo puede tener un único usuario.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">
                        <i class="fas fa-user"></i> Nombre de Usuario <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre" class="form-control" 
                           value="<?= e($formValues['nombre'] ?? '') ?>" required maxlength="60"
                           placeholder="Nombre de usuario único">
                    <small class="field-hint"><i class="fas fa-fingerprint"></i> El nombre de usuario debe ser único en el sistema.</small>
                </div>
                <div class="form-group">
                    <label for="correo">
                        <i class="fas fa-envelope"></i> Correo Electrónico <span class="required-mark">*</span>
                    </label>
                    <input type="email" id="correo" name="correo" class="form-control" 
                           value="<?= e($formValues['correo'] ?? '') ?>" required maxlength="50"
                           placeholder="correo@topsa.com">
                    <small class="field-hint"><i class="fas fa-at"></i> El correo debe ser único y pertenecer a una cuenta válida.</small>
                </div>
            </div>

            <p class="field-hint" style="margin-bottom: 1rem;">
                <i class="fas fa-info-circle"></i> 
                <?= $usuario ? 'La edición de datos conserva la contraseña actual.' : 'Se generará una contraseña temporal automáticamente al guardar. Podrá compartirla por Gmail o WhatsApp.' ?>
            </p>

            <div class="form-row">
                <div class="form-group">
                    <label for="rol">
                        <i class="fas fa-crown"></i> Rol <span class="required-mark">*</span>
                    </label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="Empleado" <?= ($formValues['rol'] ?? 'Empleado') === 'Empleado' ? 'selected' : '' ?>>Empleado</option>
                        <option value="Administrador" <?= ($formValues['rol'] ?? '') === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estado_de_cuenta">
                        <i class="fas fa-toggle-on"></i> Estado <span class="required-mark">*</span>
                    </label>
                    <select id="estado_de_cuenta" name="estado_de_cuenta" class="form-control" required>
                        <option value="Activo" <?= ($formValues['estado_de_cuenta'] ?? 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($formValues['estado_de_cuenta'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
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

<!-- ─── Ventana Flotante (Modal) para Buscar y Asignar Empleado ─── -->
<div id="modalBuscarEmpleado" style="display: none; position: fixed; inset: 0; background: rgba(15, 36, 18, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: var(--radius-xl, 12px); width: 92%; max-width: 720px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: var(--shadow-xl, 0 20px 25px -5px rgba(0, 0, 0, 0.1)); overflow: hidden; border: 1px solid var(--gray-200, #e5e7eb);">
        
        <!-- Modal Header -->
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--gray-200, #e5e7eb); display: flex; justify-content: space-between; align-items: center; background: #ffffff;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--primary-800, #14532d); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-users-cog" style="color: var(--primary-600, #16a34a);"></i> Buscar y Asignar Empleado
            </h3>
            <button type="button" id="btnCerrarModalX" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-400, #9ca3af); line-height: 1;">&times;</button>
        </div>

        <!-- Modal Body -->
        <div style="padding: 1.25rem; overflow-y: auto; flex: 1;">
            <div style="margin-bottom: 1rem; position: relative;">
                <div class="input-with-addon" style="width: 100%;">
                    <span class="input-addon"><i class="fas fa-search"></i></span>
                    <input type="text" id="modalBuscarEmpleadoInput" class="form-control" 
                           placeholder="Buscar por nombre, DUI, cargo o teléfono..." autocomplete="off">
                </div>
            </div>

            <div style="max-height: 340px; overflow-y: auto; border: 1px solid var(--gray-200, #e5e7eb); border-radius: var(--radius-md, 8px);">
                <table class="table" style="width: 100%; margin-bottom: 0; font-size: 0.875rem;">
                    <thead>
                        <tr style="background: var(--gray-50, #f9fafb); position: sticky; top: 0; z-index: 1;">
                            <th style="padding: 10px 12px;">Empleado</th>
                            <th style="padding: 10px 12px;">DUI</th>
                            <th style="padding: 10px 12px;">Cargo</th>
                            <th style="padding: 10px 12px;">Estado</th>
                            <th style="padding: 10px 12px; text-align: right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="modalEmpleadosTbody">
                        <?php foreach ($empleados as $emp): 
                            $tieneUsuario = in_array((int)$emp['id_empleado'], $empleadosConUsuario, true);
                            $esElMismo = ($usuario && (int)$usuario['id_empleado'] === (int)$emp['id_empleado']);
                            $deshabilitado = $tieneUsuario && !$esElMismo;
                        ?>
                            <tr class="modal-empleado-fila" data-search="<?= strtolower(e($emp['nombre_completo'] . ' ' . $emp['dui'] . ' ' . $emp['cargo'] . ' ' . ($emp['telefono'] ?? '') . ' ' . ($emp['correo_electronico'] ?? ''))) ?>">
                                <td style="padding: 10px 12px;">
                                    <strong><?= e($emp['nombre_completo']) ?></strong>
                                    <?php if (!empty($emp['correo_electronico'])): ?>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= e($emp['correo_electronico']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 12px;"><span class="badge badge-outline"><?= e($emp['dui']) ?></span></td>
                                <td style="padding: 10px 12px;"><?= e($emp['cargo']) ?></td>
                                <td style="padding: 10px 12px;">
                                    <?php if ($deshabilitado): ?>
                                        <span class="badge badge-warning" style="font-size: 0.75rem; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                                            <i class="fas fa-user-check"></i> Ya tiene usuario
                                        </span>
                                    <?php elseif ($esElMismo): ?>
                                        <span class="badge badge-info" style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1;">
                                            <i class="fas fa-check"></i> Asignado actual
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success" style="font-size: 0.75rem; background: #dcfce7; color: #15803d;">
                                            <i class="fas fa-check-circle"></i> Disponible
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 12px; text-align: right;">
                                    <?php if ($deshabilitado): ?>
                                        <button type="button" class="btn btn-sm btn-outline disabled" disabled style="opacity: 0.6; cursor: not-allowed;" title="Este empleado ya tiene una cuenta de usuario">
                                            <i class="fas fa-ban"></i> Ocupado
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-primary btn-asignar-empleado"
                                                data-id="<?= $emp['id_empleado'] ?>"
                                                data-nombre="<?= e($emp['nombre_completo']) ?>"
                                                data-correo="<?= e($emp['correo_electronico'] ?? '') ?>">
                                            <i class="fas fa-check"></i> Asignar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="modalSinResultados" style="display: none; padding: 2rem; text-align: center; color: var(--gray-500, #6b7280);">
                    <i class="fas fa-search" style="font-size: 1.75rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p style="margin: 0;">No se encontraron empleados coincidentes.</p>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="padding: 0.75rem 1.25rem; border-top: 1px solid var(--gray-200, #e5e7eb); background: var(--gray-50, #f9fafb); display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-outline" id="btnCerrarModalBoton">Cerrar</button>
        </div>
    </div>
</div>

<script>
(function() {
    function initUsuarioForm() {
        const modal = document.getElementById('modalBuscarEmpleado');
        const inputBuscar = document.getElementById('modalBuscarEmpleadoInput');
        const selectEmpleado = document.getElementById('id_empleado');
        const inputCorreo = document.getElementById('correo');
        const form = document.getElementById('usuarioForm');

        function abrirModal() {
            if (!modal) return;
            modal.style.display = 'flex';
            if (inputBuscar) {
                inputBuscar.value = '';
                filtrar('');
                setTimeout(() => inputBuscar.focus(), 60);
            }
        }

        function cerrarModal() {
            if (modal) modal.style.display = 'none';
        }

        function filtrar(termino) {
            const clean = termino.toLowerCase().trim();
            const filas = document.querySelectorAll('.modal-empleado-fila');
            let encontrados = 0;
            filas.forEach(f => {
                const coincide = f.dataset.search.includes(clean);
                f.style.display = coincide ? '' : 'none';
                if (coincide) encontrados++;
            });
            const sinResultados = document.getElementById('modalSinResultados');
            if (sinResultados) {
                sinResultados.style.display = encontrados === 0 ? 'block' : 'none';
            }
        }

        document.getElementById('btnAbrirModalEmpleado')?.addEventListener('click', abrirModal);
        document.getElementById('btnCerrarModalX')?.addEventListener('click', cerrarModal);
        document.getElementById('btnCerrarModalBoton')?.addEventListener('click', cerrarModal);

        modal?.addEventListener('click', (e) => {
            if (e.target === modal) cerrarModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal && modal.style.display !== 'none') {
                cerrarModal();
            }
        });

        inputBuscar?.addEventListener('input', (e) => {
            filtrar(e.target.value);
        });

        // Asignar empleado desde el modal
        document.querySelectorAll('.btn-asignar-empleado').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const nombre = btn.dataset.nombre;
                const correo = btn.dataset.correo;

                if (selectEmpleado) {
                    selectEmpleado.value = id;
                    selectEmpleado.dispatchEvent(new Event('change'));
                }

                // Si el campo de correo está vacío y el empleado tiene correo registrado, sugerirlo
                if (inputCorreo && !inputCorreo.value.trim() && correo) {
                    inputCorreo.value = correo;
                }

                cerrarModal();
                if (typeof Toast !== 'undefined' && Toast.success) {
                    Toast.success(`Empleado "${nombre}" seleccionado.`);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUsuarioForm);
    } else {
        initUsuarioForm();
    }
})();
</script>
