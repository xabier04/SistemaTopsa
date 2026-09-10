<?php
$formState = \Core\FormState::take(url('usuario/guardarClave'));
$formErrors = $formState['errors'] ?? [];
?>
<div class="page-header"><h2>Cambiar contraseña</h2></div>
<div class="form-card-clean">
    <form action="<?= url('usuario/guardarClave') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-card-body">
            <p>Ingrese su correo y contraseña actual o temporal para establecer una nueva.</p>
            <?php require dirname(__DIR__) . '/partials/form_errors.php'; ?>
            <div class="form-group">
                <label for="correo">Correo de la cuenta</label>
                <input class="form-control" id="correo" type="email" name="correo" value="<?= e($formState['values']['correo'] ?? '') ?>" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="actual">Contraseña actual o temporal</label>
                <input class="form-control" id="actual" type="password" name="actual" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label for="nueva">Nueva contraseña</label>
                <input class="form-control" id="nueva" type="password" name="nueva" required minlength="12" maxlength="72" autocomplete="new-password">
                <small class="field-hint">Use una frase de al menos 12 caracteres.</small>
            </div>
            <div class="form-group">
                <label for="confirmacion">Confirmar nueva contraseña</label>
                <input class="form-control" id="confirmacion" type="password" name="confirmacion" required minlength="12" maxlength="72" autocomplete="new-password">
            </div>
        </div>
        <div class="form-actions-toolbar"><button class="btn btn-primary" type="submit">Cambiar contraseña</button></div>
    </form>
</div>
