<?php /** Vista: Gestión Documental */ ?>

<div class="page-header">
    <h2><i class="fas fa-folder-open"></i> Gestión Documental</h2>
</div>

<!-- Upload Form -->
<div class="card mb-6">
    <div class="card-header">
        <h3><i class="fas fa-cloud-upload-alt" style="color: var(--primary-500); margin-right: 8px;"></i>Subir Documento</h3>
    </div>
    <div class="card-body">
        <form action="<?= url('documento/upload') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="id_proyecto"><i class="fas fa-project-diagram"></i> Proyecto</label>
                    <select id="id_proyecto" name="id_proyecto" class="form-control" required>
                        <option value="">Seleccione un proyecto</option>
                        <?php foreach ($proyectos as $p): ?>
                            <option value="<?= $p['id_proyecto'] ?>"><?= e($p['nombre_del_proyecto']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tipo_de_documento"><i class="fas fa-tag"></i> Tipo de Documento</label>
                    <select id="tipo_de_documento" name="tipo_de_documento" class="form-control" required>
                        <option value="">Seleccione</option>
                        <option value="Plano">Plano</option>
                        <option value="Fotografía">Fotografía</option>
                        <option value="Evidencia">Evidencia</option>
                        <option value="Contrato">Contrato</option>
                        <option value="Presupuesto">Presupuesto</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="archivo"><i class="fas fa-file"></i> Archivo</label>
                <input type="file" id="archivo" name="archivo" class="form-control" required
                       accept=".pdf,.jpg,.jpeg,.png,.dwg,.dxf,.doc,.docx,.xls,.xlsx">
                <span class="text-xs text-muted" style="margin-top: 4px; display: block;">
                    Formatos: PDF, JPG, PNG, DWG, DXF, DOC, DOCX, XLS, XLSX — Máx: 10 MB
                </span>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Subir Documento</button>
        </form>
    </div>
</div>

<!-- Document List -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list" style="color: var(--accent-500); margin-right: 8px;"></i>Documentos Registrados</h3>
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
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><i class="fas fa-file-alt" style="margin-right: 6px; color: var(--primary-400);"></i><?= e($doc['nombre_del_archivo']) ?></td>
                            <td><?= e($doc['nombre_del_proyecto'] ?? '—') ?></td>
                            <td><span class="badge badge-secondary"><?= e($doc['tipo_de_documento']) ?></span></td>
                            <td><?= formatDate($doc['fecha_de_subida']) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("documento/download/{$doc['id_documento']}") ?>" class="btn btn-sm btn-outline" title="Descargar"><i class="fas fa-download"></i></a>
                                    <button class="btn btn-sm btn-danger btn-delete-doc" data-id="<?= $doc['id_documento'] ?>" data-name="<?= e($doc['nombre_del_archivo']) ?>" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6"><div class="empty-state"><i class="fas fa-file-upload"></i><h3>Sin documentos</h3></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
