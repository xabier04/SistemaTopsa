<?php

namespace Core;

/**
 * Clase Router — Enrutador de URLs
 * 
 * Mapea URLs a controladores y acciones siguiendo la convención:
 * /{controlador}/{accion}/{param1}/{param2}/...
 */
class Router
{
    private string $controllerNamespace = 'App\\Controllers\\';
    private string $defaultController = 'AuthController';
    private string $defaultAction = 'login';

    /**
     * Despachar la petición HTTP al controlador correspondiente
     */
    public function dispatch(): void
    {
        $url = $this->parseUrl();

        if (empty($url)) {
            // Sin login — ir directo al dashboard
            $controllerName = 'DashboardController';
            $action = 'index';
        } else {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $action         = $url[1] ?? 'index';
        }

        $params = !empty($url) ? array_slice($url, 2) : [];

        $controllerClass = $this->controllerNamespace . $controllerName;

        // Verificar si el controlador existe
        if (!class_exists($controllerClass)) {
            $this->handleError(404, "Página no encontrada");
            return;
        }

        $controller = new $controllerClass();

        // Verificar si la acción existe
        if (!method_exists($controller, $action)) {
            $this->handleError(404, "Acción no encontrada");
            return;
        }

        // Verificar autenticación (DESACTIVADO para sprint backlog)
        // if ($controllerName !== 'AuthController') {
        //     if (!Session::isAuthenticated()) {
        //         $this->redirect('auth/login');
        //         return;
        //     }
        //     if (method_exists($controller, 'getRequiredRole')) {
        //         $requiredRole = $controller->getRequiredRole($action);
        //         if ($requiredRole && !Session::hasRole($requiredRole)) {
        //             $this->handleError(403, "No tienes permisos para acceder a esta sección");
        //             return;
        //         }
        //     }
        // }

        // Ejecutar la acción del controlador
        call_user_func_array([$controller, $action], $params);
    }

    /**
     * Parsear la URL de la petición
     *
     * @return array Segmentos de la URL
     */
    private function parseUrl(): array
    {
        $url = $_GET['url'] ?? '';
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if (empty($url)) {
            return [];
        }

        return explode('/', $url);
    }

    /**
     * Verificar si la petición es AJAX
     */
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Redireccionar a una URL
     */
    private function redirect(string $url): void
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        header('Location: ' . $config['base_url'] . '/' . $url);
        exit;
    }

    /**
     * Manejar errores HTTP
     */
    private function handleError(int $code, string $message): void
    {
        http_response_code($code);

        if (self::isAjax()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => true, 'message' => $message]);
        } else {
            echo "<h1>Error {$code}</h1><p>{$message}</p>";
            echo '<p><a href="' . (require dirname(__DIR__) . '/config/app.php')['base_url'] . '">Volver al inicio</a></p>';
        }
        exit;
    }
}
