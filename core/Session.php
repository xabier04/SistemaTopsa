<?php

namespace Core;

/**
 * Clase Session — Gestión de Sesiones Seguras
 * 
 * Manejo centralizado de sesiones con configuración restrictiva,
 * tokens CSRF, y almacenamiento de datos del usuario autenticado.
 */
class Session
{
    /**
     * Iniciar la sesión con configuración segura
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $config = require dirname(__DIR__) . '/config/app.php';

        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);

        session_set_cookie_params([
            'lifetime' => $config['session']['lifetime'],
            'path'     => '/',
            'domain'   => '',
            'secure'   => false, // Cambiar a true en producción con HTTPS
            'httponly'  => true,
            'samesite'  => 'Lax',
        ]);

        session_name($config['session']['name']);
        session_start();
    }

    /**
     * Iniciar sesión de usuario
     *
     * @param array $user Datos del usuario autenticado
     */
    public static function login(array $user): void
    {
        // Regenerar el ID de sesión para prevenir session fixation
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id_usuario'      => $user['id_usuario'],
            'id_empleado'     => $user['id_empleado'],
            'nombre'          => $user['nombre'],
            'correo'          => $user['correo'],
            'rol'             => $user['rol'],
            'estado_de_cuenta' => $user['estado_de_cuenta'],
        ];

        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Cerrar sesión
     */
    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id_usuario']);
    }

    /**
     * Obtener los datos del usuario autenticado
     *
     * @return array|null
     */
    public static function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     *
     * @param string $role Rol a verificar
     * @return bool
     */
    public static function hasRole(string $role): bool
    {
        return isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] === $role;
    }

    /**
     * Verificar si el usuario es administrador
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('Administrador');
    }

    /**
     * Generar token CSRF
     *
     * @return string
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validar token CSRF
     *
     * @param string $token Token recibido del formulario
     * @return bool
     */
    public static function validateCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Establecer un mensaje flash (se muestra una sola vez)
     *
     * @param string $type    Tipo de mensaje ('success', 'error', 'warning', 'info')
     * @param string $message Mensaje a mostrar
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Obtener y limpiar el mensaje flash
     *
     * @return array|null
     */
    public static function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
