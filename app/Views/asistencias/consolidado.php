<?php
/** Vista: Consolidado de Asistencia */
$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$user = \Core\Session::getUser();
$isAdmin = \Core\Session::isAdmin();
?>

<div class="page-header">
    <h2><i class="fas fa-chart-bar"></i> Consolidado Mensual</h2>
    <a href="<?= url('asistencia/index') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card mb-6">
    <div class="card-body">
        <form method="GET" action="<?= url('asistencia/consolidado') ?>" class="form-row" style="grid-template-columns: <?= $isAdmin ? '1.5fr 1fr 1fr auto' : '1fr 1fr auto' ?>; align-items: end;">
            <?php if ($isAdmin): ?>
            <div class="form-group" style="margin-bottom:0;">
                <label><i class="fas fa-user"></i> Empleado</label>
                <select name="id_empleado" class="form-control" required>
                    <option value="">Seleccione un empleado</option>
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id_empleado'] ?>" <?= $idEmpleado == $emp['id_empleado'] ? 'selected' : '' ?>><?= e($emp['nombre_completo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="id_empleado" value="<?= (int)($user['id_empleado'] ?? 0) ?>">
            <?php endif; ?>
            
            <div class="form-group" style="margin-bottom:0;">
                <label><i class="fas fa-calendar-alt"></i> Mes</label>
                <select name="mes" class="form-control">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $mesActual == $m ? 'selected' : '' ?>><?= $meses[$m] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom:0;">
                <label><i class="fas fa-calendar"></i> Año</label>
                <input type="number" name="anio" class="form-control" value="<?= $anioActual ?>" min="2020" max="2030">
            </div>
            
            <button type="submit" class="btn btn-primary" style="height:42px;"><i class="fas fa-search"></i> Consultar</button>
        </form>
    </div>
</div>

<?php if ($idEmpleado > 0): ?>
    <div class="stats-grid" style="grid-template-columns: 1fr 1fr 1fr; margin-bottom: var(--space-6);">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-user"></i></div>
            <div class="stat-content">
                <span class="stat-label">Empleado</span>
                <span class="stat-value" style="font-size: var(--text-lg);"><?= e($empleadoSeleccionado['nombre_completo'] ?? $user['nombre'] ?? '—') ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-content">
                <span class="stat-label">Días Trabajados</span>
                <span class="stat-value"><?= count($registros) ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Horas</span>
                <span class="stat-value"><?= number_format($totalHoras, 2) ?> h</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-table" style="color: var(--primary-500); margin-right: 8px;"></i>Detalle — <?= $meses[$mesActual] ?> <?= $anioActual ?></h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Horas</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><span class="cell-icon-text"><i class="fas fa-calendar-day"></i> <?= formatDate($r['fecha_de_marcaje']) ?></span></td>
                            <td><span class="badge-dot dot-success"><?= formatTime($r['hora_de_entrada']) ?></span></td>
                            <td><?= $r['hora_de_salida'] ? '<span class="badge-dot dot-danger">' . formatTime($r['hora_de_salida']) . '</span>' : '<span class="badge-dot dot-warning">Pendiente</span>' ?></td>
                            <td><span class="fw-600"><?= $r['horas_trabajadas'] ? number_format($r['horas_trabajadas'], 2) . ' h' : '—' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($registros)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <h3>Sin registros</h3>
                                    <p>No se encontraron registros de asistencia para este período</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
