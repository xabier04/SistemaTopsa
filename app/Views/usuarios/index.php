<?php /** Vista: Gestión de Usuarios */ ?>

<div class="page-header">
    <h2><i class="fas fa-user-shield"></i> Usuarios del Sistema</h2>
    <a href="<?= url('usuario/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Usuario</a>
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
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><?= e($u['nombre']) ?></td>
                            <td><?= e($u['correo']) ?></td>
                            <td><?= e($u['nombre_completo'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= $u['rol'] === 'Administrador' ? 'badge-info' : 'badge-secondary' ?>">
                                    <?= e($u['rol']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= estadoClass($u['estado_de_cuenta']) ?>" 
                                      id="estado-<?= $u['id_usuario'] ?>">
                                    <?= e($u['estado_de_cuenta']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("usuario/edit/{$u['id_usuario']}") ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-sm btn-outline btn-toggle-estado" 
                                            data-id="<?= $u['id_usuario'] ?>" title="Cambiar estado">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7"><div class="empty-state"><i class="fas fa-users"></i><h3>Sin usuarios</h3></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
