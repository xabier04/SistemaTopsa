<?php /** Vista: Listado de Clientes */ ?>

<div class="page-header">
    <h2><i class="fas fa-user-tie"></i> Clientes</h2>
    <a href="<?= url('cliente/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Cliente
    </a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon blue"><i class="fas fa-user-tie"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($clientes ?? []) ?></span>
            <span class="chip-label">Total Clientes</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-project-diagram"></i></div>
        <div class="chip-data">
            <?php $totalProy = 0; foreach ($clientes ?? [] as $c) $totalProy += ($c['total_proyectos'] ?? 0); ?>
            <span class="chip-value"><?= $totalProy ?></span>
            <span class="chip-label">Proyectos Asociados</span>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="search-input">
        <i class="fas fa-search"></i>
        <input type="text" class="form-control" id="searchClientes" placeholder="Buscar por nombre o DUI...">
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table" id="tablaClientes">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>DUI</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Proyectos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($clientes)): ?>
                    <?php foreach ($clientes as $i => $c): ?>
                        <tr data-id="<?= $c['id_cliente'] ?>">
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="cell-with-avatar">
                                    <span class="avatar-sm blue"><?= initials($c['nombre']) ?></span>
                                    <div class="cell-name"><?= e($c['nombre']) ?></div>
                                </div>
                            </td>
                            <td><span class="cell-icon-text"><i class="fas fa-id-card"></i> <?= e($c['dui']) ?></span></td>
                            <td>
                                <?php if (!empty($c['telefono'])): ?>
                                    <span class="cell-icon-text"><i class="fas fa-phone"></i> <?= e($c['telefono']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($c['correo_electronico'])): ?>
                                    <span class="cell-icon-text"><i class="fas fa-envelope"></i> <?= e($c['correo_electronico']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= $c['total_proyectos'] ?? 0 ?></span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("cliente/historial/{$c['id_cliente']}") ?>" class="btn-action act-view btn-historial" data-id="<?= $c['id_cliente'] ?>" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <a href="<?= url("cliente/edit/{$c['id_cliente']}") ?>" class="btn-action act-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-action act-delete btn-delete" data-id="<?= $c['id_cliente'] ?>" data-name="<?= e($c['nombre']) ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-user-plus"></i>
                                <h3>Sin clientes</h3>
                                <p>Registre su primer cliente para comenzar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
    $hasDetalle = !empty($clienteDetalle);
    $cDet = $clienteDetalle ?? null;
    $proys = $proyectos ?? [];

    $totalP = count($proys);
    $enProcesoP = 0;
    $finalizadosP = 0;
    $presupuestoTotalP = 0;

    foreach ($proys as $pItem) {
        $st = strtolower(trim($pItem['estado_del_proyecto'] ?? ''));
        if (str_contains($st, 'proceso') || $st === 'en proceso') $enProcesoP++;
        elseif (str_contains($st, 'finaliz') || $st === 'finalizado') $finalizadosP++;
        $presupuestoTotalP += (float)($pItem['presupuesto_inicial'] ?? 0);
    }
?>

<!-- Drawer Overlay & Container -->
<div class="drawer-overlay <?= $hasDetalle ? 'active' : '' ?>" id="drawerOverlay"></div>

<aside class="client-drawer <?= $hasDetalle ? 'active' : '' ?>" id="clientDrawer" aria-hidden="<?= $hasDetalle ? 'false' : 'true' ?>">
    <div class="drawer-header">
        <div class="drawer-header-left">
            <div class="drawer-header-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="drawer-header-info">
                <h3>Perfil del Cliente</h3>
                <p>Resumen de cuenta y proyectos</p>
            </div>
        </div>
        <button type="button" class="btn-drawer-close" id="closeDrawer" aria-label="Cerrar panel">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="drawer-body" id="drawerBody">
        <?php if ($hasDetalle): ?>
            <!-- Card / Avatar Info -->
            <div class="drawer-profile-card">
                <div class="drawer-avatar-circle"><?= initials($cDet['nombre']) ?></div>
                <h4 class="drawer-client-name"><?= e($cDet['nombre']) ?></h4>
                <div class="drawer-dui-badge">
                    <i class="far fa-id-card"></i> DUI: <?= e($cDet['dui']) ?>
                </div>

                <div class="drawer-contact-grid">
                    <div class="drawer-info-box">
                        <div class="drawer-info-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="drawer-info-text">
                            <div class="drawer-info-label">Teléfono</div>
                            <div class="drawer-info-value"><?= !empty($cDet['telefono']) ? e($cDet['telefono']) : '—' ?></div>
                        </div>
                    </div>
                    <div class="drawer-info-box">
                        <div class="drawer-info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="drawer-info-text">
                            <div class="drawer-info-label">Correo</div>
                            <div class="drawer-info-value"><?= !empty($cDet['correo_electronico']) ? e($cDet['correo_electronico']) : '—' ?></div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($cDet['direccion'])): ?>
                    <div class="drawer-address-box">
                        <div class="drawer-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="drawer-info-text">
                            <div class="drawer-info-value"><?= e($cDet['direccion']) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <a href="<?= url("cliente/edit/{$cDet['id_cliente']}") ?>" class="btn-drawer-edit">
                    <i class="fas fa-user-edit"></i> Editar Información
                </a>
            </div>

            <!-- Stats Row -->
            <div class="drawer-stats-row">
                <div class="drawer-stat-item">
                    <div class="drawer-stat-value"><?= $totalP ?></div>
                    <div class="drawer-stat-label">Total Proyectos</div>
                </div>
                <div class="drawer-stat-item">
                    <div class="drawer-stat-value en-proceso"><?= $enProcesoP ?></div>
                    <div class="drawer-stat-label">En Proceso</div>
                </div>
                <div class="drawer-stat-item">
                    <div class="drawer-stat-value finalizados"><?= $finalizadosP ?></div>
                    <div class="drawer-stat-label">Finalizados</div>
                </div>
            </div>

            <!-- Presupuesto Banner -->
            <div class="drawer-budget-banner">
                <div class="drawer-budget-title">
                    <i class="fas fa-wallet"></i> Presupuesto Total Vinculado
                </div>
                <div class="drawer-budget-value"><?= formatMoney($presupuestoTotalP) ?></div>
            </div>

            <!-- Proyectos Vinculados List -->
            <div class="drawer-projects-header">
                <div class="drawer-projects-title">
                    <i class="fas fa-drafting-compass"></i> Proyectos Vinculados
                </div>
                <span class="drawer-projects-count"><?= $totalP ?> <?= $totalP === 1 ? 'proyecto' : 'proyectos' ?></span>
            </div>

            <?php if (!empty($proys)): ?>
                <?php foreach ($proys as $p): ?>
                    <?php
                        $stP = $p['estado_del_proyecto'] ?? 'En Proceso';
                        $statusClass = match($stP) {
                            'En Proceso' => 'status-en-proceso',
                            'Finalizado' => 'status-finalizado',
                            default => 'status-default'
                        };
                    ?>
                    <div class="drawer-project-card">
                        <div class="drawer-project-card-top">
                            <div class="drawer-project-icon"><i class="fas fa-drafting-compass"></i></div>
                            <div class="drawer-project-info">
                                <h5 class="drawer-project-name"><?= e($p['nombre_del_proyecto']) ?></h5>
                                <span class="drawer-status-pill <?= $statusClass ?>">
                                    <i class="fas fa-circle" style="font-size:0.5rem"></i> <?= e($stP) ?>
                                </span>
                            </div>
                        </div>

                        <div class="drawer-project-details">
                            <div><i class="far fa-calendar-alt"></i> <strong>INICIO:</strong> <?= formatDate($p['fecha_de_inicio']) ?></div>
                            <div><i class="fas fa-dollar-sign"></i> <strong>PRESUPUESTO:</strong> <?= formatMoney($p['presupuesto_inicial']) ?></div>
                        </div>

                        <a href="<?= url("proyecto/detalle/{$p['id_proyecto']}") ?>" class="btn-drawer-view-project">
                            Ver detalles del proyecto <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state p-4 text-center">
                    <i class="fas fa-folder-open text-muted" style="font-size:2rem"></i>
                    <p class="mt-2 text-muted" style="font-size:0.875rem">Este cliente no tiene proyectos vinculados aún.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="drawer-loading text-center p-5">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="mt-2 text-muted">Cargando perfil del cliente...</p>
            </div>
        <?php endif; ?>
    </div>
</aside>

