<?php
/**
 * Dashboard — Vista Principal
 */

// Mapear estados a datos legibles
$estadosMap = [];
foreach ($estadosProyecto as $e) {
    $estadosMap[$e['estado_del_proyecto']] = $e['total'];
}
$enProceso   = $estadosMap['En Proceso'] ?? 0;
$incompletos = $estadosMap['Incompleto'] ?? 0;
$finalizados = $estadosMap['Finalizado'] ?? 0;
?>

<!-- ─── Tarjetas de Estadísticas ──────────────────────────── -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-project-diagram"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Proyectos Totales</span>
            <span class="stat-value"><?= $totalProyectos ?></span>
            <span class="stat-change">
                <span class="badge badge-info"><?= $enProceso ?> en proceso</span>
            </span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Clientes</span>
            <span class="stat-value"><?= $totalClientes ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-users-cog"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Empleados Activos</span>
            <span class="stat-value"><?= $totalEmpleados ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <span class="stat-label">Ingresos Totales</span>
            <span class="stat-value"><?= formatMoney($totalIngresos) ?></span>
        </div>
    </div>
</div>

<!-- ─── Grid Principal ────────────────────────────────────── -->
<div class="dashboard-grid">
    <!-- Proyectos Recientes -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clock" style="color: var(--primary-500); margin-right: 8px;"></i>Proyectos Recientes</h3>
            <a href="<?= url('proyecto/index') ?>" class="btn btn-sm btn-outline">Ver todos</a>
        </div>
        <div class="card-body" style="padding: var(--space-4) var(--space-6);">
            <?php if (!empty($proyectosRecientes)): ?>
                <ul class="project-list">
                    <?php foreach ($proyectosRecientes as $proy): ?>
                        <li class="project-item">
                            <div class="project-info">
                                <div class="project-name"><?= e($proy['nombre_del_proyecto']) ?></div>
                                <div class="project-client"><?= e($proy['nombre_cliente']) ?> — <?= formatDate($proy['fecha_de_inicio']) ?></div>
                            </div>
                            <span class="badge <?= estadoClass($proy['estado_del_proyecto']) ?>">
                                <?= e($proy['estado_del_proyecto']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Sin proyectos</h3>
                    <p>Aún no hay proyectos registrados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumen de Estados -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie" style="color: var(--accent-500); margin-right: 8px;"></i>Estado de Proyectos</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: var(--space-5);">
                <!-- En Proceso -->
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                        <span class="text-sm fw-600">En Proceso</span>
                        <span class="text-sm text-muted"><?= $enProceso ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $totalProyectos > 0 ? round($enProceso / $totalProyectos * 100) : 0 ?>%"></div>
                    </div>
                </div>

                <!-- Incompletos -->
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                        <span class="text-sm fw-600">Incompletos</span>
                        <span class="text-sm text-muted"><?= $incompletos ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill amber" style="width: <?= $totalProyectos > 0 ? round($incompletos / $totalProyectos * 100) : 0 ?>%"></div>
                    </div>
                </div>

                <!-- Finalizados -->
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--space-2);">
                        <span class="text-sm fw-600">Finalizados</span>
                        <span class="text-sm text-muted"><?= $finalizados ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill green" style="width: <?= $totalProyectos > 0 ? round($finalizados / $totalProyectos * 100) : 0 ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Total Presupuesto -->
            <div style="margin-top: var(--space-8); padding-top: var(--space-5); border-top: 1px solid var(--gray-200);">
                <span class="text-xs text-muted" style="text-transform: uppercase; letter-spacing: 0.5px;">Presupuesto Total</span>
                <div style="font-family: var(--font-heading); font-size: var(--text-xl); font-weight: 700; color: var(--gray-900); margin-top: var(--space-1);">
                    <?= formatMoney($totalPresupuesto) ?>
                </div>
            </div>
        </div>
    </div>
</div>
