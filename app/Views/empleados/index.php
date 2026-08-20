<?php /** Vista: Listado de Empleados */ ?>
<?php
    $totalActivos = 0;
    $totalInactivos = 0;
    if (!empty($empleados)) {
        foreach ($empleados as $emp) {
            if (($emp['estado'] ?? '') === 'Activo') $totalActivos++;
            else $totalInactivos++;
        }
    }
    $isAdmin = \Core\Session::isAdmin();
?>

<div class="page-header">
    <h2><i class="fas fa-users-cog"></i> Empleados</h2>
    <?php if ($isAdmin): ?>
    <a href="<?= url('empleado/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Empleado
    </a>
    <?php endif; ?>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-users"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($empleados ?? []) ?></span>
            <span class="chip-label">Total Empleados</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-user-check"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $totalActivos ?></span>
            <span class="chip-label">Activos</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon red"><i class="fas fa-user-times"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $totalInactivos ?></span>
            <span class="chip-label">Inactivos</span>
        </div>
    </div>
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
                    <?php if ($isAdmin): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($empleados)): ?>
                    <?php foreach ($empleados as $i => $emp): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="cell-with-avatar">
                                    <span class="avatar-sm green"><?= initials($emp['nombre_completo']) ?></span>
                                    <div>
                                        <div class="cell-name"><?= e($emp['nombre_completo']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="cell-icon-text"><i class="fas fa-id-card"></i> <?= e($emp['dui']) ?></span></td>
                            <td><?= e($emp['cargo']) ?></td>
                            <td>
                                <?php if ($emp['telefono']): ?>
                                    <span class="cell-icon-text"><i class="fas fa-phone"></i> <?= e($emp['telefono']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $dotClass = ($emp['estado'] === 'Activo') ? 'dot-success' : 'dot-danger';
                                ?>
                                <span class="badge-dot <?= $dotClass ?>"><?= e($emp['estado']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= $emp['total_proyectos'] ?? 0 ?></span>
                            </td>
                            <?php if ($isAdmin): ?>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("empleado/edit/{$emp['id_empleado']}") ?>" class="btn-action act-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-action act-delete btn-delete" data-id="<?= $emp['id_empleado'] ?>" data-name="<?= e($emp['nombre_completo']) ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $isAdmin ? 8 : 7 ?>">
                            <div class="empty-state">
                                <i class="fas fa-user-plus"></i>
                                <h3>Sin empleados</h3>
                                <p>No hay empleados registrados</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
