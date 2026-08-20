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
                                    <a href="<?= url("cliente/historial/{$c['id_cliente']}") ?>" class="btn-action act-view" title="Historial">
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
