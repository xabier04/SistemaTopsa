<?php /** Vista: Backups */ ?>

<div class="page-header">
    <h2><i class="fas fa-database"></i> Copias de Seguridad</h2>
    <a href="<?= url('backup/generar') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Generar Backup
    </a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-database"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($backups ?? []) ?></span>
            <span class="chip-label">Copias de Respaldo</span>
        </div>
    </div>
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
                    <th>Archivo de Respaldo</th>
                    <th>Tamaño</th>
                    <th>Fecha de Generación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($backups)): ?>
                    <?php foreach ($backups as $i => $bk): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600">
                                <div class="cell-with-avatar">
                                    <span class="file-icon file-doc"><i class="fas fa-file-archive"></i></span>
                                    <div class="cell-name"><?= e($bk['nombre']) ?></div>
                                </div>
                            </td>
                            <td><span class="cell-icon-text"><i class="fas fa-weight-hanging"></i> <?= number_format($bk['tamano'] / 1024, 1) ?> KB</span></td>
                            <td><span class="cell-icon-text"><i class="fas fa-calendar-alt"></i> <?= $bk['fecha'] ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("backup/descargar/{$bk['nombre']}") ?>" class="btn-action act-download" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn-action act-delete btn-delete-backup" data-name="<?= e($bk['nombre']) ?>" title="Eliminar">
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
                                <p>Genere su primera copia de seguridad para respaldar la información</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
