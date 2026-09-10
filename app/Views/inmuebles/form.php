<?php /** Vista: Formulario de Inmueble */ 
$formState = \Core\FormState::take($action);
$formValues = $formState['values'] ?? ($inmueble ?? []);
$formErrors = $formState['errors'] ?? [];
?>

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

    <form action="<?= $action ?>" method="POST" id="inmuebleForm">
        <?= csrf_field() ?>
        <?php require dirname(__DIR__) . '/partials/form_errors.php'; ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="id_cliente">
                    <i class="fas fa-user-tie"></i> Propietario (Cliente) <span class="required-mark">*</span>
                </label>
                <div style="display: flex; gap: 8px; align-items: stretch;">
                    <select id="id_cliente" name="id_cliente" class="form-control" required style="flex: 1;">
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>" <?= ($formValues['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>>
                                <?= e($c['nombre']) ?> (DUI: <?= e($c['dui']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-primary" id="btnAbrirModalCliente" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-search"></i> Buscar Cliente
                    </button>
                </div>
                <small class="field-hint"><i class="fas fa-info-circle"></i> Seleccione un cliente de la lista o use "Buscar Cliente" para buscarlo en la ventana flotante</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="matricula">
                        <i class="fas fa-hashtag"></i> Matrícula (8 números) <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="matricula" name="matricula" class="form-control" 
                           value="<?= e($formValues['matricula'] ?? '') ?>" required maxlength="8"
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
                            <option value="<?= $tipo ?>" <?= ($formValues['tipo_inmueble'] ?? '') === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
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
                           value="<?= e($formValues['area'] ?? '') ?>" required step="0.01" min="50"
                           placeholder="50.00"
                           oninvalid="this.setCustomValidity('El área mínima debe ser de al menos 50 m²')"
                           oninput="this.setCustomValidity('')">
                </div>
                <small class="field-hint" id="areaHint"><i class="fas fa-info-circle"></i> El área mínima requerida es de 50.00 m²</small>
            </div>

            <?php require dirname(__DIR__) . '/partials/ubicacion.php'; ?>
            <div class="form-group">
                <label for="direccion">
                    <i class="fas fa-map-marker-alt"></i> Dirección / detalle adicional <span class="required-mark">*</span>
                </label>
                <textarea id="direccion" name="direccion" class="form-control" rows="3" required
                          placeholder="Colonia, calle, número de casa, referencias u otros detalles"><?= e($formValues['direccion'] ?? '') ?></textarea>
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

<!-- ─── Ventana Flotante (Modal) para Buscar y Agregar Cliente ─── -->
<div id="modalBuscarCliente" style="display: none; position: fixed; inset: 0; background: rgba(15, 36, 18, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: var(--radius-xl, 12px); width: 92%; max-width: 650px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: var(--shadow-xl, 0 20px 25px -5px rgba(0, 0, 0, 0.1)); overflow: hidden; border: 1px solid var(--gray-200, #e5e7eb);">
        
        <!-- Modal Header -->
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--gray-200, #e5e7eb); display: flex; justify-content: space-between; align-items: center; background: #ffffff;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--primary-800, #14532d); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-users" style="color: var(--primary-600, #16a34a);"></i> Buscar y Asignar Cliente
            </h3>
            <button type="button" id="btnCerrarModalX" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-400, #9ca3af); line-height: 1;">&times;</button>
        </div>

        <!-- Modal Body -->
        <div style="padding: 1.25rem; overflow-y: auto; flex: 1;">
            <div style="margin-bottom: 1rem; position: relative;">
                <div class="input-with-addon" style="width: 100%;">
                    <span class="input-addon"><i class="fas fa-search"></i></span>
                    <input type="text" id="modalBuscarClienteInput" class="form-control" 
                           placeholder="Buscar por nombre, DUI o teléfono..." autocomplete="off">
                </div>
            </div>

            <div style="max-height: 320px; overflow-y: auto; border: 1px solid var(--gray-200, #e5e7eb); border-radius: var(--radius-md, 8px);">
                <table class="table" style="width: 100%; margin-bottom: 0; font-size: 0.875rem;">
                    <thead>
                        <tr style="background: var(--gray-50, #f9fafb); position: sticky; top: 0; z-index: 1;">
                            <th style="padding: 10px 12px;">Cliente</th>
                            <th style="padding: 10px 12px;">DUI</th>
                            <th style="padding: 10px 12px;">Teléfono</th>
                            <th style="padding: 10px 12px; text-align: right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="modalClientesTbody">
                        <?php foreach ($clientes as $c): ?>
                            <tr class="modal-cliente-fila" data-search="<?= strtolower(e($c['nombre'] . ' ' . $c['dui'] . ' ' . ($c['telefono'] ?? ''))) ?>">
                                <td style="padding: 10px 12px;">
                                    <strong><?= e($c['nombre']) ?></strong>
                                    <?php if (!empty($c['correo_electronico'])): ?>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= e($c['correo_electronico']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 12px;"><span class="badge badge-outline"><?= e($c['dui']) ?></span></td>
                                <td style="padding: 10px 12px;"><?= e($c['telefono'] ?? '—') ?></td>
                                <td style="padding: 10px 12px; text-align: right;">
                                    <button type="button" class="btn btn-sm btn-primary btn-asignar-cliente"
                                            data-id="<?= $c['id_cliente'] ?>"
                                            data-nombre="<?= e($c['nombre']) ?>">
                                        <i class="fas fa-check"></i> Asignar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="modalSinResultados" style="display: none; padding: 2rem; text-align: center; color: var(--gray-500, #6b7280);">
                    <i class="fas fa-search" style="font-size: 1.75rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p style="margin: 0;">No se encontraron clientes coincidentes.</p>
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
    function initInmuebleForm() {
        const modal = document.getElementById('modalBuscarCliente');
        const inputBuscar = document.getElementById('modalBuscarClienteInput');
        const selectCliente = document.getElementById('id_cliente');
        const form = document.getElementById('inmuebleForm');

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
            const filas = document.querySelectorAll('.modal-cliente-fila');
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

        document.getElementById('btnAbrirModalCliente')?.addEventListener('click', abrirModal);
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

        // Asignar cliente desde el modal
        document.querySelectorAll('.btn-asignar-cliente').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const nombre = btn.dataset.nombre;
                if (selectCliente) {
                    selectCliente.value = id;
                    selectCliente.dispatchEvent(new Event('change'));
                }
                cerrarModal();
                if (typeof Toast !== 'undefined' && Toast.success) {
                    Toast.success(`Cliente "${nombre}" seleccionado.`);
                }
            });
        });

        // Validación de área mínima de 50 m² al enviar formulario
        form?.addEventListener('submit', (e) => {
            const areaInput = document.getElementById('area');
            if (areaInput) {
                const val = parseFloat(areaInput.value);
                if (isNaN(val) || val < 50) {
                    e.preventDefault();
                    if (typeof Toast !== 'undefined' && Toast.error) {
                        Toast.error('El área del inmueble debe ser de al menos 50.00 m².');
                    } else {
                        alert('El área del inmueble debe ser de al menos 50.00 m².');
                    }
                    areaInput.focus();
                    return;
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initInmuebleForm);
    } else {
        initInmuebleForm();
    }
})();
</script>
