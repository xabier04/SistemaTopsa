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
                <div class="form-group">
                    <label for="id_proyecto">
                        <i class="fas fa-project-diagram"></i> Proyecto
                    </label>
                    <select id="id_proyecto" name="id_proyecto" class="form-control" required>
                        <option value="">Seleccione un proyecto</option>
                        <?php foreach ($proyectos as $p): ?>
                            <option value="<?= $p['id_proyecto'] ?>"><?= e($p['nombre_del_proyecto']) ?></option>
                        <?php endforeach; ?>
                    </select>
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
