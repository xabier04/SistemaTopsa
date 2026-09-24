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
        $empleadosConUsuario = $this->model->empleadosConUsuario();
        $this->view('usuarios/form', [
            'pageTitle'           => 'Nuevo Usuario',
            'usuario'             => null,
            'seleccionEmpleado'   => (int) ($_GET['id_empleado'] ?? 0),
            'empleados'           => $empleados,
            'empleadosConUsuario' => $empleadosConUsuario,
            'action'              => url('usuario/store'),
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
        $rules = [
            'id_empleado'      => 'required|numeric|unique:usuarios',
            'nombre'           => 'required|max:60|unique:usuarios',
            'correo'           => 'required|email|max:50|unique:usuarios',
            'rol'              => 'required|in:Administrador,Empleado',
            'estado_de_cuenta' => 'required|in:Activo,Inactivo',
        ];

        $valid = $validator->validate($rules);
        $errors = $validator->getErrors();

        if (isset($errors['id_empleado']) && str_contains($errors['id_empleado'], 'ya existe')) {
            $errors['id_empleado'] = 'El empleado seleccionado ya cuenta con un usuario en el sistema. Cada empleado solo puede tener un único usuario.';
        }
        if (isset($errors['nombre']) && str_contains($errors['nombre'], 'ya existe')) {
            $errors['nombre'] = 'El nombre de usuario ya está registrado por otra cuenta. Cada nombre de usuario debe ser único.';
        }
        if (isset($errors['correo']) && str_contains($errors['correo'], 'ya existe')) {
            $errors['correo'] = 'El correo electrónico ya está registrado por otra cuenta. Cada correo debe ser único.';
        }
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
        $this->logActivity('Creó usuario con contraseña temporal: ' . $data['nombre'], 'usuarios', $id);
        Session::flash('success', "Usuario creado exitosamente con contraseña temporal.");
        $this->redirect("usuario/credenciales/{$id}");
        return;
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
        $empleadosConUsuario = $this->model->empleadosConUsuario($id);
        $this->view('usuarios/form', [
            'pageTitle'           => 'Editar Usuario',
            'usuario'             => $usuario,
            'empleados'           => $empleados,
            'empleadosConUsuario' => $empleadosConUsuario,
            'action'              => url("usuario/update/{$id}"),
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
            'id_empleado'      => "required|numeric|unique:usuarios,{$id}",
            'nombre'           => "required|max:60|unique:usuarios,{$id}",
            'correo'           => "required|email|max:50|unique:usuarios,{$id}",
            'rol'              => 'required|in:Administrador,Empleado',
            'estado_de_cuenta' => 'required|in:Activo,Inactivo',
        ];

        $valid = $validator->validate($rules);
        $errors = $validator->getErrors();

        if (isset($errors['id_empleado']) && str_contains($errors['id_empleado'], 'ya existe')) {
            $errors['id_empleado'] = 'El empleado seleccionado ya cuenta con un usuario en el sistema. Cada empleado solo puede tener un único usuario.';
        }
        if (isset($errors['nombre']) && str_contains($errors['nombre'], 'ya existe')) {
            $errors['nombre'] = 'El nombre de usuario ya está registrado por otra cuenta. Cada nombre de usuario debe ser único.';
        }
        if (isset($errors['correo']) && str_contains($errors['correo'], 'ya existe')) {
            $errors['correo'] = 'El correo electrónico ya está registrado por otra cuenta. Cada correo debe ser único.';
        }
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

    private function rememberCredential(int $id, string $password): void
    {
        $_SESSION['credential'] = [
            'id'       => $id, 
            'password' => $password, 
            'expires'  => time() + 600
        ];
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
            'pageTitle' => 'Credenciales de la cuenta', 
            'usuario'   => $usuario,
            'temporal'  => $credential['password'], 
            'telefono'  => $empleado['telefono'] ?? '',
        ]);
    }

    public function regenerar(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf() || !($usuario = $this->model->find($id))) {
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
        if (strlen($nueva) < 8 || strlen($nueva) > 72) {
            $errors['nueva'] = 'La contraseña debe tener al menos 8 caracteres.';
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

    /**
     * Eliminar usuario
     */
    public function delete(int $id = 0): void
    {
        $isAjax = \Core\Router::isAjax();
        if (!$this->isPost() || !$this->validateCsrf()) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Petición no válida.'], 400);
            } else {
                $this->redirect('usuario/index');
            }
            return;
        }

        $usuario = $this->model->find($id);
        if (!$usuario) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
            } else {
                Session::flash('error', 'Usuario no encontrado.');
                $this->redirect('usuario/index');
            }
            return;
        }

        // Evitar que el usuario logueado elimine su propia cuenta
        $currentUser = Session::getUser();
        if ($currentUser && (int) ($currentUser['id_usuario'] ?? 0) === $id) {
            $msg = 'No puede eliminar su propio usuario en sesión activa.';
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $msg], 400);
            } else {
                Session::flash('error', $msg);
                $this->redirect('usuario/index');
            }
            return;
        }

        $this->model->delete($id);
        $this->logActivity('Eliminó usuario: ' . $usuario['nombre'], 'usuarios', $id);

        if ($isAjax) {
            $this->json([
                'success' => true,
                'message' => "Usuario \"{$usuario['nombre']}\" eliminado exitosamente."
            ]);
        } else {
            Session::flash('success', "Usuario \"{$usuario['nombre']}\" eliminado exitosamente.");
            $this->redirect('usuario/index');
        }
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
