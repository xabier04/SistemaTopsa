<?php /** Vista: Formulario de Valuación */ ?>

<div class="page-header">
    <h2><i class="fas fa-clipboard-check"></i> <?= $valuo ? 'Editar Valuación' : 'Nueva Valuación' ?></h2>
    <a href="<?= url('valuo/index') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="id_proyecto"><i class="fas fa-project-diagram"></i> Proyecto</label>
                <select id="id_proyecto" name="id_proyecto" class="form-control" required>
                    <option value="">Seleccione un proyecto</option>
                    <?php foreach ($proyectos as $p): ?>
                        <option value="<?= $p['id_proyecto'] ?>" <?= ($valuo['id_proyecto'] ?? '') == $p['id_proyecto'] ? 'selected' : '' ?>>
                            <?= e($p['nombre_del_proyecto']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_del_valuo"><i class="fas fa-calendar"></i> Fecha</label>
                    <input type="date" id="fecha_del_valuo" name="fecha_del_valuo" class="form-control" 
                           value="<?= e($valuo['fecha_del_valuo'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="monto_estimado"><i class="fas fa-dollar-sign"></i> Monto Estimado ($)</label>
                    <input type="number" id="monto_estimado" name="monto_estimado" class="form-control" 
                           value="<?= e($valuo['monto_estimado'] ?? '') ?>" required step="0.01" min="0">
                </div>
            </div>

            <div class="form-group">
                <label for="porcentaje_de_avance"><i class="fas fa-percentage"></i> Porcentaje de Avance (%)</label>
                <input type="number" id="porcentaje_de_avance" name="porcentaje_de_avance" class="form-control" 
                       value="<?= e($valuo['porcentaje_de_avance'] ?? '0') ?>" required step="0.01" min="0" max="100">
            </div>

            <div class="form-group">
                <label for="observaciones"><i class="fas fa-comment-dots"></i> Observaciones</label>
                <textarea id="observaciones" name="observaciones" class="form-control" rows="3"><?= e($valuo['observaciones'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: var(--space-3); justify-content: flex-end; margin-top: var(--space-6);">
                <a href="<?= url('valuo/index') ?>" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $valuo ? 'Actualizar' : 'Guardar' ?></button>
            </div>
        </form>
    </div>
</div>
