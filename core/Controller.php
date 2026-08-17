<?php

namespace Core;

/**
 * Clase Controller — Controlador Base Abstracto
 * 
 * Proporciona métodos comunes para todos los controladores:
 * renderizado de vistas, respuestas JSON, redirecciones, y
 * registro en bitácora.
 */
abstract class Controller
{
    protected array $appConfig;

    public function __construct()
    {
        $this->appConfig = require dirname(__DIR__) . '/config/app.php';
    }

    /**
     * Renderizar una vista con un layout
     *
     * @param string $view   Ruta relativa de la vista (ej: 'clientes/index')
     * @param array  $data   Datos para pasar a la vista
     * @param string $layout Layout a utilizar ('main' o 'auth')
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // Extraer variables para que estén disponibles en la vista
        extract($data);

        // Variables globales disponibles en todas las vistas
        $appConfig = $this->appConfig;
        $baseUrl   = $this->appConfig['base_url'];
        $appName   = $this->appConfig['name'];
        $currentUser = Session::getUser();

        // Capturar el contenido de la vista
        $viewPath = dirname(__DIR__) . "/app/Views/{$view}.php";
        if (!file_exists($viewPath)) {
            die("Vista no encontrada: {$view}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Renderizar dentro del layout
        $layoutPath = dirname(__DIR__) . "/app/Views/layouts/{$layout}.php";
        if (!file_exists($layoutPath)) {
            die("Layout no encontrado: {$layout}");
        }

        require $layoutPath;
    }

    /**
     * Responder con JSON (para peticiones AJAX)
     *
     * @param mixed $data   Datos a convertir en JSON
     * @param int   $code   Código HTTP de respuesta
     */
    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redireccionar a una URL del sistema
     *
     * @param string $url Ruta relativa (ej: 'clientes/index')
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $this->appConfig['base_url'] . '/' . $url);
        exit;
    }

    /**
     * Obtener datos del formulario POST de forma segura
     *
     * @param string $key     Clave del campo
     * @param mixed  $default Valor por defecto
     * @return mixed
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    /**
     * Obtener todos los datos POST sanitizados
     *
     * @return array
     */
    protected function allInput(): array
    {
        $data = [];
        foreach ($_POST as $key => $value) {
            $data[$key] = is_string($value) ? trim($value) : $value;
        }
        return $data;
    }

    /**
     * Verificar que la petición sea POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Verificar token CSRF
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['_csrf_token'] ?? '';
        return Session::validateCsrfToken($token);
    }

    /**
     * Registrar una acción en la bitácora
     *
     * @param string   $accion         Descripción de la acción
     * @param string   $tablaAfectada  Tabla que fue afectada
     * @param int|null $idRegistro     ID del registro afectado
     */
    protected function logActivity(string $accion, string $tablaAfectada = '', ?int $idRegistro = null): void
    {
        $db = Database::getInstance();
        $user = Session::getUser();

        $db->query(
            "INSERT INTO bitacora (id_usuario, accion, tabla_afectada, id_registro, ip_address) 
             VALUES (:id_usuario, :accion, :tabla, :id_registro, :ip)",
            [
                ':id_usuario'  => $user['id_usuario'] ?? 0,
                ':accion'      => $accion,
                ':tabla'       => $tablaAfectada,
                ':id_registro' => $idRegistro,
                ':ip'          => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ]
        );
    }

    /**
     * Definir el rol requerido para acciones específicas
     * Los controladores hijos pueden sobrescribir este método
     *
     * @param string $action Nombre de la acción
     * @return string|null   Rol requerido o null si no hay restricción
     */
    public function getRequiredRole(string $action): ?string
    {
        return null;
    }
}
