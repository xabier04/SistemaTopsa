<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Usuario;

/**
 * Controlador de Autenticación
 * 
 * Maneja el login, logout y verificación de credenciales.
 */
class AuthController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Mostrar formulario de login
     */
    public function login(): void
    {
        // Si ya está autenticado, redirigir al dashboard
        if (Session::isAuthenticated()) {
            $this->redirect('dashboard/index');
            return;
        }

        $this->view('auth/login', [], 'auth');
    }

    /**
     * Procesar autenticación
     */
    public function authenticate(): void
    {
        if (!$this->isPost()) {
            $this->redirect('auth/login');
            return;
        }

        // Validar CSRF
        if (!$this->validateCsrf()) {
            Session::flash('error', 'Token de seguridad inválido. Intente de nuevo.');
            $this->redirect('auth/login');
            return;
        }

        $correo = $this->input('correo', '');
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        if (($_SESSION['login_blocked_until'] ?? 0) > time()) {
            Session::flash('error', 'Espere unos minutos antes de volver a intentar.');
            $this->redirect('auth/login');
            return;
        }

        // Validar campos vacíos
        if (empty($correo) || empty($contrasena)) {
            Session::flash('error', 'Todos los campos son obligatorios.');
            $this->redirect('auth/login');
            return;
        }

        // Intentar autenticación
        $user = $this->usuarioModel->authenticate($correo, $contrasena);

        if (!$user) {
            $_SESSION['login_failures'] = ($_SESSION['login_failures'] ?? 0) + 1;
            if ($_SESSION['login_failures'] >= 5) {
                $_SESSION['login_blocked_until'] = time() + 300;
                $_SESSION['login_failures'] = 0;
            }
            Session::flash('error', 'Credenciales incorrectas o cuenta inactiva.');
            $this->redirect('auth/login');
            return;
        }

        // Iniciar sesión
        unset($_SESSION['login_failures'], $_SESSION['login_blocked_until']);
        Session::login($user);

        // Registrar en bitácora
        $this->logActivity('Inicio de sesión', 'usuarios', $user['id_usuario']);

        // Redirigir al dashboard
        $this->redirect('dashboard/index');
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('auth/login');
            return;
        }
        if (Session::isAuthenticated()) {
            $this->logActivity('Cierre de sesión', 'usuarios', Session::getUser()['id_usuario']);
        }

        Session::logout();
        $this->redirect('auth/login');
    }

    public function recuperar(): void
    {
        $this->view('auth/recuperar', [], 'auth');
    }

    public function solicitarRecuperacion(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('auth/recuperar');
            return;
        }
        $correo = $this->input('correo', '');
        if (filter_var($correo, FILTER_VALIDATE_EMAIL) && ($_SESSION['recovery_next'] ?? 0) <= time()) {
            $user = $this->usuarioModel->findBy('correo', $correo);
            if ($user && $user['estado_de_cuenta'] === 'Activo') {
                \Core\Database::getInstance()->query('INSERT IGNORE INTO recuperaciones (id_usuario) VALUES (?)', [$user['id_usuario']]);
            }
            $_SESSION['recovery_next'] = time() + 60;
        }
        Session::flash('success', 'Si la cuenta está activa, el administrador recibirá su solicitud. Contáctelo para verificar su identidad y obtener una clave temporal.');
        $this->redirect('auth/recuperar');
    }
}
