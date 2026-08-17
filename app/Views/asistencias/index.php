<?php /** Vista: Control de Asistencia */ ?>

<div class="page-header">
    <h2><i class="fas fa-clock"></i> Control de Asistencia</h2>
    <a href="<?= url('asistencia/consolidado') ?>" class="btn btn-outline">
        <i class="fas fa-chart-bar"></i> Consolidado
    </a>
</div>

<!-- Marcaje -->
<div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: var(--space-6);">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-sign-in-alt" style="color: var(--success-color); margin-right: 8px;"></i>Marcar Entrada</h3>
        </div>
        <div class="card-body">
            <form action="<?= url('asistencia/marcarEntrada') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="emp_entrada"><i class="fas fa-user"></i> Empleado</label>
                    <select id="emp_entrada" name="id_empleado" class="form-control" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($empleados as $emp): ?>
                            <option value="<?= $emp['id_empleado'] ?>"><?= e($emp['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-sign-in-alt"></i> Registrar Entrada</button>
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
                    <select id="emp_salida" name="id_empleado" class="form-control" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($empleados as $emp): ?>
                            <option value="<?= $emp['id_empleado'] ?>"><?= e($emp['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-sign-out-alt"></i> Registrar Salida</button>
            </form>
        </div>
    </div>
</div>

<!-- Registros del día -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list" style="color: var(--primary-500); margin-right: 8px;"></i>Registros del <?= formatDate($fechaFiltro) ?></h3>
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
                            <td class="fw-600"><?= e($a['nombre_completo'] ?? '—') ?></td>
                            <td><?= e($a['cargo'] ?? '—') ?></td>
                            <td><span class="badge badge-success"><?= formatTime($a['hora_de_entrada']) ?></span></td>
                            <td>
                                <?php if ($a['hora_de_salida']): ?>
                                    <span class="badge badge-danger"><?= formatTime($a['hora_de_salida']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $a['horas_trabajadas'] ? number_format($a['horas_trabajadas'], 2) . ' h' : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5"><div class="empty-state"><i class="fas fa-clock"></i><h3>Sin registros</h3></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
