<div class="page-header"><h2><?= e($pageTitle) ?></h2></div>
<div class="card"><div class="card-header"><h3>Proyectos asignados y avance de tareas</h3></div>
<div class="card-body"><label for="filtroProyectos">Mostrar proyectos</label><select id="filtroProyectos" class="form-control"><option value="activos">Activos</option><option value="todos">Todos</option><option value="Finalizado">Finalizados</option></select>
<p>El avance es el promedio de las tareas<?= \Core\Session::isAdmin() ? ' del proyecto' : ' asignadas a usted' ?>.</p></div>
<div class="table-container"><table class="table"><thead><tr><th>Proyecto</th><th>Estado</th><th>Tareas</th><th>Avance</th></tr></thead><tbody>
<?php foreach ($proyectos as $p): ?><tr data-project-state="<?= e($p['estado_del_proyecto']) ?>"><td><?= e($p['nombre_del_proyecto']) ?><?php if (\Core\Session::isAdmin()): ?> · <a href="<?= url('proyecto/detalle/' . $p['id_proyecto']) ?>">Gestionar equipo</a><?php endif; ?></td><td><?= e($p['estado_del_proyecto']) ?></td><td><?= (int) $p['total_tareas'] ?></td><td><?php if ($p['total_tareas']): ?><progress max="100" value="<?= (int) $p['avance'] ?>"></progress> <?= (int) $p['avance'] ?>%<?php else: ?>Sin tareas<?php endif; ?></td></tr><?php endforeach; ?>
<?php if (!$proyectos): ?><tr><td colspan="4">No hay proyectos asignados.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php if (\Core\Session::isAdmin()): ?>
<div class="card mt-6"><div class="card-header"><h3>Asignar tarea</h3></div><form method="post" action="<?= url('tarea/store') ?>"><div class="card-body">
<?= csrf_field() ?>
<div class="form-group"><label for="asignacion">Proyecto y empleado</label><select class="form-control" id="asignacion" name="asignacion" required><option value="">Seleccione una asignación</option><?php foreach ($asignaciones as $a): ?><option value="<?= (int) $a['id_proyecto'] ?>:<?= (int) $a['id_empleado'] ?>"><?= e($a['nombre_del_proyecto'] . ' — ' . $a['nombre_completo']) ?></option><?php endforeach; ?></select><small>Agregue personal desde el detalle del proyecto si no aparece en esta lista.</small></div>
<div class="form-row"><div class="form-group"><label for="titulo">Tarea</label><input id="titulo" name="titulo" class="form-control" required maxlength="150"></div><div class="form-group"><label for="fecha_limite">Fecha límite</label><input id="fecha_limite" type="date" name="fecha_limite" class="form-control" required></div></div>
<div class="form-group"><label for="descripcion">Qué debe hacer</label><textarea id="descripcion" name="descripcion" class="form-control" maxlength="2000"></textarea></div><button class="btn btn-primary" type="submit">Asignar tarea</button>
</div></form></div>
<?php endif; ?>
<div class="card mt-6"><div class="card-header"><h3>Seguimiento del personal</h3></div><div class="card-body">
<?php if (!$tareas): ?><p>No hay tareas registradas.</p><?php endif; ?>
<?php foreach ($tareas as $t): ?>
<form action="<?= url('tarea/avance/' . $t['id_tarea']) ?>" method="post" class="form-card-clean">
<div class="form-card-body"><?= csrf_field() ?>
<h3><?= e($t['titulo']) ?></h3><p><?= e($t['nombre_del_proyecto']) ?> · <?= e($t['nombre_completo']) ?></p>
<p><?= nl2br(e($t['descripcion'] ?? '')) ?></p>
<p>Fecha límite: <?= formatDate($t['fecha_limite']) ?> · <strong><?= $t['avance'] == 100 ? 'Completada' : ($t['avance'] > 0 ? 'En proceso' : 'Pendiente') ?></strong><?= $t['avance'] < 100 && $t['fecha_limite'] < date('Y-m-d') ? ' · Vencida' : '' ?></p>
<div class="form-group"><label for="avance-<?= $t['id_tarea'] ?>">Avance (%)</label><input class="form-control" id="avance-<?= $t['id_tarea'] ?>" name="avance" type="number" min="0" max="100" step="1" required value="<?= (int) $t['avance'] ?>"></div>
<div class="form-group"><label for="obs-<?= $t['id_tarea'] ?>">Observaciones del avance</label><textarea class="form-control" id="obs-<?= $t['id_tarea'] ?>" name="observaciones" maxlength="2000"><?= e($t['observaciones'] ?? '') ?></textarea></div>
<p>Última actualización: <?= e($t['actualizado_en']) ?></p><button class="btn btn-primary" type="submit">Guardar avance</button>
</div></form>
<?php endforeach; ?></div></div>
<script>const filter = document.getElementById('filtroProyectos'); function filterProjects() { document.querySelectorAll('[data-project-state]').forEach(row => { row.hidden = filter.value === 'activos' ? row.dataset.projectState === 'Finalizado' : filter.value !== 'todos' && row.dataset.projectState !== filter.value; }); } filter.addEventListener('change', filterProjects); filterProjects();</script>
