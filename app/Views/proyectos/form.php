<?php /** Vista: Formulario de Proyecto */ ?>

<div class="page-header">
    <h2>
        <i class="fas fa-drafting-compass"></i>
        <?= $proyecto ? 'Editar Proyecto' : 'Nuevo Proyecto' ?>
    </h2>
    <a href="<?= url('proyecto/index') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="form-card-clean">
    <div class="form-card-header">
        <h3>
            <i class="fas fa-project-diagram"></i>
            <?= $proyecto ? 'Actualizar Proyecto' : 'Datos del Proyecto' ?>
        </h3>
    </div>

    <form action="<?= $action ?>" method="POST" id="proyectoForm">
        <?= csrf_field() ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="nombre_del_proyecto">
                    <i class="fas fa-file-signature"></i> Nombre del Proyecto <span class="required-mark">*</span>
                </label>
                <input type="text" id="nombre_del_proyecto" name="nombre_del_proyecto" class="form-control" 
                       value="<?= e($proyecto['nombre_del_proyecto'] ?? '') ?>" required maxlength="60"
                       placeholder="Nombre descriptivo del proyecto"
                       data-no-numbers="true" pattern="^[^0-9]+$"
                       title="El nombre del proyecto no debe contener números" autocomplete="off"
                       oninput="this.value = this.value.replace(/[0-9]/g, '')"
                       onkeydown="if(event.key >= '0' && event.key <= '9' && !event.ctrlKey && !event.metaKey) event.preventDefault();">
                <small class="field-hint" id="proyectoNombreHint"><i class="fas fa-info-circle"></i> Solo letras y espacios (no se permiten números)</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="id_cliente">
                        <i class="fas fa-user-tie"></i> Cliente <span class="required-mark">*</span>
                    </label>
                    <div style="display: flex; gap: 8px; align-items: stretch;">
                        <select id="id_cliente" name="id_cliente" class="form-control" required style="flex: 1;">
                            <option value="">Seleccione un cliente</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id_cliente'] ?>" 
                                    <?= ($proyecto['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>>
                                    <?= e($c['nombre']) ?><?= !empty($c['dui']) ? ' (DUI: ' . e($c['dui']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-primary" id="btnAbrirModalCliente" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-search"></i> Buscar Cliente
                        </button>
                    </div>
                    <small class="field-hint"><i class="fas fa-info-circle"></i> Seleccione un cliente o use "Buscar Cliente"</small>
                </div>
                <div class="form-group">
                    <label for="id_inmueble">
                        <i class="fas fa-building"></i> Inmueble <span class="text-muted">(opcional)</span>
                    </label>
                    <div style="display: flex; gap: 8px; align-items: stretch;">
                        <select id="id_inmueble" name="id_inmueble" class="form-control" style="flex: 1;">
                            <option value="">Sin inmueble</option>
                            <?php foreach ($inmuebles as $inm): ?>
                                <option value="<?= $inm['id_inmueble'] ?>"
                                    data-cliente-id="<?= $inm['id_cliente'] ?>"
                                    <?= ($proyecto['id_inmueble'] ?? '') == $inm['id_inmueble'] ? 'selected' : '' ?>>
                                    Matrícula: <?= e($inm['matricula']) ?> (<?= e($inm['tipo_inmueble'] ?? 'Inmueble') ?> — <?= e($inm['direccion']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-primary" id="btnAbrirModalInmueble" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-search"></i> Buscar Inmueble
                        </button>
                    </div>
                    <small class="field-hint" id="inmuebleHint"><i class="fas fa-info-circle"></i> Inmuebles del cliente (o use "Buscar Inmueble")</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_de_inicio">
                        <i class="fas fa-calendar-alt"></i> Fecha de Inicio <span class="required-mark">*</span>
                    </label>
                    <div class="input-with-addon">
                        <span class="input-addon datepicker-trigger" style="cursor: pointer;" title="Abrir calendario"><i class="fas fa-calendar-day"></i></span>
                        <input type="text" id="fecha_de_inicio" name="fecha_de_inicio" class="form-control datepicker" 
                               value="<?= e($proyecto['fecha_de_inicio'] ?? date('Y-m-d')) ?>" required
                               placeholder="AAAA-MM-DD" autocomplete="off">
                    </div>
                    <small class="field-hint"><i class="fas fa-calendar-check"></i> Calendario interactivo (selección directa)</small>
                </div>
                <div class="form-group">
                    <label for="presupuesto_inicial">
                        <i class="fas fa-dollar-sign"></i> Presupuesto Inicial <span class="required-mark">*</span>
                    </label>
                    <div class="input-with-addon">
                        <span class="input-addon">$</span>
                        <input type="number" id="presupuesto_inicial" name="presupuesto_inicial" class="form-control" 
                               value="<?= e($proyecto['presupuesto_inicial'] ?? '0.00') ?>" required step="0.01" min="0"
                               placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="estado_del_proyecto">
                    <i class="fas fa-flag"></i> Estado <span class="required-mark">*</span>
                </label>
                <select id="estado_del_proyecto" name="estado_del_proyecto" class="form-control" required>
                    <option value="En Proceso" <?= ($proyecto['estado_del_proyecto'] ?? 'En Proceso') === 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                    <option value="Observado" <?= ($proyecto['estado_del_proyecto'] ?? '') === 'Observado' ? 'selected' : '' ?>>Observado</option>
                    <option value="Aprobado" <?= ($proyecto['estado_del_proyecto'] ?? '') === 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                    <option value="Finalizado" <?= ($proyecto['estado_del_proyecto'] ?? '') === 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
                    <option value="Incompleto" <?= ($proyecto['estado_del_proyecto'] ?? '') === 'Incompleto' ? 'selected' : '' ?>>Incompleto</option>
                </select>
            </div>
        </div>

        <div class="form-actions-toolbar">
            <a href="<?= url('proyecto/index') ?>" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= $proyecto ? 'Actualizar' : 'Guardar' ?>
            </button>
        </div>
    </form>
</div>

<!-- ─── Ventana Flotante (Modal) para Buscar y Asignar Cliente ─── -->
<div id="modalBuscarCliente" class="selector-modal-backdrop">
    <div class="selector-modal-card">
        <div class="selector-modal-header">
            <h3><i class="fas fa-users" style="color: var(--primary-600);"></i> Buscar y Asignar Cliente</h3>
            <button type="button" class="selector-modal-close-x" id="btnCerrarModalClienteX">&times;</button>
        </div>
        <div class="selector-modal-body">
            <div style="margin-bottom: 1rem;">
                <div class="input-with-addon" style="width: 100%;">
                    <span class="input-addon"><i class="fas fa-search"></i></span>
                    <input type="text" id="modalBuscarClienteInput" class="form-control" 
                           placeholder="Buscar por nombre, DUI o teléfono..." autocomplete="off">
                </div>
            </div>
            <div class="selector-modal-table-container">
                <table class="table" style="width: 100%; margin-bottom: 0; font-size: 0.875rem;">
                    <thead>
                        <tr style="background: var(--gray-50); position: sticky; top: 0; z-index: 1;">
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
                <div id="modalClientesSinResultados" style="display: none; padding: 2rem; text-align: center; color: var(--gray-500);">
                    <i class="fas fa-search" style="font-size: 1.75rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p style="margin: 0;">No se encontraron clientes coincidentes.</p>
                </div>
            </div>
        </div>
        <div class="selector-modal-footer">
            <button type="button" class="btn btn-outline" id="btnCerrarModalClienteBoton">Cerrar</button>
        </div>
    </div>
</div>

<!-- ─── Ventana Flotante (Modal) para Buscar y Asignar Inmueble ─── -->
<div id="modalBuscarInmueble" class="selector-modal-backdrop">
    <div class="selector-modal-card">
        <div class="selector-modal-header">
            <h3><i class="fas fa-building" style="color: var(--primary-600);"></i> Buscar y Asignar Inmueble</h3>
            <button type="button" class="selector-modal-close-x" id="btnCerrarModalInmuebleX">&times;</button>
        </div>
        <div class="selector-modal-body">
            <div style="margin-bottom: 1rem;">
                <div class="input-with-addon" style="width: 100%;">
                    <span class="input-addon"><i class="fas fa-search"></i></span>
                    <input type="text" id="modalBuscarInmuebleInput" class="form-control" 
                           placeholder="Buscar por matrícula, dirección o propietario..." autocomplete="off">
                </div>
            </div>
            <div class="selector-modal-table-container">
                <table class="table" style="width: 100%; margin-bottom: 0; font-size: 0.875rem;">
                    <thead>
                        <tr style="background: var(--gray-50); position: sticky; top: 0; z-index: 1;">
                            <th style="padding: 10px 12px;">Matrícula</th>
                            <th style="padding: 10px 12px;">Tipo / Área</th>
                            <th style="padding: 10px 12px;">Dirección</th>
                            <th style="padding: 10px 12px;">Propietario</th>
                            <th style="padding: 10px 12px; text-align: right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="modalInmueblesTbody">
                        <?php foreach ($inmuebles as $inm): ?>
                            <tr class="modal-inmueble-fila" 
                                data-id="<?= $inm['id_inmueble'] ?>"
                                data-cliente-id="<?= $inm['id_cliente'] ?>"
                                data-search="<?= strtolower(e($inm['matricula'] . ' ' . ($inm['tipo_inmueble'] ?? '') . ' ' . $inm['direccion'] . ' ' . ($inm['nombre_cliente'] ?? ''))) ?>">
                                <td style="padding: 10px 12px;">
                                    <strong><i class="fas fa-hashtag text-muted"></i> <?= e($inm['matricula']) ?></strong>
                                </td>
                                <td style="padding: 10px 12px;">
                                    <span class="badge badge-info"><?= e($inm['tipo_inmueble'] ?? 'Inmueble') ?></span>
                                    <?php if (!empty($inm['area'])): ?>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= e($inm['area']) ?> m²</div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 12px; max-width: 200px;"><?= e($inm['direccion']) ?></td>
                                <td style="padding: 10px 12px;">
                                    <strong><?= e($inm['nombre_cliente'] ?? 'Sin asignar') ?></strong>
                                </td>
                                <td style="padding: 10px 12px; text-align: right;">
                                    <button type="button" class="btn btn-sm btn-primary btn-asignar-inmueble"
                                            data-id="<?= $inm['id_inmueble'] ?>"
                                            data-cliente-id="<?= $inm['id_cliente'] ?>"
                                            data-matricula="<?= e($inm['matricula']) ?>"
                                            data-cliente-nombre="<?= e($inm['nombre_cliente'] ?? '') ?>">
                                        <i class="fas fa-check"></i> Asignar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="modalInmueblesSinResultados" style="display: none; padding: 2rem; text-align: center; color: var(--gray-500);">
                    <i class="fas fa-search" style="font-size: 1.75rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p style="margin: 0;">No se encontraron inmuebles coincidentes.</p>
                </div>
            </div>
        </div>
        <div class="selector-modal-footer">
            <button type="button" class="btn btn-outline" id="btnCerrarModalInmuebleBoton">Cerrar</button>
        </div>
    </div>
</div>

<script>
(function() {
    function initProyectosForm() {
        const clienteSelect = document.getElementById('id_cliente');
        const inmuebleSelect = document.getElementById('id_inmueble');
        if (!clienteSelect || !inmuebleSelect) return;

        // Modales y botones
        const modalCliente = document.getElementById('modalBuscarCliente');
        const inputBuscarCliente = document.getElementById('modalBuscarClienteInput');
        const modalInmueble = document.getElementById('modalBuscarInmueble');
        const inputBuscarInmueble = document.getElementById('modalBuscarInmuebleInput');

        // Guardar todas las opciones originales de inmueble (excepto 'Sin inmueble')
        const todasLasOpciones = [];
        Array.from(inmuebleSelect.options).forEach(opt => {
            if (opt.value !== '') {
                todasLasOpciones.push({
                    value: opt.value,
                    text: opt.textContent,
                    clienteId: opt.dataset.clienteId || '',
                    selected: opt.selected
                });
            }
        });

        function filtrarInmuebles(preservarActual) {
            const clienteId = clienteSelect.value;
            const inmuebleActual = inmuebleSelect.value;

            inmuebleSelect.innerHTML = '';

            const optVacia = document.createElement('option');
            optVacia.value = '';

            if (!clienteId) {
                optVacia.textContent = '— Primero seleccione un cliente —';
                inmuebleSelect.appendChild(optVacia);
                inmuebleSelect.disabled = true;
                return;
            }

            inmuebleSelect.disabled = false;
            optVacia.textContent = 'Sin inmueble';
            inmuebleSelect.appendChild(optVacia);

            const coincidentes = todasLasOpciones.filter(item => String(item.clienteId) === String(clienteId));

            if (coincidentes.length === 0) {
                const optNinguno = document.createElement('option');
                optNinguno.value = '';
                optNinguno.disabled = true;
                optNinguno.textContent = '(Este cliente no posee inmuebles registrados)';
                inmuebleSelect.appendChild(optNinguno);
            } else {
                coincidentes.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.value;
                    opt.textContent = item.text;
                    opt.dataset.clienteId = item.clienteId;
                    if (preservarActual && String(item.value) === String(inmuebleActual)) {
                        opt.selected = true;
                    }
                    inmuebleSelect.appendChild(opt);
                });
            }
        }

        clienteSelect.addEventListener('change', function() {
            filtrarInmuebles(false);
        });

        // Ejecutar inmediatamente al cargar la vista
        if (clienteSelect.value) {
            filtrarInmuebles(true);
        } else {
            filtrarInmuebles(false);
        }

        // ─── Control Modal Cliente ───
        function abrirModalCliente() {
            if (!modalCliente) return;
            modalCliente.classList.add('active');
            if (inputBuscarCliente) {
                inputBuscarCliente.value = '';
                filtrarClientes('');
                setTimeout(() => inputBuscarCliente.focus(), 60);
            }
        }

        function cerrarModalCliente() {
            if (modalCliente) modalCliente.classList.remove('active');
        }

        function filtrarClientes(termino) {
            const clean = termino.toLowerCase().trim();
            const filas = document.querySelectorAll('.modal-cliente-fila');
            let encontrados = 0;
            filas.forEach(f => {
                const coincide = f.dataset.search.includes(clean);
                f.style.display = coincide ? '' : 'none';
                if (coincide) encontrados++;
            });
            const sinRes = document.getElementById('modalClientesSinResultados');
            if (sinRes) sinRes.style.display = encontrados === 0 ? 'block' : 'none';
        }

        document.getElementById('btnAbrirModalCliente')?.addEventListener('click', abrirModalCliente);
        document.getElementById('btnCerrarModalClienteX')?.addEventListener('click', cerrarModalCliente);
        document.getElementById('btnCerrarModalClienteBoton')?.addEventListener('click', cerrarModalCliente);
        inputBuscarCliente?.addEventListener('input', (e) => filtrarClientes(e.target.value));

        modalCliente?.addEventListener('click', (e) => {
            if (e.target === modalCliente) cerrarModalCliente();
        });

        document.querySelectorAll('.btn-asignar-cliente').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const nombre = btn.dataset.nombre;
                clienteSelect.value = id;
                clienteSelect.dispatchEvent(new Event('change'));
                cerrarModalCliente();
                if (typeof Toast !== 'undefined' && Toast.success) {
                    Toast.success(`Cliente "${nombre}" seleccionado.`);
                }
            });
        });

        // ─── Control Modal Inmueble ───
        function abrirModalInmueble() {
            if (!modalInmueble) return;
            modalInmueble.classList.add('active');
            if (inputBuscarInmueble) {
                inputBuscarInmueble.value = '';
                filtrarInmueblesModal('');
                setTimeout(() => inputBuscarInmueble.focus(), 60);
            }
        }

        function cerrarModalInmueble() {
            if (modalInmueble) modalInmueble.classList.remove('active');
        }

        function filtrarInmueblesModal(termino) {
            const clean = termino.toLowerCase().trim();
            const clienteActual = clienteSelect.value;
            const filas = document.querySelectorAll('.modal-inmueble-fila');
            let encontrados = 0;

            filas.forEach(f => {
                const coincide = f.dataset.search.includes(clean);
                // Si hay un cliente seleccionado, resaltamos o filtramos preferentemente
                f.style.display = coincide ? '' : 'none';
                if (coincide) {
                    encontrados++;
                    if (clienteActual && f.dataset.clienteId === clienteActual) {
                        f.style.background = 'var(--primary-50, #f0fdf4)';
                    } else {
                        f.style.background = '';
                    }
                }
            });
            const sinRes = document.getElementById('modalInmueblesSinResultados');
            if (sinRes) sinRes.style.display = encontrados === 0 ? 'block' : 'none';
        }

        document.getElementById('btnAbrirModalInmueble')?.addEventListener('click', abrirModalInmueble);
        document.getElementById('btnCerrarModalInmuebleX')?.addEventListener('click', cerrarModalInmueble);
        document.getElementById('btnCerrarModalInmuebleBoton')?.addEventListener('click', cerrarModalInmueble);
        inputBuscarInmueble?.addEventListener('input', (e) => filtrarInmueblesModal(e.target.value));

        modalInmueble?.addEventListener('click', (e) => {
            if (e.target === modalInmueble) cerrarModalInmueble();
        });

        document.querySelectorAll('.btn-asignar-inmueble').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const clienteId = btn.dataset.clienteId;
                const matricula = btn.dataset.matricula;
                const clienteNombre = btn.dataset.clienteNombre;

                // Si el inmueble pertenece a un cliente y aún no estaba seleccionado, se autoselecciona
                if (clienteId && clienteSelect.value !== clienteId) {
                    clienteSelect.value = clienteId;
                    clienteSelect.dispatchEvent(new Event('change'));
                }

                inmuebleSelect.value = id;
                cerrarModalInmueble();
                if (typeof Toast !== 'undefined' && Toast.success) {
                    Toast.success(`Inmueble Matrícula "${matricula}" asignado${clienteNombre ? ` (${clienteNombre})` : ''}.`);
                }
            });
        });

        // Cerrar con escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (modalCliente?.classList.contains('active')) cerrarModalCliente();
                if (modalInmueble?.classList.contains('active')) cerrarModalInmueble();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProyectosForm);
    } else {
        initProyectosForm();
    }
})();
</script>
