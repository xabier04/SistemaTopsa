<?php /** Vista: Formulario de Proyecto */ ?>

<div class="page-header">
    <h2>
        <i class="fas fa-project-diagram"></i>
        <?= $proyecto ? 'Editar Proyecto' : 'Nuevo Proyecto' ?>
    </h2>
    <a href="<?= url('proyecto/index') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card" style="max-width: 750px;">
    <div class="card-body">
        <form action="<?= $action ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="nombre_del_proyecto"><i class="fas fa-file-signature"></i> Nombre del Proyecto</label>
                <input type="text" id="nombre_del_proyecto" name="nombre_del_proyecto" class="form-control" 
                       value="<?= e($proyecto['nombre_del_proyecto'] ?? '') ?>" required maxlength="60">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="id_cliente"><i class="fas fa-user-tie"></i> Cliente</label>
                    <select id="id_cliente" name="id_cliente" class="form-control" required>
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>" 
                                <?= ($proyecto['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>>
                                <?= e($c['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_inmueble"><i class="fas fa-building"></i> Inmueble (opcional)</label>
                    <select id="id_inmueble" name="id_inmueble" class="form-control">
                        <option value="">Sin inmueble</option>
                        <?php foreach ($inmuebles as $inm): ?>
                            <option value="<?= $inm['id_inmueble'] ?>"
                                <?= ($proyecto['id_inmueble'] ?? '') == $inm['id_inmueble'] ? 'selected' : '' ?>>
                                <?= e($inm['matricula']) ?> — <?= e($inm['nombre_cliente'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_de_inicio"><i class="fas fa-calendar"></i> Fecha de Inicio</label>
                    <input type="date" id="fecha_de_inicio" name="fecha_de_inicio" class="form-control" 
                           value="<?= e($proyecto['fecha_de_inicio'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="presupuesto_inicial"><i class="fas fa-dollar-sign"></i> Presupuesto Inicial ($)</label>
                    <input type="number" id="presupuesto_inicial" name="presupuesto_inicial" class="form-control" 
                           value="<?= e($proyecto['presupuesto_inicial'] ?? '0.00') ?>" required step="0.01" min="0">
                </div>
            </div>

            <div class="form-group">
                <label for="estado_del_proyecto"><i class="fas fa-flag"></i> Estado del Proyecto</label>
                <select id="estado_del_proyecto" name="estado_del_proyecto" class="form-control" required>
                    <option value="En Proceso" <?= ($proyecto['estado_del_proyecto'] ?? 'En Proceso') === 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                    <option value="Incompleto" <?= ($proyecto['estado_del_proyecto'] ?? '') === 'Incompleto' ? 'selected' : '' ?>>Incompleto</option>
                    <option value="Finalizado" <?= ($proyecto['estado_del_proyecto'] ?? '') === 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
                </select>
            </div>

            <div style="display: flex; gap: var(--space-3); justify-content: flex-end; margin-top: var(--space-6);">
                <a href="<?= url('proyecto/index') ?>" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $proyecto ? 'Actualizar' : 'Guardar' ?>
                </button>
            </div>
        </form>
    </div>
</div>
