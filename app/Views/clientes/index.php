<?php /** Vista: Listado de Clientes */ ?>

<div class="page-header">
    <h2><i class="fas fa-user-tie"></i> Clientes</h2>
    <a href="<?= url('cliente/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Cliente
    </a>
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
                            <td class="fw-600"><?= e($c['nombre']) ?></td>
                            <td><?= e($c['dui']) ?></td>
                            <td><?= e($c['telefono'] ?? '—') ?></td>
                            <td><?= e($c['correo_electronico'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-info"><?= $c['total_proyectos'] ?? 0 ?></span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("cliente/historial/{$c['id_cliente']}") ?>" class="btn btn-sm btn-outline" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <a href="<?= url("cliente/edit/{$c['id_cliente']}") ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $c['id_cliente'] ?>" data-name="<?= e($c['nombre']) ?>" title="Eliminar">
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
