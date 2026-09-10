<?php /** Vista: Formulario de Transacción */ ?>

<div class="page-header">
    <h2><i class="fas fa-money-bill-wave"></i> Nueva Transacción</h2>
    <a href="<?= url('transaccion/index') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="form-card-clean">
    <div class="form-card-header">
        <h3><i class="fas fa-receipt"></i> Registrar Transacción</h3>
    </div>

    <form action="<?= $action ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="id_proyecto">
                    <i class="fas fa-project-diagram"></i> Proyecto <span class="required-mark">*</span>
                </label>
                <div style="display: flex; gap: 8px; align-items: stretch;">
                    <select id="id_proyecto" name="id_proyecto" class="form-control" required style="flex: 1;">
                        <option value="">Seleccione un proyecto</option>
                        <?php foreach ($proyectos as $p): ?>
                            <option value="<?= $p['id_proyecto'] ?>">
                                <?= e($p['nombre_del_proyecto']) ?> — <?= e($p['nombre_cliente'] ?? '') ?> (Saldo: <?= formatMoney($p['saldo_pendiente'] ?? 0) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-primary" id="btnAbrirModalProyecto" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-search"></i> Buscar Proyecto
                    </button>
                </div>
                <small class="field-hint"><i class="fas fa-info-circle"></i> Seleccione un proyecto o use la búsqueda flotante</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_de_pago">
                        <i class="fas fa-calendar"></i> Fecha de Pago
                    </label>
                    <input type="date" id="fecha_de_pago" name="fecha_de_pago" class="form-control" 
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="monto_abonado">
                        <i class="fas fa-dollar-sign"></i> Monto Abonado
                    </label>
                    <div class="input-with-addon">
                        <span class="input-addon">$</span>
                        <input type="number" id="monto_abonado" name="monto_abonado" class="form-control" 
                               required step="0.01" min="0.01" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="tipo_de_transaccion">
                    <i class="fas fa-tag"></i> Tipo de Transacción
                </label>
                <select id="tipo_de_transaccion" name="tipo_de_transaccion" class="form-control" required>
                    <option value="">Seleccione</option>
                    <option value="Abono">Abono</option>
                    <option value="Pago Completo">Pago Completo</option>
                    <option value="Anticipo">Anticipo</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
        </div>

        <div class="form-actions-toolbar">
            <a href="<?= url('transaccion/index') ?>" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Registrar Pago</button>
        </div>
    </form>
</div>

<!-- ─── Ventana Flotante (Modal) para Buscar y Asignar Proyecto ─── -->
<div id="modalBuscarProyecto" class="selector-modal-backdrop">
    <div class="selector-modal-card">
        <div class="selector-modal-header">
            <h3><i class="fas fa-project-diagram" style="color: var(--primary-600);"></i> Buscar y Asignar Proyecto</h3>
            <button type="button" class="selector-modal-close-x" id="btnCerrarModalProyectoX">&times;</button>
        </div>
        <div class="selector-modal-body">
            <div style="margin-bottom: 1rem;">
                <div class="input-with-addon" style="width: 100%;">
                    <span class="input-addon"><i class="fas fa-search"></i></span>
                    <input type="text" id="modalBuscarProyectoInput" class="form-control" 
                           placeholder="Buscar por nombre de proyecto o cliente..." autocomplete="off">
                </div>
            </div>
            <div class="selector-modal-table-container">
                <table class="table" style="width: 100%; margin-bottom: 0; font-size: 0.875rem;">
                    <thead>
                        <tr style="background: var(--gray-50); position: sticky; top: 0; z-index: 1;">
                            <th style="padding: 10px 12px;">Proyecto</th>
                            <th style="padding: 10px 12px;">Cliente</th>
                            <th style="padding: 10px 12px;">Saldo Pendiente</th>
                            <th style="padding: 10px 12px; text-align: right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="modalProyectosTbody">
                        <?php foreach ($proyectos as $p): ?>
                            <tr class="modal-proyecto-fila" 
                                data-id="<?= $p['id_proyecto'] ?>"
                                data-search="<?= strtolower(e($p['nombre_del_proyecto'] . ' ' . ($p['nombre_cliente'] ?? ''))) ?>">
                                <td style="padding: 10px 12px;">
                                    <strong><?= e($p['nombre_del_proyecto']) ?></strong>
                                </td>
                                <td style="padding: 10px 12px;"><?= e($p['nombre_cliente'] ?? '—') ?></td>
                                <td style="padding: 10px 12px;">
                                    <strong style="color: var(--primary-700);"><?= formatMoney($p['saldo_pendiente'] ?? 0) ?></strong>
                                </td>
                                <td style="padding: 10px 12px; text-align: right;">
                                    <button type="button" class="btn btn-sm btn-primary btn-asignar-proyecto"
                                            data-id="<?= $p['id_proyecto'] ?>"
                                            data-nombre="<?= e($p['nombre_del_proyecto']) ?>">
                                        <i class="fas fa-check"></i> Asignar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="modalProyectosSinResultados" style="display: none; padding: 2rem; text-align: center; color: var(--gray-500);">
                    <i class="fas fa-search" style="font-size: 1.75rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p style="margin: 0;">No se encontraron proyectos coincidentes.</p>
                </div>
            </div>
        </div>
        <div class="selector-modal-footer">
            <button type="button" class="btn btn-outline" id="btnCerrarModalProyectoBoton">Cerrar</button>
        </div>
    </div>
</div>

<script>
(function() {
    function initTransaccionProyectoModal() {
        const modal = document.getElementById('modalBuscarProyecto');
        const inputBuscar = document.getElementById('modalBuscarProyectoInput');
        const selectProyecto = document.getElementById('id_proyecto');
        if (!modal || !selectProyecto) return;

        function abrirModal() {
            modal.classList.add('active');
            if (inputBuscar) {
                inputBuscar.value = '';
                filtrar('');
                setTimeout(() => inputBuscar.focus(), 60);
            }
        }

        function cerrarModal() {
            modal.classList.remove('active');
        }

        function filtrar(termino) {
            const clean = termino.toLowerCase().trim();
            const filas = document.querySelectorAll('.modal-proyecto-fila');
            let encontrados = 0;
            filas.forEach(f => {
                const coincide = f.dataset.search.includes(clean);
                f.style.display = coincide ? '' : 'none';
                if (coincide) encontrados++;
            });
            const sinRes = document.getElementById('modalProyectosSinResultados');
            if (sinRes) sinRes.style.display = encontrados === 0 ? 'block' : 'none';
        }

        document.getElementById('btnAbrirModalProyecto')?.addEventListener('click', abrirModal);
        document.getElementById('btnCerrarModalProyectoX')?.addEventListener('click', cerrarModal);
        document.getElementById('btnCerrarModalProyectoBoton')?.addEventListener('click', cerrarModal);
        inputBuscar?.addEventListener('input', (e) => filtrar(e.target.value));

        modal.addEventListener('click', (e) => {
            if (e.target === modal) cerrarModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                cerrarModal();
            }
        });

        document.querySelectorAll('.btn-asignar-proyecto').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const nombre = btn.dataset.nombre;
                selectProyecto.value = id;
                selectProyecto.dispatchEvent(new Event('change'));
                cerrarModal();
                if (typeof Toast !== 'undefined' && Toast.success) {
                    Toast.success(`Proyecto "${nombre}" asignado.`);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTransaccionProyectoModal);
    } else {
        initTransaccionProyectoModal();
    }
})();
</script>
