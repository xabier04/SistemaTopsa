<?php /** Vista: Valuaciones */ ?>

<div class="page-header">
    <h2><i class="fas fa-clipboard-check"></i> Valuaciones (Avalúos)</h2>
    <a href="<?= url('valuo/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Valuación</a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-clipboard-list"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($valuos ?? []) ?></span>
            <span class="chip-label">Total Valuaciones</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Proyecto</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Monto Estimado</th>
                    <th>Avance</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($valuos)): ?>
                    <?php foreach ($valuos as $i => $v): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><?= e($v['nombre_del_proyecto'] ?? '—') ?></td>
                            <td><?= e($v['nombre_cliente'] ?? '—') ?></td>
                            <td><span class="cell-icon-text"><i class="fas fa-calendar-alt"></i> <?= formatDate($v['fecha_del_valuo']) ?></span></td>
                            <td><span class="cell-money"><?= formatMoney($v['monto_estimado']) ?></span></td>
                            <td>
                                <div class="progress-inline">
                                    <div class="progress-bar">
                                        <div class="progress-fill green" style="width: <?= $v['porcentaje_de_avance'] ?>%"></div>
                                    </div>
                                    <span class="progress-label"><?= formatPercent($v['porcentaje_de_avance']) ?></span>
                                </div>
                            </td>
                            <td><?= truncate($v['observaciones'] ?? '', 30) ?></td>
                            <td>
                                <a href="<?= url("valuo/edit/{$v['id_valuo']}") ?>" class="btn-action act-edit" title="Editar"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <h3>Sin valuaciones</h3>
                                <p>Cree su primera valuación para comenzar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
