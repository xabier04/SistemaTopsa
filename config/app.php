<?php

/**
 * Configuración general de la aplicación
 * Sistema TOPSA — Topografía y Servicios Anexos
 */

return [
    'name'          => 'Sistema TOPSA',
    'version'       => '1.0.0',
    // URL base detectada dinámicamente según el entorno y dominio de acceso
    'base_url' => (function () {
        if (php_sapi_name() === 'cli' || empty($_SERVER['HTTP_HOST'])) {
            return 'http://sistematopsa.test';
        }
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $protocol = $isHttps ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname($scriptName));
        
        if ($dir === '/' || $dir === '\\' || $dir === '.') {
            $basePath = '';
        } else {
            $basePath = preg_replace('#/public$#', '', $dir);
        }
        
        return rtrim($protocol . $host . $basePath, '/');
    })(),
    'timezone'      => 'America/El_Salvador',
    'debug'         => true,

    // Rutas del sistema
    'paths' => [
        'root'      => dirname(__DIR__),
        'app'       => dirname(__DIR__) . '/app',
        'core'      => dirname(__DIR__) . '/core',
        'public'    => dirname(__DIR__) . '/public',
        'storage'   => dirname(__DIR__) . '/storage',
        'uploads'   => dirname(__DIR__) . '/public/uploads',
        'backups'   => dirname(__DIR__) . '/storage/backups',
        'logs'      => dirname(__DIR__) . '/storage/logs',
    ],

    // Configuración de sesiones
    'session' => [
        'name'     => 'TOPSA_SESSION',
        'lifetime' => 7200, // 2 horas
    ],

    // Configuración de uploads
    'uploads' => [
        'max_size'       => 10 * 1024 * 1024, // 10 MB
        'allowed_types'  => ['pdf', 'jpg', 'jpeg', 'png', 'dwg', 'dxf', 'doc', 'docx', 'xls', 'xlsx'],
    ],

    // Roles del sistema
    'roles' => [
        'admin'    => 'Administrador',
        'empleado' => 'Empleado',
    ],

    // Estados de proyectos
    'estados_proyecto' => [
        'en_proceso'  => 'En Proceso',
        'incompleto'  => 'Incompleto',
        'finalizado'  => 'Finalizado',
    ],

    // Estados de empleados
    'estados_empleado' => [
        'activo'   => 'Activo',
        'inactivo' => 'Inactivo',
    ],

    // Estados de cuentas de usuario
    'estados_cuenta' => [
        'activo'      => 'Activo',
        'inactivo'    => 'Inactivo',
        'bloqueado'   => 'Bloqueado',
    ],
];
