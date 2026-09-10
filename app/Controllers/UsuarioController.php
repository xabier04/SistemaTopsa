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
        return in_array($action, ['cambiarClave', 'guardarClave'], true) ? null : 'Administrador';
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
        $empleados = (new Empleado())->all('nombre_completo', 'ASC');
        $this->view('usuarios/form', [
            'pageTitle' => 'Nuevo Usuario',
            'usuario'   => null,
            'seleccionEmpleado' => (int) ($_GET['id_empleado'] ?? 0),
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

        $data = array_intersect_key($this->allInput(), array_flip(['id_empleado', 'nombre', 'correo', 'rol', 'estado_de_cuenta']));

        $validator = new Validator($data);
        $valid = $validator->validate([
            'id_empleado'      => 'required|numeric',
            'nombre'           => 'required|max:60',
            'correo'           => 'required|email|max:50|unique:usuarios',
            'rol'              => 'required|in:Administrador,Empleado',
            'estado_de_cuenta' => 'required|in:Activo,Inactivo,Bloqueado',
        ]);
        $errors = $validator->getErrors();
        if (!(new Empleado())->find((int) ($data['id_empleado'] ?? 0))) {
            $errors['id_empleado'] = 'Seleccione un empleado válido.';
        }
        if (!$valid || $errors) {
            \Core\FormState::save(url('usuario/store'), $data, $errors);
            Session::flash('error', reset($errors));
            $this->redirect('usuario/create');
            return;
        }

        $temporal = Usuario::temporaryPassword();
        $data['contrasena'] = $temporal;
        $data['requiere_cambio_contrasena'] = 1;
        $id = $this->model->createUser($data);
        $this->rememberCredential($id, $temporal);
        $this->logActivity('Creó usuario: ' . $data['nombre'], 'usuarios');
        Session::flash('success', 'Usuario creado con contraseña temporal.');
        $this->redirect("usuario/credenciales/{$id}");
    }

    public function edit(int $id = 0): void
    {
        $usuario = $this->model->find($id);
        if (!$usuario) {
            Session::flash('error', 'Usuario no encontrado.');
            $this->redirect('usuario/index');
            return;
        }

        $empleados = (new Empleado())->all('nombre_completo', 'ASC');
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

        $data = array_intersect_key($this->allInput(), array_flip(['id_empleado', 'nombre', 'correo', 'rol', 'estado_de_cuenta']));

        if (!$this->model->find($id)) {
            $this->redirect('usuario/index');
            return;
        }

        $validator = new Validator($data);
        $rules = [
            'id_empleado' => 'required|numeric',
            'nombre'           => 'required|max:60',
            'correo'           => "required|email|max:50|unique:usuarios,{$id}",
            'rol'              => 'required|in:Administrador,Empleado',
            'estado_de_cuenta' => 'required|in:Activo,Inactivo,Bloqueado',
        ];

        $valid = $validator->validate($rules);
        $errors = $validator->getErrors();
        if (!(new Empleado())->find((int) ($data['id_empleado'] ?? 0))) {
            $errors['id_empleado'] = 'Seleccione un empleado válido.';
        }
        if (!$valid || $errors) {
            \Core\FormState::save(url("usuario/update/{$id}"), $data, $errors);
            Session::flash('error', reset($errors));
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
    private function rememberCredential(int $id, string $password): void
    {
        $_SESSION['credential'] = ['id' => $id, 'password' => $password, 'expires' => time() + 600];
    }

    public function credenciales(int $id = 0): void
    {
        header('Cache-Control: no-store, private');
        header('Referrer-Policy: no-referrer');
        $credential = $_SESSION['credential'] ?? null;
        unset($_SESSION['credential']);
        $usuario = $this->model->find($id);
        if (!$usuario || !$credential || $credential['id'] !== $id || $credential['expires'] < time()
            || !password_verify($credential['password'], $usuario['contrasena'])) {
            Session::flash('error', 'La contraseña solo se muestra una vez. Puede generar otra desde Editar Usuario.');
            $this->redirect('usuario/index');
            return;
        }
        $empleado = (new Empleado())->find((int) $usuario['id_empleado']);
        $this->view('usuarios/credenciales', [
            'pageTitle' => 'Contraseña temporal', 'usuario' => $usuario,
            'temporal' => $credential['password'], 'telefono' => $empleado['telefono'] ?? '',
        ]);
    }

    public function regenerar(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf() || !$this->model->find($id)) {
            $this->redirect('usuario/index');
            return;
        }
        $temporal = Usuario::temporaryPassword();
        $this->model->update($id, [
            'contrasena' => password_hash($temporal, PASSWORD_BCRYPT),
            'requiere_cambio_contrasena' => 1,
        ]);
        $this->rememberCredential($id, $temporal);
        $this->logActivity('Generó una nueva contraseña temporal', 'usuarios', $id);
        $this->redirect("usuario/credenciales/{$id}");
    }

    public function cambiarClave(): void
    {
        header('Cache-Control: no-store, private');
        $this->view('usuarios/cambiar_clave', ['pageTitle' => 'Cambiar contraseña']);
    }

    public function guardarClave(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('usuario/cambiarClave');
            return;
        }
        // Nunca conservar contraseñas en el estado del formulario ni en bitácora.
        $correo = trim((string) ($_POST['correo'] ?? ''));
        $actual = (string) ($_POST['actual'] ?? '');
        $nueva = (string) ($_POST['nueva'] ?? '');
        $confirmacion = (string) ($_POST['confirmacion'] ?? '');
        $errors = [];
        if (strlen($nueva) < 12 || strlen($nueva) > 72) {
            $errors['nueva'] = 'Use al menos 12 caracteres; si la contraseña es demasiado larga, reduzca su longitud.';
        } elseif ($nueva !== $confirmacion) {
            $errors['confirmacion'] = 'La confirmación no coincide con la nueva contraseña.';
        } elseif ($nueva === $actual) {
            $errors['nueva'] = 'Elija una contraseña diferente a la actual.';
        } elseif (!$this->model->changeOwnPassword($correo, $actual, $nueva)) {
            $errors['actual'] = 'El correo o la contraseña actual no son válidos, o la cuenta no está activa.';
        }
        if ($errors) {
            \Core\FormState::save(url('usuario/guardarClave'), ['correo' => $correo], $errors);
            Session::flash('error', reset($errors));
        } else {
            unset($_SESSION['credential']);
            Session::flash('success', 'Contraseña cambiada correctamente. Ya puede descartar la contraseña anterior.');
        }
        $this->redirect('usuario/cambiarClave');
    }

    public function toggleEstado(int $id = 0): void
    {
        if (!\Core\Router::isAjax() || !$this->isPost() || !$this->validateCsrf()) {
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
