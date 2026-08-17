<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión — Oficina de Topografía y Servicios Anexos TOPSA">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= e($appName) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/logo-icon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
</head>
<body>
    <div class="app-layout">
        <!-- ─── Sidebar ──────────────────────────────────────────── -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?= url('dashboard/index') ?>" class="sidebar-brand">
                    <img src="<?= asset('img/logo.svg') ?>" alt="TOPSA Logo" class="sidebar-brand-img">
                    <img src="<?= asset('img/logo-icon.svg') ?>" alt="TOPSA Icon" class="sidebar-brand-icon">
                </a>
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Colapsar menú">
                    <i class="fas fa-angles-left"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">Principal</span>
                    <a href="<?= url('dashboard/index') ?>" class="nav-link <?= isActiveRoute('dashboard') ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">Gestión</span>
                    <a href="<?= url('cliente/index') ?>" class="nav-link <?= isActiveRoute('cliente') ?>">
                        <i class="fas fa-user-tie"></i>
                        <span>Clientes</span>
                    </a>
                    <a href="<?= url('empleado/index') ?>" class="nav-link <?= isActiveRoute('empleado') ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>Empleados</span>
                    </a>
                    <a href="<?= url('inmueble/index') ?>" class="nav-link <?= isActiveRoute('inmueble') ?>">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Inmuebles</span>
                    </a>
                    <a href="<?= url('proyecto/index') ?>" class="nav-link <?= isActiveRoute('proyecto') ?>">
                        <i class="fas fa-drafting-compass"></i>
                        <span>Proyectos y Trabajos</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">Finanzas</span>
                    <a href="<?= url('transaccion/index') ?>" class="nav-link <?= isActiveRoute('transaccion') ?>">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Transacciones / Abonos</span>
                    </a>
                    <a href="<?= url('valuo/index') ?>" class="nav-link <?= isActiveRoute('valuo') ?>">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Valuaciones (Avalúos)</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">Operaciones</span>
                    <a href="<?= url('documento/index') ?>" class="nav-link <?= isActiveRoute('documento') ?>">
                        <i class="fas fa-folder-open"></i>
                        <span>Planos y Documentos</span>
                    </a>
                    <a href="<?= url('asistencia/index') ?>" class="nav-link <?= isActiveRoute('asistencia') ?>">
                        <i class="fas fa-user-clock"></i>
                        <span>Control Asistencia</span>
                    </a>
                </div>

                <?php if (\Core\Session::isAdmin()): ?>
                <div class="nav-section">
                    <span class="nav-section-title">Administración</span>
                    <a href="<?= url('usuario/index') ?>" class="nav-link <?= isActiveRoute('usuario') ?>">
                        <i class="fas fa-user-shield"></i>
                        <span>Usuarios y Roles</span>
                    </a>
                    <a href="<?= url('backup/index') ?>" class="nav-link <?= isActiveRoute('backup') ?>">
                        <i class="fas fa-database"></i>
                        <span>Copias de Seguridad</span>
                    </a>
                </div>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= initials($currentUser['nombre'] ?? 'U') ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?= e($currentUser['nombre'] ?? '') ?></span>
                        <span class="user-role"><?= e($currentUser['rol'] ?? '') ?></span>
                    </div>
                </div>
                <a href="<?= url('auth/logout') ?>" class="btn-logout" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </aside>

        <!-- ─── Contenido Principal ──────────────────────────────── -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="topbar">
                <button class="topbar-toggle" id="mobileMenuToggle" aria-label="Abrir menú">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-title">
                    <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
                </div>
                <div class="topbar-actions">
                    <span class="topbar-date">
                        <i class="fas fa-calendar-check"></i>
                        <?= date('d/m/Y') ?>
                    </span>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php $flash = \Core\Session::getFlash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>" id="flashAlert">
                    <i class="fas fa-<?= match($flash['type']) {
                        'success' => 'check-circle',
                        'error' => 'exclamation-circle',
                        'warning' => 'exclamation-triangle',
                        default => 'info-circle'
                    } ?>"></i>
                    <span><?= e($flash['message']) ?></span>
                    <button class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="page-content">
                <?= $content ?>
            </div>
        </main>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Modal Container -->
    <div class="modal-backdrop" id="modalBackdrop" style="display:none;">
        <div class="modal" id="globalModal">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle"></h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer" id="modalFooter"></div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if (isset($pageScript)): ?>
        <script src="<?= asset("js/modules/{$pageScript}.js") ?>"></script>
    <?php endif; ?>
</body>
</html>
