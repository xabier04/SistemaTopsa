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
                    <th>Saldo al registrar</th><th>Comprobante</th>
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
                            <td><a class="btn btn-sm btn-outline" href="<?= url('transaccion/comprobante/' . $t['id_transaccion']) ?>">Ver / imprimir</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
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

<div class="card mt-6"><div class="card-header"><h3>Saldos actuales por cliente y proyecto</h3></div>
<div class="card-body"><label for="buscarSaldo">Buscar cliente o proyecto</label><input id="buscarSaldo" class="form-control" type="search" placeholder="Escriba un nombre"></div>
<div class="table-container"><table class="table"><thead><tr><th>Cliente</th><th>Proyecto</th><th>Presupuesto</th><th>Abonado</th><th>Saldo pendiente</th></tr></thead><tbody id="saldosBody">
<?php foreach ($saldos as $s): ?><tr><td><?= e($s['nombre_cliente']) ?></td><td><?= e($s['nombre_del_proyecto']) ?></td><td><?= formatMoney($s['presupuesto_inicial']) ?></td><td><?= formatMoney($s['total_abonado']) ?></td><td><?= formatMoney($s['saldo_pendiente']) ?></td></tr><?php endforeach; ?>
<?php if (!$saldos): ?><tr><td colspan="5">No hay proyectos registrados.</td></tr><?php endif; ?>
</tbody></table></div></div>
<script>document.getElementById('buscarSaldo').addEventListener('input', function () { document.querySelectorAll('#saldosBody tr').forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase().includes(this.value.toLocaleLowerCase()); }); });</script>
