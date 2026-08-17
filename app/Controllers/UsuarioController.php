<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Usuario;
use App\Models\Empleado;

/**
 * Controlador de Usuarios (solo Administrador)
 */
class UsuarioController extends Controller
{
    private Usuario $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Usuario();
    }

    public function getRequiredRole(string $action): ?string
    {
        return 'Administrador';
    }

    public function index(): void
    {
        $usuarios = $this->model->allWithEmpleado();
        $this->view('usuarios/index', [
            'pageTitle'  => 'Gestión de Usuarios',
            'pageScript' => 'usuarios',
            'usuarios'   => $usuarios,
        ]);
    }

    public function create(): void
    {
        $empleados = (new Empleado())->activos();
        $this->view('usuarios/form', [
            'pageTitle' => 'Nuevo Usuario',
            'usuario'   => null,
            'empleados' => $empleados,
            'action'    => url('usuario/store'),
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('usuario/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_empleado'      => 'required|numeric',
            'nombre'           => 'required|max:60',
            'correo'           => 'required|email|max:50|unique:usuarios',
            'contrasena'       => 'required|min:6',
            'rol'              => 'required|in:Administrador,Empleado',
            'estado_de_cuenta' => 'required|in:Activo,Inactivo,Bloqueado',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect('usuario/create');
            return;
        }

        $this->model->createUser($data);
        $this->logActivity('Creó usuario: ' . $data['nombre'], 'usuarios');
        Session::flash('success', 'Usuario creado exitosamente.');
        $this->redirect('usuario/index');
    }

    public function edit(int $id = 0): void
    {
        $usuario = $this->model->find($id);
        if (!$usuario) {
            Session::flash('error', 'Usuario no encontrado.');
            $this->redirect('usuario/index');
            return;
        }

        $empleados = (new Empleado())->activos();
        $this->view('usuarios/form', [
            'pageTitle' => 'Editar Usuario',
            'usuario'   => $usuario,
            'empleados' => $empleados,
            'action'    => url("usuario/update/{$id}"),
        ]);
    }

    public function update(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('usuario/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        // Si la contraseña viene vacía, no actualizarla
        if (empty($data['contrasena'])) {
            unset($data['contrasena']);
        } else {
            $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_BCRYPT);
        }

        $validator = new Validator($data);
        $rules = [
            'nombre'           => 'required|max:60',
            'correo'           => "required|email|max:50|unique:usuarios,{$id}",
            'rol'              => 'required|in:Administrador,Empleado',
            'estado_de_cuenta' => 'required|in:Activo,Inactivo,Bloqueado',
        ];

        if (!$validator->validate($rules)) {
            Session::flash('error', $validator->firstError());
            $this->redirect("usuario/edit/{$id}");
            return;
        }

        $this->model->update($id, $data);
        $this->logActivity('Actualizó usuario: ' . $data['nombre'], 'usuarios', $id);
        Session::flash('success', 'Usuario actualizado exitosamente.');
        $this->redirect('usuario/index');
    }

    /**
     * Toggle estado de cuenta (AJAX)
     */
    public function toggleEstado(int $id = 0): void
    {
        if (!\Core\Router::isAjax()) {
            $this->redirect('usuario/index');
            return;
        }

        $usuario = $this->model->find($id);
        if (!$usuario) {
            $this->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
            return;
        }

        $newEstado = $usuario['estado_de_cuenta'] === 'Activo' ? 'Inactivo' : 'Activo';
        $this->model->update($id, ['estado_de_cuenta' => $newEstado]);
        $this->logActivity("Cambió estado de usuario a {$newEstado}", 'usuarios', $id);
        $this->json(['success' => true, 'message' => "Estado cambiado a {$newEstado}.", 'estado' => $newEstado]);
    }
}
