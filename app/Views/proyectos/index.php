<?php /** Vista: Listado de Proyectos — Sprint Backlog */ ?>
<?php
    $enProceso = 0; $finalizados = 0; $incompletos = 0;
    $totalPresupuesto = 0;
    if (!empty($proyectos)) {
        foreach ($proyectos as $p) {
            $estado = $p['estado_del_proyecto'] ?? '';
            if ($estado === 'Finalizado' || $estado === 'Aprobado') $finalizados++;
            elseif ($estado === 'Incompleto') $incompletos++;
            else $enProceso++;
            $totalPresupuesto += ($p['presupuesto_inicial'] ?? 0);
        }
    }
?>

<div class="page-header topsa-hero">
    <div><span class="topsa-eyebrow">CONTROL DE TRABAJOS</span><h2>Proyectos</h2><p>Organiza tus trabajos y consulta cada detalle desde un solo lugar.</p></div>
    <a href="<?= url('proyecto/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Proyecto
    </a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-drafting-compass"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($proyectos ?? []) ?></span>
            <span class="chip-label">Total Proyectos</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon amber"><i class="fas fa-spinner"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $enProceso ?></span>
            <span class="chip-label">En Proceso</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $finalizados ?></span>
            <span class="chip-label">Finalizados</span>
        </div>
    </div>
    <div class="summary-chip">
        <div class="chip-icon red"><i class="fas fa-exclamation-circle"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= $incompletos ?></span>
            <span class="chip-label">Incompletos</span>
        </div>
    </div>
</div>

<div class="filter-bar project-toolbar">
    <div class="search-input">
        <i class="fas fa-search"></i>
        <input type="search" class="form-control" id="searchProyectos" aria-label="Buscar proyectos" placeholder="Buscar por nombre o cliente...">
    </div>
    <div class="view-switch" role="group" aria-label="Vista de proyectos">
        <button type="button" class="btn btn-outline" data-project-view="table" aria-pressed="true"><i class="fas fa-list" aria-hidden="true"></i> Tabla</button>
        <button type="button" class="btn btn-outline" data-project-view="cards" aria-pressed="false"><i class="fas fa-grip" aria-hidden="true"></i> Tarjetas</button>
    </div>
</div>
<p id="projectResults" class="project-results" role="status" aria-live="polite"></p>

<div class="card" id="projectTable">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Proyecto</th>
                    <th>Cliente</th>
                    <th>Fecha Inicio</th>
                    <th>Presupuesto</th>
                    <th>Inmueble</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($proyectos)): ?>
                    <?php foreach ($proyectos as $i => $p): ?>
                        <?php
                            $estadoProy = $p['estado_del_proyecto'] ?? '';
                            $dotEstado = estadoDotClass($estadoProy);
                        ?>
                        <tr data-project-id="<?= (int) $p['id_proyecto'] ?>" data-state="<?= e($estadoProy) ?>">
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><?= e($p['nombre_del_proyecto']) ?></td>
                            <td><?= e($p['nombre_cliente'] ?? '—') ?></td>
                            <td><span class="cell-icon-text"><i class="fas fa-calendar-alt"></i> <?= formatDate($p['fecha_de_inicio']) ?></span></td>
                            <td><span class="cell-money"><?= formatMoney($p['presupuesto_inicial']) ?></span></td>
                            <td><?= e($p['direccion_inmueble'] ?? '—') ?></td>
                            <td>
                                <span class="badge-dot <?= $dotEstado ?>"><?= e($estadoProy) ?></span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("proyecto/detalle/{$p['id_proyecto']}") ?>" class="btn-action act-view" title="Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= url("proyecto/edit/{$p['id_proyecto']}") ?>" class="btn-action act-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-action act-delete btn-delete" data-id="<?= $p['id_proyecto'] ?>" data-name="<?= e($p['nombre_del_proyecto']) ?>" title="Eliminar">
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
                                <i class="fas fa-project-diagram"></i>
                                <h3>Sin proyectos</h3>
                                <p>Cree su primer proyecto para comenzar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="projectCards" class="project-cards" hidden></div>
<div id="projectEmpty" class="card empty-state" hidden><i class="fas fa-search" aria-hidden="true"></i><h3>No encontramos proyectos</h3><p>Prueba otro nombre o cambia los filtros.</p><button type="button" class="btn btn-outline" id="clearProjectFilters">Limpiar filtros</button></div>
<dialog id="projectPreview" class="project-preview" aria-labelledby="previewTitle">
    <div class="preview-header"><span class="topsa-eyebrow">CONSULTA RÁPIDA</span><button type="button" class="btn btn-outline" id="closeProjectPreview" aria-label="Cerrar consulta">&times;</button></div>
    <h2 id="previewTitle"></h2><div id="previewBody"></div>
    <a id="previewDetail" class="btn btn-primary">Abrir detalle completo <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
</dialog>
