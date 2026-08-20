<?php /** Vista: Control de Asistencia */ ?>
<?php
    $user = \Core\Session::getUser();
    $isAdmin = \Core\Session::isAdmin();
    $totalHoy = count($asistencias ?? []);
    $conSalida = 0;
    $pendientes = 0;
    foreach ($asistencias ?? [] as $a) {
        if (!empty($a['hora_de_salida'])) $conSalida++;
        else $pendientes++;
    }
?>

<div class="page-header">
    <h2><i class="fas fa-clock"></i> Control de Asistencia</h2>
    <a href="<?= url('asistencia/consolidado') ?>" class="btn btn-outline">
        <i class="fas fa-chart-bar"></i> Consolidado
    </a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-calendar-check"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $totalHoy ?></span>
            <span class="chip-label"><?= $isAdmin ? 'Marcajes Hoy' : 'Mi Registro Hoy' ?></span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon amber"><i class="fas fa-user-clock"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $pendientes ?></span>
            <span class="chip-label"><?= $isAdmin ? 'Jornadas en Curso' : 'Pendiente de Salida' ?></span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon blue"><i class="fas fa-check-double"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $conSalida ?></span>
            <span class="chip-label">Jornadas Completadas</span>
        </div>
    </div>
</div>

<!-- Marcaje -->
<div class="stats-grid" style="grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-bottom: var(--space-6);">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-sign-in-alt" style="color: var(--success-color); margin-right: 8px;"></i>Marcar Entrada</h3>
        </div>
        <div class="card-body">
            <form action="<?= url('asistencia/marcarEntrada') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="emp_entrada"><i class="fas fa-user"></i> Empleado</label>
                    <?php if ($isAdmin): ?>
                        <select id="emp_entrada" name="id_empleado" class="form-control" required>
                            <option value="">Seleccione un empleado</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?= $emp['id_empleado'] ?>"><?= e($emp['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="hidden" name="id_empleado" value="<?= (int)($user['id_empleado'] ?? 0) ?>">
                        <div class="cell-with-avatar" style="padding: var(--space-3); background: var(--gray-100); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
                            <span class="avatar-sm green"><?= initials($currentEmpleado['nombre_completo'] ?? $user['nombre'] ?? 'U') ?></span>
                            <div>
                                <div class="cell-name"><?= e($currentEmpleado['nombre_completo'] ?? $user['nombre'] ?? '') ?></div>
                                <div class="cell-sub"><?= e($currentEmpleado['cargo'] ?? 'Empleado') ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-sign-in-alt"></i> Registrar Mi Entrada</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-sign-out-alt" style="color: var(--error-color); margin-right: 8px;"></i>Marcar Salida</h3>
        </div>
        <div class="card-body">
            <form action="<?= url('asistencia/marcarSalida') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="emp_salida"><i class="fas fa-user"></i> Empleado</label>
                    <?php if ($isAdmin): ?>
                        <select id="emp_salida" name="id_empleado" class="form-control" required>
                            <option value="">Seleccione un empleado</option>
                            <?php foreach ($empleados as $emp): ?>
                                <option value="<?= $emp['id_empleado'] ?>"><?= e($emp['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="hidden" name="id_empleado" value="<?= (int)($user['id_empleado'] ?? 0) ?>">
                        <div class="cell-with-avatar" style="padding: var(--space-3); background: var(--gray-100); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
                            <span class="avatar-sm green"><?= initials($currentEmpleado['nombre_completo'] ?? $user['nombre'] ?? 'U') ?></span>
                            <div>
                                <div class="cell-name"><?= e($currentEmpleado['nombre_completo'] ?? $user['nombre'] ?? '') ?></div>
                                <div class="cell-sub"><?= e($currentEmpleado['cargo'] ?? 'Empleado') ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-sign-out-alt"></i> Registrar Mi Salida</button>
            </form>
        </div>
    </div>
</div>

<!-- Registros del día -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list" style="color: var(--primary-500); margin-right: 8px;"></i><?= $isAdmin ? 'Registros del ' . formatDate($fechaFiltro) : 'Mi Marcaje del ' . formatDate($fechaFiltro) ?></h3>
        <form style="display:flex; gap:var(--space-3); align-items:center;">
            <input type="date" name="fecha" class="form-control" value="<?= e($fechaFiltro) ?>" style="width:auto;" onchange="this.form.submit()">
        </form>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Cargo</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Horas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($asistencias)): ?>
                    <?php foreach ($asistencias as $a): ?>
                        <tr>
                            <td>
                                <div class="cell-with-avatar">
                                    <span class="avatar-sm green"><?= initials($a['nombre_completo'] ?? 'U') ?></span>
                                    <div class="cell-name"><?= e($a['nombre_completo'] ?? '—') ?></div>
                                </div>
                            </td>
                            <td><?= e($a['cargo'] ?? '—') ?></td>
                            <td><span class="badge-dot dot-success"><?= formatTime($a['hora_de_entrada']) ?></span></td>
                            <td>
                                <?php if ($a['hora_de_salida']): ?>
                                    <span class="badge-dot dot-danger"><?= formatTime($a['hora_de_salida']) ?></span>
                                <?php else: ?>
                                    <span class="badge-dot dot-warning">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="fw-600"><?= $a['horas_trabajadas'] ? number_format($a['horas_trabajadas'], 2) . ' h' : '—' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-clock"></i>
                                <h3>Sin registros</h3>
                                <p><?= $isAdmin ? 'No hay registros de asistencia para esta fecha' : 'No tienes registros de asistencia para esta fecha' ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
