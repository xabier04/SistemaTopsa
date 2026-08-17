<?php /** Vista: Backups */ ?>

<div class="page-header">
    <h2><i class="fas fa-database"></i> Copias de Seguridad</h2>
    <a href="<?= url('backup/generar') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Generar Backup
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-hdd" style="color: var(--primary-500); margin-right: 8px;"></i>Backups Disponibles</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Archivo</th>
                    <th>Tamaño</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($backups)): ?>
                    <?php foreach ($backups as $i => $bk): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600">
                                <i class="fas fa-file-archive" style="margin-right: 6px; color: var(--accent-500);"></i>
                                <?= e($bk['nombre']) ?>
                            </td>
                            <td><?= number_format($bk['tamano'] / 1024, 1) ?> KB</td>
                            <td><?= $bk['fecha'] ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("backup/descargar/{$bk['nombre']}") ?>" class="btn btn-sm btn-success" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-delete-backup" data-name="<?= e($bk['nombre']) ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-database"></i>
                                <h3>Sin backups</h3>
                                <p>Genere su primera copia de seguridad</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
