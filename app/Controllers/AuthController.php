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
        $contrasena = $this->input('contrasena', '');

        // Validar campos vacíos
        if (empty($correo) || empty($contrasena)) {
            Session::flash('error', 'Todos los campos son obligatorios.');
            $this->redirect('auth/login');
            return;
        }

        // Intentar autenticación
        $user = $this->usuarioModel->authenticate($correo, $contrasena);

        if (!$user) {
            Session::flash('error', 'Credenciales incorrectas o cuenta inactiva.');
            $this->redirect('auth/login');
            return;
        }

        // Iniciar sesión
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
        if (Session::isAuthenticated()) {
            $this->logActivity('Cierre de sesión', 'usuarios', Session::getUser()['id_usuario']);
        }

        Session::logout();
        $this->redirect('auth/login');
    }
}
