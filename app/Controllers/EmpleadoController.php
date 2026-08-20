<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Empleado;

/**
 * Controlador de Empleados
 */
class EmpleadoController extends Controller
{
    private Empleado $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Empleado();
    }

    public function getRequiredRole(string $action): ?string
    {
        if (in_array($action, ['create', 'store', 'edit', 'update', 'delete'])) {
            return 'Administrador';
        }
        return null;
    }

    public function index(): void
    {
        if (\Core\Router::isAjax()) {
            $search = $_GET['search'] ?? '';
            $empleados = !empty($search)
                ? $this->model->search($search)
                : $this->model->allWithProjectCount();
            $this->json(['success' => true, 'data' => $empleados]);
            return;
        }

        $empleados = $this->model->allWithProjectCount();
        $this->view('empleados/index', [
            'pageTitle'  => 'Gestión de Empleados',
            'pageScript' => 'empleados',
            'empleados'  => $empleados,
        ]);
    }

    public function create(): void
    {
        $this->view('empleados/form', [
            'pageTitle' => 'Nuevo Empleado',
            'empleado'  => null,
            'action'    => url('empleado/store'),
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('empleado/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        $validator = new Validator($data);
        if (!$validator->validate([
            'nombre_completo' => 'required|max:60',
            'dui'             => 'required|dui|unique:empleados',
            'cargo'           => 'required|max:90',
            'telefono'        => 'phone',
            'estado'          => 'required|in:Activo,Inactivo',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect('empleado/create');
            return;
        }

        $this->model->create($data);
        $this->logActivity('Registró empleado: ' . $data['nombre_completo'], 'empleados');
        Session::flash('success', 'Empleado registrado exitosamente.');
        $this->redirect('empleado/index');
    }

    public function edit(int $id = 0): void
    {
        $empleado = $this->model->find($id);
        if (!$empleado) {
            Session::flash('error', 'Empleado no encontrado.');
            $this->redirect('empleado/index');
            return;
        }

        $this->view('empleados/form', [
            'pageTitle' => 'Editar Empleado',
            'empleado'  => $empleado,
            'action'    => url("empleado/update/{$id}"),
        ]);
    }

    public function update(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('empleado/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        $validator = new Validator($data);
        if (!$validator->validate([
            'nombre_completo' => 'required|max:60',
            'dui'             => "required|dui|unique:empleados,{$id}",
            'cargo'           => 'required|max:90',
            'telefono'        => 'phone',
            'estado'          => 'required|in:Activo,Inactivo',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect("empleado/edit/{$id}");
            return;
        }

        $this->model->update($id, $data);
        $this->logActivity('Actualizó empleado: ' . $data['nombre_completo'], 'empleados', $id);
        Session::flash('success', 'Empleado actualizado exitosamente.');
        $this->redirect('empleado/index');
    }

    public function delete(int $id = 0): void
    {
        if (!\Core\Router::isAjax()) {
            $this->redirect('empleado/index');
            return;
        }

        try {
            $empleado = $this->model->find($id);
            $this->model->delete($id);
            $this->logActivity('Eliminó empleado: ' . ($empleado['nombre_completo'] ?? ''), 'empleados', $id);
            $this->json(['success' => true, 'message' => 'Empleado eliminado exitosamente.']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'No se puede eliminar: el empleado tiene registros asociados.'], 400);
        }
    }
}
