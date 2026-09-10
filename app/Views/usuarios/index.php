<?php /** Vista: Gestión de Usuarios */ ?>

<div class="page-header">
    <h2><i class="fas fa-user-shield"></i> Usuarios del Sistema</h2>
    <a href="<?= url('usuario/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Usuario</a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon blue"><i class="fas fa-users"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($usuarios ?? []) ?></span>
            <span class="chip-label">Total Usuarios</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Empleado</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Contraseña</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="cell-with-avatar">
                                    <span class="avatar-sm <?= $u['rol'] === 'Administrador' ? 'green' : 'blue' ?>"><?= initials($u['nombre']) ?></span>
                                    <div class="cell-name"><?= e($u['nombre']) ?></div>
                                </div>
                            </td>
                            <td><span class="cell-icon-text"><i class="fas fa-envelope"></i> <?= e($u['correo']) ?></span></td>
                            <td><?= e($u['nombre_completo'] ?? '—') ?></td>
                            <td>
                                <span class="badge-dot <?= $u['rol'] === 'Administrador' ? 'dot-info' : 'dot-neutral' ?>">
                                    <?= e($u['rol']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    $estadoDot = match($u['estado_de_cuenta']) {
                                        'Activo' => 'dot-success',
                                        'Inactivo' => 'dot-danger',
                                        default => 'dot-neutral'
                                    };
                                ?>
                                <span class="badge-dot <?= $estadoDot ?>" id="estado-<?= $u['id_usuario'] ?>">
                                    <?= e($u['estado_de_cuenta']) ?>
                                </span>
                            </td>
                            <td><?= !empty($u['requiere_cambio_contrasena']) ? 'Temporal · pendiente de cambio' : 'Establecida' ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("usuario/edit/{$u['id_usuario']}") ?>" class="btn-action act-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                    <button class="btn-action act-toggle btn-toggle-estado" data-id="<?= $u['id_usuario'] ?>" title="Cambiar estado">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <h3>Sin usuarios</h3>
                                <p>Cree el primer usuario del sistema</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
