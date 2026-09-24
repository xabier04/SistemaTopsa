<?php
$mensaje = "Hola {$usuario['nombre']}, se ha preparado tu cuenta de acceso a Sistema TOPSA.\n\n"
         . "Tus datos de acceso son:\n"
         . "• Usuario: {$usuario['nombre']}\n"
         . "• Correo: {$usuario['correo']}\n"
         . "• Contraseña temporal: {$temporal}\n\n"
         . "Por seguridad, puedes cambiar tu contraseña aquí:\n" . url('usuario/cambiarClave');

$numero = preg_replace('/\D/', '', $telefono ?? '');
if (strlen($numero) === 8) $numero = '503' . $numero;
$whatsapp = strlen($numero) === 11 && str_starts_with($numero, '503');

$gmailWebUrl = "https://mail.google.com/mail/?view=cm&fs=1"
             . "&to=" . rawurlencode($usuario['correo'])
             . "&su=" . rawurlencode("Tus credenciales de acceso — Sistema TOPSA")
             . "&body=" . rawurlencode($mensaje);
?>

<div class="page-header">
    <h2><i class="fas fa-key"></i> Credenciales de la Cuenta</h2>
    <a href="<?= url('usuario/index') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Volver a Usuarios</a>
</div>

<div class="form-card-clean">
    <div class="form-card-body">
        
        <div style="margin-bottom: 1.5rem; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 14px 18px; border-radius: 8px; display: flex; align-items: flex-start; gap: 12px;">
            <i class="fas fa-paper-plane" style="font-size: 1.3rem; color: #2563eb; margin-top: 2px;"></i>
            <div>
                <strong style="font-size: 0.95rem;">Compartir credenciales de acceso</strong>
                <div style="font-size: 0.85rem; margin-top: 3px; color: #374151;">
                    Utilice los botones a continuación para enviar las credenciales directamente por <strong>Gmail</strong> o por <strong>WhatsApp</strong> al empleado.
                </div>
            </div>
        </div>

        <p style="font-size: 0.95rem; margin-bottom: 0.5rem;">
            Cuenta: <strong><?= e($usuario['correo']) ?></strong> &nbsp;·&nbsp; Usuario: <strong><?= e($usuario['nombre']) ?></strong>
        </p>
        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 1.25rem;">
            <i class="fas fa-shield-alt"></i> Esta contraseña solo se muestra una vez por motivos de seguridad. Compártala antes de salir de esta pantalla.
        </p>

        <div class="form-group">
            <label for="claveTemporal"><i class="fas fa-lock"></i> Contraseña temporal asignada</label>
            <div style="display: flex; gap: 8px; max-width: 500px;">
                <input id="claveTemporal" class="form-control" value="<?= e($temporal) ?>" readonly autocomplete="off" 
                       style="font-family: monospace; font-size: 1.15rem; font-weight: 700; color: #15803d; letter-spacing: 1px; background: #f8fafc;">
                <button type="button" class="btn btn-outline" id="btnCopiarClave" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="mensajeCredenciales"><i class="fas fa-comment-alt"></i> Mensaje preparado para el usuario</label>
            <textarea id="mensajeCredenciales" class="form-control" rows="6" readonly style="font-size: 0.875rem; font-family: monospace; background: #f8fafc;"><?= e($mensaje) ?></textarea>
        </div>

        <div class="form-actions-toolbar" style="flex-wrap: wrap; gap: 10px; padding-top: 1.25rem; border-top: 1px solid var(--gray-200, #e5e7eb);">
            <!-- Botón directo de Gmail Web -->
            <a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="<?= $gmailWebUrl ?>" 
               style="background: #ea4335; border-color: #ea4335; color: #ffffff; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                <i class="fab fa-google"></i> Abrir y Enviar por Gmail
            </a>

            <!-- Botón WhatsApp -->
            <?php if ($whatsapp): ?>
                <a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="https://wa.me/<?= e($numero) ?>?text=<?= rawurlencode($mensaje) ?>" 
                   style="background: #25d366; border-color: #25d366; color: #ffffff; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                    <i class="fab fa-whatsapp"></i> Enviar por WhatsApp
                </a>
            <?php endif; ?>

            <a class="btn btn-outline" href="<?= url('usuario/index') ?>" style="margin-left: auto;">
                <i class="fas fa-check"></i> Finalizar y volver
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('btnCopiarClave')?.addEventListener('click', () => {
    const clave = document.getElementById('claveTemporal')?.value || '';
    if (navigator.clipboard && clave) {
        navigator.clipboard.writeText(clave).then(() => {
            if (typeof Toast !== 'undefined' && Toast.success) {
                Toast.success('Contraseña copiada al portapapeles');
            } else {
                alert('Contraseña copiada al portapapeles');
            }
        });
    }
});
</script>
