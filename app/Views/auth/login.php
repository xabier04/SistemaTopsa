<div class="login-container">
    <div class="login-left">
        <div class="login-brand">
            <div class="login-logo-card">
                <img src="<?= asset('img/logo.svg') ?>" alt="TOPSA - Topografía y Servicios Anexos" class="login-logo-img">
            </div>
            <h1>SISTEMA DE GESTIÓN</h1>
            <p>Topografía y Servicios Anexos</p>
        </div>
        <div class="login-features">
            <div class="feature-item">
                <i class="fas fa-drafting-compass"></i>
                <span>Proyectos y Trabajos</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-map-marked-alt"></i>
                <span>Inmuebles y Catastro</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-clipboard-check"></i>
                <span>Valuaciones y Avalúos</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-money-bill-wave"></i>
                <span>Abonos y Finanzas</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-folder-open"></i>
                <span>Planos y Documentos</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-user-clock"></i>
                <span>Control de Asistencia</span>
            </div>
        </div>
    </div>

    <div class="login-right">
        <div class="login-form-wrapper">
            <div class="login-header">
                <h2>Iniciar Sesión</h2>
                <p>Ingrese sus credenciales para acceder</p>
            </div>

            <?php $flash = \Core\Session::getFlash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-<?= $flash['type'] === 'error' ? 'exclamation-circle' : 'check-circle' ?>"></i>
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="<?= url('auth/authenticate') ?>" method="POST" class="login-form" id="loginForm">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="correo">
                        <i class="fas fa-envelope"></i>
                        Correo Electrónico
                    </label>
                    <input 
                        type="email" 
                        id="correo" 
                        name="correo" 
                        class="form-control" 
                        placeholder="usuario@topsa.com"
                        required 
                        autocomplete="email"
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="contrasena">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="contrasena" 
                            name="contrasena" 
                            class="form-control" 
                            placeholder="••••••••"
                            required 
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Acceder al Sistema
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; <?= date('Y') ?> TOPSA — Topografía y Servicios Anexos</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Toggle password visibility
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('contrasena');

    if (toggle && password) {
        toggle.addEventListener('click', () => {
            const type = password.type === 'password' ? 'text' : 'password';
            password.type = type;
            toggle.querySelector('i').classList.toggle('fa-eye');
            toggle.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    // Disable submit button on form submit to prevent double-click
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('loginBtn');

    if (form && btn) {
        form.addEventListener('submit', () => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando credenciales...';
        });
    }
});
</script>
