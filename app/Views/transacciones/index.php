<?php /** Vista: Transacciones */ ?>

<div class="page-header">
    <h2><i class="fas fa-money-bill-wave"></i> Transacciones</h2>
    <a href="<?= url('transaccion/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Transacción
    </a>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Proyecto</th>
                    <th>Cliente</th>
                    <th>Fecha de Pago</th>
                    <th>Monto Abonado</th>
                    <th>Tipo</th>
                    <th>Saldo Pendiente</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transacciones)): ?>
                    <?php foreach ($transacciones as $i => $t): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><?= e($t['nombre_del_proyecto'] ?? '—') ?></td>
                            <td><?= e($t['nombre_cliente'] ?? '—') ?></td>
                            <td><?= formatDate($t['fecha_de_pago']) ?></td>
                            <td style="color: var(--success-color); font-weight: 600;"><?= formatMoney($t['monto_abonado']) ?></td>
                            <td><span class="badge badge-secondary"><?= e($t['tipo_de_transaccion']) ?></span></td>
                            <td style="color: <?= $t['saldo_pendiente'] > 0 ? 'var(--error-color)' : 'var(--success-color)' ?>; font-weight: 600;">
                                <?= formatMoney($t['saldo_pendiente']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7"><div class="empty-state"><i class="fas fa-receipt"></i><h3>Sin transacciones</h3></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
