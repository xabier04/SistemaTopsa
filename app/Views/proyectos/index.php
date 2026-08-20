<?php /** Vista: Listado de Proyectos */ ?>
<?php
    $enProceso = 0; $finalizados = 0; $incompletos = 0;
    $totalPresupuesto = 0; $totalAbonado = 0;
    if (!empty($proyectos)) {
        foreach ($proyectos as $p) {
            $estado = $p['estado_del_proyecto'] ?? '';
            if ($estado === 'En Proceso') $enProceso++;
            elseif ($estado === 'Finalizado') $finalizados++;
            else $incompletos++;
            $totalPresupuesto += ($p['presupuesto_inicial'] ?? 0);
            $totalAbonado += ($p['total_abonado'] ?? 0);
        }
    }
?>

<div class="page-header">
    <h2><i class="fas fa-project-diagram"></i> Proyectos</h2>
    <a href="<?= url('proyecto/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Proyecto
    </a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-drafting-compass"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($proyectos ?? []) ?></span>
            <span class="chip-label">Total Proyectos</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon amber"><i class="fas fa-spinner"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $enProceso ?></span>
            <span class="chip-label">En Proceso</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $finalizados ?></span>
            <span class="chip-label">Finalizados</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon red"><i class="fas fa-exclamation-circle"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $incompletos ?></span>
            <span class="chip-label">Incompletos</span>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="search-input">
        <i class="fas fa-search"></i>
        <input type="text" class="form-control" id="searchProyectos" placeholder="Buscar por nombre o cliente...">
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
                    <th>Fecha Inicio</th>
                    <th>Presupuesto</th>
                    <th>Abonado</th>
                    <th>Saldo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($proyectos)): ?>
                    <?php foreach ($proyectos as $i => $p): ?>
                        <?php
                            $saldo = $p['saldo_pendiente'] ?? 0;
                            $estadoProy = $p['estado_del_proyecto'] ?? '';
                            $dotEstado = match($estadoProy) {
                                'Finalizado' => 'dot-success',
                                'En Proceso' => 'dot-warning',
                                'Incompleto' => 'dot-danger',
                                default => 'dot-neutral'
                            };
                            // Calculate payment progress
                            $presupuesto = $p['presupuesto_inicial'] ?? 0;
                            $abonado = $p['total_abonado'] ?? 0;
                            $pagoPct = $presupuesto > 0 ? min(100, round(($abonado / $presupuesto) * 100)) : 0;
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><?= e($p['nombre_del_proyecto']) ?></td>
                            <td><?= e($p['nombre_cliente'] ?? '—') ?></td>
                            <td><span class="cell-icon-text"><i class="fas fa-calendar-alt"></i> <?= formatDate($p['fecha_de_inicio']) ?></span></td>
                            <td><span class="cell-money"><?= formatMoney($p['presupuesto_inicial']) ?></span></td>
                            <td><span class="cell-money positive"><?= formatMoney($abonado) ?></span></td>
                            <td><span class="cell-money <?= $saldo > 0 ? 'negative' : 'positive' ?>"><?= formatMoney($saldo) ?></span></td>
                            <td>
                                <span class="badge-dot <?= $dotEstado ?>"><?= e($estadoProy) ?></span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("proyecto/detalle/{$p['id_proyecto']}") ?>" class="btn-action act-view" title="Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= url("proyecto/edit/{$p['id_proyecto']}") ?>" class="btn-action act-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-action act-delete btn-delete" data-id="<?= $p['id_proyecto'] ?>" data-name="<?= e($p['nombre_del_proyecto']) ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-project-diagram"></i>
                                <h3>Sin proyectos</h3>
                                <p>Cree su primer proyecto para comenzar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
