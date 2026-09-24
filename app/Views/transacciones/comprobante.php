<style>@media print { .sidebar,.topbar,.sidebar-overlay,.alert,.no-print { display:none!important } .main-content { margin:0!important;width:100%!important } .card { box-shadow:none!important } }</style>
<div class="page-header no-print"><a class="btn btn-outline" href="<?= url('transaccion/index') ?>">Volver a pagos</a><a class="btn btn-primary" href="<?= url('transaccion/create') ?>?nuevo=1">Nuevo pago</a><button class="btn btn-primary" onclick="window.print()">Imprimir / guardar PDF</button></div>
<article class="card"><div class="card-header"><h2>TOPSA · Comprobante de pago #<?= str_pad((string) $pago['id_transaccion'], 6, '0', STR_PAD_LEFT) ?></h2></div>
<div class="card-body">
    <p><strong>Cliente:</strong> <?= e($pago['nombre_cliente']) ?> · DUI: <?= e($pago['dui']) ?></p>
    <p><strong>Proyecto:</strong> <?= e($pago['nombre_del_proyecto']) ?></p>
    <p><strong>Fecha del pago:</strong> <?= formatDate($pago['fecha_de_pago']) ?></p>
    <p><strong>Concepto:</strong> <?= e($pago['tipo_de_transaccion']) ?></p>
    <h2>Recibido: <?= formatMoney($pago['monto_abonado']) ?></h2>
    <p>Saldo pendiente al registrar este pago: <strong><?= formatMoney($pago['saldo_pendiente']) ?></strong></p>
    <p>Gracias por su pago.</p>
</div></article>
