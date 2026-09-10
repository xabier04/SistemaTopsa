<?php /** Vista: Gestión Documental */ ?>
<?php
    function getFileIconClass($filename, $tipo = '') {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === 'pdf') return 'file-pdf';
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) return 'file-image';
        if (in_array($ext, ['dwg', 'dxf'])) return 'file-plan';
        if (in_array($ext, ['doc', 'docx'])) return 'file-doc';
        if (in_array($ext, ['xls', 'xlsx'])) return 'file-excel';
        if ($tipo === 'Plano') return 'file-plan';
        if ($tipo === 'Fotografía') return 'file-image';
        return 'file-other';
    }
    function getFileIconFA($class) {
        return match($class) {
            'file-pdf' => 'fas fa-file-pdf',
            'file-image' => 'fas fa-file-image',
            'file-plan' => 'fas fa-drafting-compass',
            'file-doc' => 'fas fa-file-word',
            'file-excel' => 'fas fa-file-excel',
            default => 'fas fa-file-alt',
        };
    }
?>

<div class="page-header">
    <h2><i class="fas fa-folder-open"></i> Gestión Documental</h2>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon blue"><i class="fas fa-file-alt"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($documentos ?? []) ?></span>
            <span class="chip-label">Total Documentos</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-drafting-compass"></i></div>
        <div class="chip-data">
            <?php 
                $planos = 0; 
                foreach ($documentos ?? [] as $d) if (($d['tipo_de_documento'] ?? '') === 'Plano') $planos++;
            ?>
            <span class="chip-value"><?= $planos ?></span>
            <span class="chip-label">Planos</span>
        </div>
    </div>
</div>

<!-- Upload Form Card -->
<div class="card mb-6">
    <div class="card-header">
        <h3><i class="fas fa-cloud-upload-alt" style="color: var(--primary-500); margin-right: 8px;"></i>Subir Documento</h3>
    </div>
    <div class="card-body">
        <form action="<?= url('documento/upload') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="id_proyecto">
                        <i class="fas fa-project-diagram"></i> Proyecto <span class="required-mark">*</span>
                    </label>
                    <div style="display: flex; gap: 8px; align-items: stretch;">
                        <select id="id_proyecto" name="id_proyecto" class="form-control" required style="flex: 1;">
                            <option value="">Seleccione un proyecto</option>
                            <?php foreach ($proyectos as $p): ?>
                                <option value="<?= $p['id_proyecto'] ?>">
                                    <?= e($p['nombre_del_proyecto']) ?><?= !empty($p['nombre_cliente']) ? ' — ' . e($p['nombre_cliente']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-primary" id="btnAbrirModalProyecto" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-search"></i> Buscar Proyecto
                        </button>
                    </div>
                    <small class="field-hint"><i class="fas fa-info-circle"></i> Seleccione un proyecto o use la búsqueda flotante</small>
                </div>

                <div class="form-group">
                    <label for="tipo_de_documento">
                        <i class="fas fa-tag"></i> Tipo de Documento
                    </label>
                    <select id="tipo_de_documento" name="tipo_de_documento" class="form-control" required>
                        <option value="">Seleccione</option>
                        <option value="Plano">Plano</option>
                        <option value="Fotografía">Fotografía</option>
                        <option value="Contrato">Contrato</option>
                        <option value="Presupuesto">Presupuesto</option>
                        <option value="Evidencia">Evidencia</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="archivo">
                    <i class="fas fa-file"></i> Archivo
                </label>
                <input type="file" id="archivo" name="archivo" class="form-control" required
                       accept=".pdf,.jpg,.jpeg,.png,.dwg,.dxf,.doc,.docx,.xls,.xlsx">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Subir Documento
            </button>
        </form>
    </div>
</div>

<!-- Document List Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list" style="color: var(--primary-500); margin-right: 8px;"></i>Documentos Registrados</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Archivo</th>
                    <th>Proyecto</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($documentos)): ?>
                    <?php foreach ($documentos as $i => $doc): ?>
                        <?php
                            $iconClass = getFileIconClass($doc['nombre_del_archivo'], $doc['tipo_de_documento']);
                            $iconFA = getFileIconFA($iconClass);
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="cell-with-avatar">
                                    <span class="file-icon <?= $iconClass ?>"><i class="<?= $iconFA ?>"></i></span>
                                    <div class="cell-name"><?= e($doc['nombre_del_archivo']) ?></div>
                                </div>
                            </td>
                            <td><?= e($doc['nombre_del_proyecto'] ?? '—') ?></td>
                            <td><span class="badge-dot dot-neutral"><?= e($doc['tipo_de_documento']) ?></span></td>
                            <td><span class="cell-icon-text"><i class="fas fa-calendar-alt"></i> <?= formatDate($doc['fecha_de_subida']) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("documento/download/{$doc['id_documento']}") ?>" class="btn-action act-download" title="Descargar"><i class="fas fa-download"></i></a>
                                    <button class="btn-action act-delete btn-delete-doc" data-id="<?= $doc['id_documento'] ?>" data-name="<?= e($doc['nombre_del_archivo']) ?>" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-file-upload"></i>
                                <h3>Sin documentos</h3>
                                <p>Suba su primer documento para comenzar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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
    function initDocumentoProyectoModal() {
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
        document.addEventListener('DOMContentLoaded', initDocumentoProyectoModal);
    } else {
        initDocumentoProyectoModal();
    }
})();
</script>
