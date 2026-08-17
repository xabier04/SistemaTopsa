<?php /** Vista: Listado de Empleados */ ?>

<div class="page-header">
    <h2><i class="fas fa-users-cog"></i> Empleados</h2>
    <a href="<?= url('empleado/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Empleado
    </a>
</div>

<div class="filter-bar">
    <div class="search-input">
        <i class="fas fa-search"></i>
        <input type="text" class="form-control" id="searchEmpleados" placeholder="Buscar por nombre, DUI o cargo...">
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table" id="tablaEmpleados">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>DUI</th>
                    <th>Cargo</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Proyectos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($empleados)): ?>
                    <?php foreach ($empleados as $i => $emp): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><?= e($emp['nombre_completo']) ?></td>
                            <td><?= e($emp['dui']) ?></td>
                            <td><?= e($emp['cargo']) ?></td>
                            <td><?= e($emp['telefono'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= estadoClass($emp['estado']) ?>"><?= e($emp['estado']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= $emp['total_proyectos'] ?? 0 ?></span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("empleado/edit/{$emp['id_empleado']}") ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $emp['id_empleado'] ?>" data-name="<?= e($emp['nombre_completo']) ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-user-plus"></i>
                                <h3>Sin empleados</h3>
                                <p>Registre su primer empleado para comenzar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
