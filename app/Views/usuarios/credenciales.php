<?php
$mensaje = "Hola {$usuario['nombre']}, se ha preparado tu cuenta de TOPSA.\nCorreo: {$usuario['correo']}\nContraseña temporal: {$temporal}\nCambia tu contraseña aquí: " . url('usuario/cambiarClave');
$numero = preg_replace('/\D/', '', $telefono);
if (strlen($numero) === 8) $numero = '503' . $numero;
$whatsapp = strlen($numero) === 11 && str_starts_with($numero, '503');
?>
<div class="page-header"><h2>Contraseña temporal creada</h2></div>
<div class="form-card-clean">
    <div class="form-card-body">
        <p>Cuenta: <strong><?= e($usuario['correo']) ?></strong></p>
        <p>Esta contraseña se muestra una sola vez. Compártala antes de salir de esta pantalla.</p>
        <div class="form-group">
            <label for="claveTemporal">Contraseña temporal</label>
            <input id="claveTemporal" class="form-control" value="<?= e($temporal) ?>" readonly autocomplete="off">
        </div>
        <div class="form-group">
            <label for="mensajeCredenciales">Mensaje para el empleado</label>
            <textarea id="mensajeCredenciales" class="form-control" rows="6" readonly><?= e($mensaje) ?></textarea>
        </div>
        <p>Los botones preparan el mensaje; confirme el envío en su aplicación de correo o WhatsApp.</p>
        <p>Para que el empleado abra el enlace desde otro dispositivo, el sistema debe tener una dirección accesible desde ese dispositivo.</p>
        <div class="form-actions-toolbar">
            <a class="btn btn-outline" href="mailto:<?= e($usuario['correo']) ?>?subject=<?= rawurlencode('Tu cuenta TOPSA') ?>&amp;body=<?= rawurlencode($mensaje) ?>">Preparar correo</a>
            <?php if ($whatsapp): ?>
            <a class="btn btn-outline" target="_blank" rel="noopener noreferrer" href="https://wa.me/<?= e($numero) ?>?text=<?= rawurlencode($mensaje) ?>">Preparar WhatsApp</a>
            <?php else: ?>
            <span class="text-muted">Agregue un teléfono al empleado para compartir por WhatsApp.</span>
            <?php endif; ?>
            <a class="btn btn-primary" href="<?= url('usuario/index') ?>">Volver a usuarios</a>
        </div>
    </div>
</div>
