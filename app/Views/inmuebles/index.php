<?php /** Vista: Inmuebles */ ?>

<div class="page-header">
    <h2><i class="fas fa-building"></i> Inmuebles</h2>
    <a href="<?= url('inmueble/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Inmueble
    </a>
</div>

<!-- Summary Chips -->
<div class="summary-chips">
    <div class="summary-chip">
        <div class="chip-icon purple"><i class="fas fa-building"></i></div>
        <div class="chip-data">
            <span class="chip-value"><?= count($inmuebles ?? []) ?></span>
            <span class="chip-label">Total Inmuebles</span>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="search-input">
        <i class="fas fa-search"></i>
        <input type="text" class="form-control" id="searchInmuebles" placeholder="Buscar por matrícula, cliente o dirección...">
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table class="table" id="tablaInmuebles">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Matrícula</th>
                    <th>Cliente</th>
                    <th>Dirección</th>
                    <th>Área (m²)</th>
                    <th>Tipo</th>
                    <th>Proyectos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inmuebles)): ?>
                    <?php foreach ($inmuebles as $i => $inm): ?>
                        <?php $proyCount = (int)($inm['total_proyectos'] ?? 0); ?>
                        <tr data-id="<?= $inm['id_inmueble'] ?>">
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><span class="cell-icon-text"><i class="fas fa-hashtag"></i> <?= e($inm['matricula']) ?></span></td>
                            <td><?= e($inm['nombre_cliente'] ?? '—') ?></td>
                            <td><span class="cell-icon-text"><i class="fas fa-map-marker-alt"></i> <?= truncate($inm['direccion'] ?? '', 40) ?></span><?php if (!empty($inm['distrito'])): ?><small class="d-block text-muted"><?= e(implode(', ', array_filter([$inm['distrito'] ?? '', $inm['municipio'] ?? '', $inm['departamento'] ?? '']))) ?></small><?php endif; ?></td>
                            <td><?= $inm['area'] ? number_format($inm['area'], 2) : '—' ?></td>
                            <td><span class="badge-dot dot-neutral"><?= e($inm['tipo_inmueble']) ?></span></td>
                            <td>
                                <?php if ($proyCount > 0): ?>
                                    <span class="badge badge-info" title="<?= $proyCount ?> proyecto(s) asociado(s)">
                                        <i class="fas fa-drafting-compass"></i> <?= $proyCount ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary" title="Sin proyectos asociados">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("inmueble/edit/{$inm['id_inmueble']}") ?>" class="btn-action act-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                    <?php if ($proyCount > 0): ?>
                                        <button class="btn-action act-delete btn-delete disabled" 
                                                data-id="<?= $inm['id_inmueble'] ?>" 
                                                data-name="<?= e($inm['matricula']) ?>" 
                                                data-proyectos="<?= $proyCount ?>" 
                                                title="No se puede eliminar: tiene <?= $proyCount ?> proyecto(s) asociado(s)"
                                                disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-action act-delete btn-delete" 
                                                data-id="<?= $inm['id_inmueble'] ?>" 
                                                data-name="<?= e($inm['matricula']) ?>" 
                                                data-proyectos="0" 
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-building"></i>
                                <h3>Sin inmuebles</h3>
                                <p>Registre su primer inmueble para comenzar</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
