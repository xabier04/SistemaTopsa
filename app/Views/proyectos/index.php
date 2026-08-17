<?php /** Vista: Listado de Proyectos */ ?>

<div class="page-header">
    <h2><i class="fas fa-project-diagram"></i> Proyectos</h2>
    <a href="<?= url('proyecto/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Proyecto
    </a>
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
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><?= e($p['nombre_del_proyecto']) ?></td>
                            <td><?= e($p['nombre_cliente'] ?? '—') ?></td>
                            <td><?= formatDate($p['fecha_de_inicio']) ?></td>
                            <td><?= formatMoney($p['presupuesto_inicial']) ?></td>
                            <td style="color: var(--success-color);"><?= formatMoney($p['total_abonado'] ?? 0) ?></td>
                            <td style="color: <?= ($p['saldo_pendiente'] ?? 0) > 0 ? 'var(--error-color)' : 'var(--success-color)' ?>;">
                                <?= formatMoney($p['saldo_pendiente'] ?? 0) ?>
                            </td>
                            <td>
                                <span class="badge <?= estadoClass($p['estado_del_proyecto']) ?>">
                                    <?= e($p['estado_del_proyecto']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("proyecto/detalle/{$p['id_proyecto']}") ?>" class="btn btn-sm btn-outline" title="Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= url("proyecto/edit/{$p['id_proyecto']}") ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $p['id_proyecto'] ?>" data-name="<?= e($p['nombre_del_proyecto']) ?>" title="Eliminar">
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
