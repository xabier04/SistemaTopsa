<div class="login-container"><div class="login-right"><div class="login-form-wrapper">
    <h1>Recuperar contraseña</h1>
    <?php $flash = \Core\Session::getFlash(); if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
    <p>Solicite una clave temporal al administrador. Después de verificar su identidad, podrá entregarle una nueva clave.</p>
    <form action="<?= url('auth/solicitarRecuperacion') ?>" method="post">
        <?= csrf_field() ?>
        <div class="form-group"><label for="correo">Correo de su cuenta</label><input class="form-control" type="email" id="correo" name="correo" required maxlength="50" autocomplete="email"></div>
        <button class="btn btn-primary" type="submit">Solicitar recuperación</button>
    </form>
    <p><a href="<?= url('auth/login') ?>">Volver al inicio de sesión</a></p>
</div></div></div>
