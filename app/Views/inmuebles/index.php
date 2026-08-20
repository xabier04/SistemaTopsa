<?php /** Vista: Inmuebles */ ?>

<div class="page-header">
    <h2><i class="fas fa-building"></i> Inmuebles</h2>
    <a href="<?= url('inmueble/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Inmueble
    </a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon purple"><i class="fas fa-building"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($inmuebles ?? []) ?></span>
            <span class="chip-label">Total Inmuebles</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Matrícula</th>
                    <th>Cliente</th>
                    <th>Dirección</th>
                    <th>Área (m²)</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inmuebles)): ?>
                    <?php foreach ($inmuebles as $i => $inm): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><span class="cell-icon-text"><i class="fas fa-hashtag"></i> <?= e($inm['matricula']) ?></span></td>
                            <td><?= e($inm['nombre_cliente'] ?? '—') ?></td>
                            <td><span class="cell-icon-text"><i class="fas fa-map-marker-alt"></i> <?= truncate($inm['direccion'] ?? '', 40) ?></span></td>
                            <td><?= $inm['area'] ? number_format($inm['area'], 2) : '—' ?></td>
                            <td><span class="badge-dot dot-neutral"><?= e($inm['tipo_inmueble']) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("inmueble/edit/{$inm['id_inmueble']}") ?>" class="btn-action act-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                    <button class="btn-action act-delete btn-delete" data-id="<?= $inm['id_inmueble'] ?>" data-name="<?= e($inm['matricula']) ?>" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-building"></i>
                                <h3>Sin inmuebles</h3>
                                <p>Registre su primer inmueble para comenzar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
