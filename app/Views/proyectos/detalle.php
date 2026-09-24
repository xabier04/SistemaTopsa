<?php /** Vista: Detalle de Proyecto — Sprint Backlog */ ?>

<div class="page-header">
    <h2><i class="fas fa-drafting-compass"></i> <?= e($proyecto['nombre_del_proyecto']) ?></h2>
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
            <span class="stat-label">Cliente Titular</span>
            <span class="stat-value" style="font-size: var(--text-lg);"><?= e($proyecto['nombre_cliente']) ?></span>
            <span class="text-xs text-muted"><i class="fas fa-phone"></i> <?= e($proyecto['telefono_cliente'] ?? '—') ?></span>
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
        <div class="stat-icon purple"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-content">
            <span class="stat-label">Fecha de Inicio</span>
            <span class="stat-value" style="font-size: var(--text-lg);"><?= formatDate($proyecto['fecha_de_inicio']) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <?php
            $iconColor = match($proyecto['estado_del_proyecto'] ?? '') {
                'Finalizado', 'Aprobado' => 'green',
                'Incompleto'             => 'red',
                default                  => 'amber',
            };
        ?>
        <div class="stat-icon <?= $iconColor ?>">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Estado</span>
            <span class="badge-dot <?= estadoDotClass($proyecto['estado_del_proyecto']) ?>">
                <?= e($proyecto['estado_del_proyecto']) ?>
            </span>
        </div>
    </div>
</div>

<!-- ─── Información del Inmueble ─────────────────────────── -->
<?php if (!empty($proyecto['matricula'])): ?>
<div class="card mt-6" style="margin-bottom: var(--space-6);">
    <div class="card-header">
        <h3><i class="fas fa-map-marked-alt" style="color: var(--accent-500); margin-right: 8px;"></i>Inmueble Asociado</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4);">
            <div>
                <span class="text-xs text-muted">Matrícula</span>
                <div class="fw-600"><?= e($proyecto['matricula']) ?></div>
            </div>
            <div>
                <span class="text-xs text-muted">Dirección</span>
                <div class="fw-600"><?= e($proyecto['direccion_inmueble'] ?? '—') ?></div>
            </div>
            <div>
                <span class="text-xs text-muted">Tipo</span>
                <div class="fw-600"><?= e($proyecto['tipo_inmueble'] ?? '—') ?></div>
            </div>
            <div>
                <span class="text-xs text-muted">Área</span>
                <div class="fw-600"><?= e($proyecto['area'] ?? '—') ?> m²</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ─── Empleados Asignados ─────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-users" style="color: var(--primary-500); margin-right: 8px;"></i>Equipo de Campo Asignado</h3>
    </div>
    <div class="card-body" style="padding: var(--space-4) var(--space-6);">
        <?php if (!empty($empleados)): ?>
            <ul class="project-list">
                <?php foreach ($empleados as $emp): ?>
                    <li class="project-item">
                        <div class="cell-with-avatar" style="flex: 1;">
                            <span class="avatar-sm green"><?= initials($emp['nombre_completo']) ?></span>
                            <div class="project-info">
                                <div class="project-name"><?= e($emp['nombre_completo']) ?></div>
                                <div class="project-client"><?= e($emp['cargo'] ?? '') ?> — Asignado: <?= formatDate($emp['fecha_asignacion']) ?></div>
                            </div>
                        </div>
                        <?php $dotClass = ($emp['estado'] === 'Activo') ? 'dot-success' : 'dot-danger'; ?>
                        <span class="badge-dot <?= $dotClass ?>"><?= e($emp['estado']) ?></span>
                        <form method="post" action="<?= url('proyecto/desasignarEmpleado') ?>"><?= csrf_field() ?><input type="hidden" name="id_proyecto" value="<?= $proyecto['id_proyecto'] ?>"><input type="hidden" name="id_empleado" value="<?= $emp['id_empleado'] ?>"><button type="submit" class="btn btn-sm btn-outline">Quitar del equipo</button></form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-plus"></i>
                <h3>Sin personal asignado</h3>
                <p>No hay técnicos asignados a este proyecto</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-6"><div class="card-header"><h3>Asignar personal al proyecto</h3></div><div class="card-body">
<?php if ($proyecto['estado_del_proyecto'] !== 'Finalizado'): ?>
<form method="post" action="<?= url('proyecto/asignarEmpleado') ?>">
<?= csrf_field() ?><input type="hidden" name="id_proyecto" value="<?= $proyecto['id_proyecto'] ?>">
<div class="form-group"><label for="empleadoAsignar">Empleado activo</label><select class="form-control" id="empleadoAsignar" name="id_empleado" required><option value="">Seleccione un empleado</option>
<?php foreach ($disponibles as $d): if (in_array($d['id_empleado'], array_column($empleados, 'id_empleado'))) continue; ?><option value="<?= $d['id_empleado'] ?>"><?= e($d['nombre_completo']) ?></option><?php endforeach; ?>
</select></div><button class="btn btn-primary" type="submit">Asignar empleado</button>
</form>
<?php else: ?><p>Este proyecto está finalizado.</p><?php endif; ?>
<p><a href="<?= url('tarea/index') ?>">Asignar tareas y consultar avances del equipo</a></p></div></div>
