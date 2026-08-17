<?php

/**
 * Front Controller — Punto único de entrada
 * Sistema TOPSA — Oficina de Ingeniería Civil
 * 
 * Todas las peticiones HTTP pasan por este archivo.
 */

// ─── Configuración de errores ────────────────────────────────────────────
$config = require dirname(__DIR__) . '/config/app.php';

if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ─── Zona horaria ────────────────────────────────────────────────────────
date_default_timezone_set($config['timezone']);

// ─── Autoloader de clases (PSR-4 simplificado) ──────────────────────────
spl_autoload_register(function (string $class) {
    $prefixes = [
        'Core\\'            => dirname(__DIR__) . '/core/',
        'App\\Controllers\\' => dirname(__DIR__) . '/app/Controllers/',
        'App\\Models\\'      => dirname(__DIR__) . '/app/Models/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// ─── Cargar funciones utilitarias ────────────────────────────────────────
require_once dirname(__DIR__) . '/core/helpers.php';

// ─── Iniciar sesión segura ───────────────────────────────────────────────
\Core\Session::start();

// ─── Despachar la petición ───────────────────────────────────────────────
$router = new \Core\Router();
$router->dispatch();
