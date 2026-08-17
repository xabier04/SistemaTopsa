<?php /** Vista: Inmuebles */ ?>

<div class="page-header">
    <h2><i class="fas fa-building"></i> Inmuebles</h2>
    <a href="<?= url('inmueble/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Inmueble
    </a>
</div>

<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Matrícula</th>
                    <th>Cliente</th>
                    <th>Dirección</th>
                    <th>Área (m²)</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inmuebles)): ?>
                    <?php foreach ($inmuebles as $i => $inm): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><?= e($inm['matricula']) ?></td>
                            <td><?= e($inm['nombre_cliente'] ?? '—') ?></td>
                            <td><?= truncate($inm['direccion'] ?? '', 40) ?></td>
                            <td><?= $inm['area'] ? number_format($inm['area'], 2) : '—' ?></td>
                            <td><span class="badge badge-secondary"><?= e($inm['tipo_inmueble']) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url("inmueble/edit/{$inm['id_inmueble']}") ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $inm['id_inmueble'] ?>" data-name="<?= e($inm['matricula']) ?>"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7"><div class="empty-state"><i class="fas fa-building"></i><h3>Sin inmuebles</h3></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
