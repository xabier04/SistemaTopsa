<?php /** Vista: Transacciones */ ?>
<?php
    $totalAbonado = 0;
    if (!empty($transacciones)) {
        foreach ($transacciones as $t) {
            $totalAbonado += ($t['monto_abonado'] ?? 0);
        }
    }
?>

<div class="page-header">
    <h2><i class="fas fa-money-bill-wave"></i> Transacciones</h2>
    <a href="<?= url('transaccion/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Transacción
    </a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-receipt"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($transacciones ?? []) ?></span>
            <span class="chip-label">Total Transacciones</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon amber"><i class="fas fa-dollar-sign"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= formatMoney($totalAbonado) ?></span>
            <span class="chip-label">Monto Total Abonado</span>
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
                            <td><span class="cell-icon-text"><i class="fas fa-calendar-alt"></i> <?= formatDate($t['fecha_de_pago']) ?></span></td>
                            <td><span class="cell-money positive"><?= formatMoney($t['monto_abonado']) ?></span></td>
                            <td><span class="badge-dot dot-neutral"><?= e($t['tipo_de_transaccion']) ?></span></td>
                            <td>
                                <span class="cell-money <?= $t['saldo_pendiente'] > 0 ? 'negative' : 'positive' ?>">
                                    <?= formatMoney($t['saldo_pendiente']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-receipt"></i>
                                <h3>Sin transacciones</h3>
                                <p>Registre su primera transacción para comenzar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
