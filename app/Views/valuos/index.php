<?php /** Vista: Valuaciones */ ?>

<div class="page-header">
    <h2><i class="fas fa-clipboard-check"></i> Valuaciones (Avalúos)</h2>
    <a href="<?= url('valuo/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Valuación</a>
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
                            <td><?= formatDate($v['fecha_del_valuo']) ?></td>
                            <td><?= formatMoney($v['monto_estimado']) ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: var(--space-2); min-width: 120px;">
                                    <div class="progress-bar" style="flex: 1;">
                                        <div class="progress-fill green" style="width: <?= $v['porcentaje_de_avance'] ?>%"></div>
                                    </div>
                                    <span class="text-xs fw-600"><?= formatPercent($v['porcentaje_de_avance']) ?></span>
                                </div>
                            </td>
                            <td><?= truncate($v['observaciones'] ?? '', 30) ?></td>
                            <td>
                                <a href="<?= url("valuo/edit/{$v['id_valuo']}") ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-clipboard-list"></i><h3>Sin valuaciones</h3></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
