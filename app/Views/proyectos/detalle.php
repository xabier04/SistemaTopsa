<?php /** Vista: Detalle de Proyecto */ ?>

<div class="page-header">
    <h2><i class="fas fa-project-diagram"></i> <?= e($proyecto['nombre_del_proyecto']) ?></h2>
    <div class="d-flex gap-3">
        <a href="<?= url("proyecto/edit/{$proyecto['id_proyecto']}") ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="<?= url('proyecto/index') ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- ─── Info Cards ────────────────────────────────────────── -->
<div class="stats-grid" style="margin-bottom: var(--space-6);">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-user-tie"></i></div>
        <div class="stat-content">
            <span class="stat-label">Cliente</span>
            <span class="stat-value" style="font-size: var(--text-lg);"><?= e($proyecto['nombre_cliente']) ?></span>
            <span class="text-xs text-muted"><?= e($proyecto['telefono_cliente'] ?? '') ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-content">
            <span class="stat-label">Presupuesto</span>
            <span class="stat-value"><?= formatMoney($proyecto['presupuesto_inicial']) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="stat-content">
            <span class="stat-label">Total Abonado</span>
            <span class="stat-value"><?= formatMoney($proyecto['total_abonado']) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= ($proyecto['saldo_pendiente'] ?? 0) > 0 ? 'red' : 'green' ?>">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Saldo Pendiente</span>
            <span class="stat-value"><?= formatMoney($proyecto['saldo_pendiente']) ?></span>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- ─── Empleados Asignados ─────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-users" style="color: var(--primary-500); margin-right: 8px;"></i>Empleados Asignados</h3>
        </div>
        <div class="card-body" style="padding: var(--space-4) var(--space-6);">
            <?php if (!empty($empleados)): ?>
                <ul class="project-list">
                    <?php foreach ($empleados as $emp): ?>
                        <li class="project-item">
                            <div class="project-info">
                                <div class="project-name"><?= e($emp['nombre_completo']) ?></div>
                                <div class="project-client"><?= e($emp['cargo'] ?? '') ?> — Asignado: <?= formatDate($emp['fecha_asignacion']) ?></div>
                            </div>
                            <span class="badge <?= estadoClass($emp['estado']) ?>"><?= e($emp['estado']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-plus"></i>
                    <p>Sin empleados asignados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ─── Transacciones ───────────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-money-bill-wave" style="color: var(--success-color); margin-right: 8px;"></i>Transacciones</h3>
            <a href="<?= url('transaccion/create') ?>" class="btn btn-sm btn-success">
                <i class="fas fa-plus"></i> Nuevo
            </a>
        </div>
        <div class="card-body" style="padding: var(--space-4) var(--space-6);">
            <?php if (!empty($transacciones)): ?>
                <ul class="project-list">
                    <?php foreach ($transacciones as $t): ?>
                        <li class="project-item">
                            <div class="project-info">
                                <div class="project-name"><?= formatMoney($t['monto_abonado']) ?></div>
                                <div class="project-client"><?= e($t['tipo_de_transaccion']) ?> — <?= formatDate($t['fecha_de_pago']) ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <p>Sin transacciones</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ─── Valuaciones ─────────────────────────────────────────── -->
<div class="card mt-6">
    <div class="card-header">
        <h3><i class="fas fa-clipboard-check" style="color: var(--accent-500); margin-right: 8px;"></i>Valuaciones (Avalúos)</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($valuaciones)): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Monto Estimado</th>
                            <th>Avance</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($valuaciones as $v): ?>
                            <tr>
                                <td><?= formatDate($v['fecha_del_valuo']) ?></td>
                                <td><?= formatMoney($v['monto_estimado']) ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: var(--space-3);">
                                        <div class="progress-bar" style="flex: 1;">
                                            <div class="progress-fill green" style="width: <?= $v['porcentaje_de_avance'] ?>%"></div>
                                        </div>
                                        <span class="text-sm fw-600"><?= formatPercent($v['porcentaje_de_avance']) ?></span>
                                    </div>
                                </td>
                                <td><?= e($v['observaciones'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <p>Sin valuaciones registradas</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ─── Documentos ──────────────────────────────────────────── -->
<div class="card mt-6">
    <div class="card-header">
        <h3><i class="fas fa-folder-open" style="color: var(--info-color); margin-right: 8px;"></i>Documentos</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($documentos)): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Descargar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                            <tr>
                                <td class="fw-600"><?= e($doc['nombre_del_archivo']) ?></td>
                                <td><span class="badge badge-secondary"><?= e($doc['tipo_de_documento']) ?></span></td>
                                <td><?= formatDate($doc['fecha_de_subida']) ?></td>
                                <td>
                                    <a href="<?= url("documento/download/{$doc['id_documento']}") ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-upload"></i>
                <p>Sin documentos adjuntos</p>
            </div>
        <?php endif; ?>
    </div>
</div>
