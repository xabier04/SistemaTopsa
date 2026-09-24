<?php /** Vista: Formulario de Empleado */ 
$formState = \Core\FormState::take($action);
$formValues = $formState['values'] ?? ($empleado ?? []);
$formErrors = $formState['errors'] ?? [];
$cargosLista = $cargos ?? \App\Models\Empleado::getCargosOficiales();
$currentCargo = $formValues['cargo'] ?? '';
if ($currentCargo && !in_array($currentCargo, $cargosLista, true)) {
    array_unshift($cargosLista, $currentCargo);
}
$currentEstado = $formValues['estado'] ?? 'Activo';
if ($currentEstado === 'De Vacaciones') {
    $currentEstado = 'Vacaciones';
}

$isEdit = !empty($empleado);
$estadosDisponibles = $isEdit 
    ? [
        'Activo'       => ['label' => 'Activo', 'icon' => 'fa-user-check', 'class' => 'state-activo'],
        'Vacaciones'   => ['label' => 'Vacaciones', 'icon' => 'fa-umbrella-beach', 'class' => 'state-vacaciones'],
        'Incapacitado' => ['label' => 'Incapacitado', 'icon' => 'fa-user-nurse', 'class' => 'state-incapacitado'],
        'Inactivo'     => ['label' => 'Inactivo', 'icon' => 'fa-user-slash', 'class' => 'state-inactivo']
      ]
    : [
        'Activo'       => ['label' => 'Activo', 'icon' => 'fa-user-check', 'class' => 'state-activo'],
        'Inactivo'     => ['label' => 'Inactivo', 'icon' => 'fa-user-slash', 'class' => 'state-inactivo']
      ];
?>

<style>
/* ─── Modern Custom Select Component ────────────────────── */
.custom-select-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.custom-select-wrapper .select-icon-left {
    position: absolute;
    left: 1rem;
    color: #64748b;
    font-size: 0.95rem;
    pointer-events: none;
    transition: color 0.2s ease;
    z-index: 2;
}

.custom-select-wrapper .select-chevron-right {
    position: absolute;
    right: 1rem;
    color: #94a3b8;
    font-size: 0.8rem;
    pointer-events: none;
    transition: transform 0.2s ease, color 0.2s ease;
    z-index: 2;
}

.custom-select-control {
    width: 100%;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding: 0.7rem 2.5rem 0.7rem 2.75rem;
    font-size: 0.88rem;
    font-weight: 600;
    font-family: inherit;
    color: #1e293b;
    background-color: #ffffff;
    background-image: linear-gradient(to bottom, #ffffff, #f8fafc);
    border: 2px solid #cbd5e1;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.custom-select-control:hover {
    border-color: #94a3b8;
    background-color: #ffffff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}

.custom-select-control:focus {
    outline: none;
    border-color: #2d6635;
    background-color: #ffffff;
    box-shadow: 0 0 0 4px rgba(45, 102, 53, 0.15);
}

.custom-select-wrapper:focus-within .select-icon-left {
    color: #2d6635;
}

.custom-select-wrapper:focus-within .select-chevron-right {
    color: #2d6635;
    transform: rotate(180deg);
}

.custom-select-control option {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    font-weight: 500;
    color: #1e293b;
    background: #ffffff;
}

.status-pill-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.25rem;
}

/* Tarjeta deseleccionada por defecto: Gris neutro sin color */
.status-pill-card {
    flex: 1;
    min-width: 110px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.65rem 0.85rem;
    border-radius: 8px;
    border: 2px solid #cbd5e1;
    background: #f8fafc;
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    user-select: none;
}
.status-pill-card input[type="radio"] {
    accent-color: currentColor;
    width: 1.1rem;
    height: 1.1rem;
    cursor: pointer;
}

/* Colores aplicados ÚNICAMENTE a la tarjeta que tenga su radio marcado o la clase is-selected */

/* 1. Activo — Verde Suave */
.status-pill-card[data-state="Activo"].is-selected,
.status-pill-card[data-state="Activo"]:has(input[type="radio"]:checked) {
    border-color: #16a34a !important;
    background: #f0fdf4 !important;
    color: #15803d !important;
    box-shadow: 0 2px 8px rgba(22, 163, 74, 0.18);
}

/* 2. Vacaciones — Azul Suave */
.status-pill-card[data-state="Vacaciones"].is-selected,
.status-pill-card[data-state="Vacaciones"]:has(input[type="radio"]:checked),
.status-pill-card[data-state="De Vacaciones"].is-selected,
.status-pill-card[data-state="De Vacaciones"]:has(input[type="radio"]:checked) {
    border-color: #60a5fa !important;
    background: #eff6ff !important;
    color: #1d4ed8 !important;
    box-shadow: 0 2px 8px rgba(96, 165, 250, 0.22);
}

/* 3. Incapacitado — Amarillo / Ámbar Suave */
.status-pill-card[data-state="Incapacitado"].is-selected,
.status-pill-card[data-state="Incapacitado"]:has(input[type="radio"]:checked) {
    border-color: #facc15 !important;
    background: #fefce8 !important;
    color: #a16207 !important;
    box-shadow: 0 2px 8px rgba(250, 204, 21, 0.28);
}

/* 4. Inactivo — Rojo Suave */
.status-pill-card[data-state="Inactivo"].is-selected,
.status-pill-card[data-state="Inactivo"]:has(input[type="radio"]:checked) {
    border-color: #f87171 !important;
    background: #fef2f2 !important;
    color: #b91c1c !important;
    box-shadow: 0 2px 8px rgba(248, 113, 113, 0.22);
}

.field-hint {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.35rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.field-label-required::after {
    content: " *";
    color: #dc2626;
}
</style>

<div class="page-header">
    <h2>
        <i class="fas fa-users-cog"></i>
        <?= $empleado ? 'Editar Empleado' : 'Nuevo Empleado' ?>
    </h2>
    <a href="<?= url('empleado/index') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<div class="form-card-clean">
    <div class="form-card-header">
        <h3>
            <i class="fas fa-id-badge"></i>
            <?= $empleado ? 'Actualizar Datos' : 'Información del Empleado' ?>
        </h3>
        <?php if ($empleado): ?>
            <?php
                $badgeClass = match($currentEstado) {
                    'Activo'       => 'dot-success',
                    'Vacaciones'   => 'dot-info',
                    'Incapacitado' => 'dot-warning',
                    'Inactivo'     => 'dot-danger',
                    default        => 'dot-secondary',
                };
            ?>
            <span class="badge-dot <?= $badgeClass ?>" id="headerStatusBadge">
                <?= e($currentEstado) ?>
            </span>
        <?php endif; ?>
    </div>

    <form action="<?= $action ?>" method="POST" id="formEmpleado">
        <?= csrf_field() ?>
        <?php require dirname(__DIR__) . '/partials/form_errors.php'; ?>

        <div class="form-card-body">
            <div class="form-group">
                <label for="nombre_completo" class="field-label-required">
                    <i class="fas fa-user"></i> Nombre Completo
                </label>
                <input type="text" id="nombre_completo" name="nombre_completo" class="form-control" 
                       value="<?= e($formValues['nombre_completo'] ?? '') ?>" required maxlength="60"
                       data-validate="name"
                       placeholder="Ej. Juan Carlos Pérez"
                       pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\'\-]+">
                <div class="field-hint" id="nombreHint">
                    <i class="fas fa-info-circle"></i> Ingrese solo letras y espacios.
                </div>
            </div>
        
            <div class="form-row">
                <div class="form-group">
                    <label for="dui" class="field-label-required">
                        <i class="fas fa-id-card"></i> DUI
                    </label>
                    <input type="text" id="dui" name="dui" class="form-control" 
                           value="<?= e($formValues['dui'] ?? '') ?>" required maxlength="10"
                           placeholder="00000000-0" data-mask="dui" pattern="\d{8}-\d">
                    <div class="field-hint" id="duiHint">
                        <i class="fas fa-fingerprint"></i> Formato de 9 dígitos con guion automático (00000000-0).
                    </div>
                </div>
                <div class="form-group">
                    <label for="telefono">
                        <i class="fas fa-phone"></i> Teléfono
                    </label>
                    <input type="text" id="telefono" name="telefono" class="form-control" 
                           value="<?= e($formValues['telefono'] ?? '') ?>" maxlength="9"
                           placeholder="7000-0000" data-mask="phone" pattern="[2678]\d{3}-\d{4}">
                    <div class="field-hint" id="telefonoHint">
                        <i class="fas fa-phone-alt"></i> Formato de 8 dígito con guion automático (0000-0000)
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cargo" class="field-label-required">
                        <i class="fas fa-briefcase"></i> Cargo Oficial
                    </label>
                    <div class="custom-select-wrapper">
                        <i class="fas fa-briefcase select-icon-left"></i>
                        <select id="cargo" name="cargo" class="custom-select-control" required>
                            <option value="" disabled <?= empty($currentCargo) ? 'selected' : '' ?>> Seleccione un cargo oficial </option>
                            <?php foreach ($cargosLista as $cg): ?>
                                <option value="<?= e($cg) ?>" <?= ($currentCargo === $cg) ? 'selected' : '' ?>>
                                    <?= e($cg) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down select-chevron-right"></i>
                    </div>
                    <div class="field-hint">
                        <i class="fas fa-building"></i> Seleccione cargo del empleado.
                    </div>
                </div>
                <div class="form-group">
                    <label class="field-label-required">
                        <i class="fas fa-toggle-on"></i> Estado del Empleado
                    </label>
                    <div class="status-pill-group">
                        <?php foreach ($estadosDisponibles as $keyVal => $stInfo): ?>
                            <?php 
                                $isChecked = ($currentEstado === $keyVal);
                                $radioId = 'estado_' . strtolower(str_replace(' ', '_', $keyVal));
                            ?>
                            <label class="status-pill-card <?= $isChecked ? 'is-selected' : '' ?>" data-state="<?= e($keyVal) ?>" for="<?= $radioId ?>">
                                <input type="radio" id="<?= $radioId ?>" name="estado" value="<?= e($keyVal) ?>" <?= $isChecked ? 'checked' : '' ?> required>
                                <i class="fas <?= $stInfo['icon'] ?>"></i> <?= e($stInfo['label']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="field-hint">
                        <i class="fas fa-shield-alt"></i> Disponibilidad operativa del empleado.
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-toolbar">
            <a href="<?= url('empleado/index') ?>" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?= $empleado ? 'Actualizar Empleado' : 'Guardar Empleado' ?>
            </button>
        </div>
    </form>
</div>
