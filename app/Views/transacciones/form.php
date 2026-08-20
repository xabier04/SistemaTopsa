<?php /** Vista: Formulario de Transacción */ ?>

<div class="page-header">
    <h2><i class="fas fa-money-bill-wave"></i> Nueva Transacción</h2>
    <a href="<?= url('transaccion/index') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="form-card-clean">
    <div class="form-card-header">
        <h3><i class="fas fa-receipt"></i> Registrar Transacción</h3>
    </div>

    <form action="<?= $action ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="id_proyecto">
                    <i class="fas fa-project-diagram"></i> Proyecto
                </label>
                <select id="id_proyecto" name="id_proyecto" class="form-control" required>
                    <option value="">Seleccione un proyecto</option>
                    <?php foreach ($proyectos as $p): ?>
                        <option value="<?= $p['id_proyecto'] ?>">
                            <?= e($p['nombre_del_proyecto']) ?> — <?= e($p['nombre_cliente'] ?? '') ?> (Saldo: <?= formatMoney($p['saldo_pendiente'] ?? 0) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_de_pago">
                        <i class="fas fa-calendar"></i> Fecha de Pago
                    </label>
                    <input type="date" id="fecha_de_pago" name="fecha_de_pago" class="form-control" 
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label for="monto_abonado">
                        <i class="fas fa-dollar-sign"></i> Monto Abonado
                    </label>
                    <div class="input-with-addon">
                        <span class="input-addon">$</span>
                        <input type="number" id="monto_abonado" name="monto_abonado" class="form-control" 
                               required step="0.01" min="0.01" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="tipo_de_transaccion">
                    <i class="fas fa-tag"></i> Tipo de Transacción
                </label>
                <select id="tipo_de_transaccion" name="tipo_de_transaccion" class="form-control" required>
                    <option value="">Seleccione</option>
                    <option value="Abono">Abono</option>
                    <option value="Pago Completo">Pago Completo</option>
                    <option value="Anticipo">Anticipo</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
        </div>

        <div class="form-actions-toolbar">
            <a href="<?= url('transaccion/index') ?>" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Registrar Pago</button>
        </div>
    </form>
</div>
