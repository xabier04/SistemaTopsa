<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Proyecto;
use App\Models\Cliente;
use App\Models\Inmueble;

/**
 * Controlador de Proyectos
 */
class ProyectoController extends Controller
{
    private Proyecto $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Proyecto();
    }

    public function index(): void
    {
        if (\Core\Router::isAjax()) {
            $search = $_GET['search'] ?? '';
            $proyectos = !empty($search)
                ? $this->model->search($search)
                : $this->model->allWithRelations();
            $this->json(['success' => true, 'data' => $proyectos]);
            return;
        }

        $proyectos = $this->model->allWithRelations();
        $this->view('proyectos/index', [
            'pageTitle'  => 'Gestión de Proyectos',
            'pageScript' => 'proyectos',
            'proyectos'  => $proyectos,
        ]);
    }

    public function create(): void
    {
        $clientes  = (new Cliente())->all('nombre', 'ASC');
        $inmuebles = (new Inmueble())->allWithCliente();

        $this->view('proyectos/form', [
            'pageTitle'  => 'Nuevo Proyecto',
            'pageScript' => 'proyectos',
            'proyecto'   => null,
            'clientes'   => $clientes,
            'inmuebles'  => $inmuebles,
            'action'     => url('proyecto/store'),
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('proyecto/index');
            return;
        }

        $data = array_intersect_key($this->allInput(), array_flip(['id_cliente', 'id_inmueble', 'nombre_del_proyecto', 'fecha_de_inicio', 'estado_del_proyecto', 'presupuesto_inicial']));

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_cliente'           => 'required|numeric',
            'nombre_del_proyecto'  => 'required|max:60|no_numbers',
            'fecha_de_inicio'      => 'required|date',
            'estado_del_proyecto'  => 'required|in:En Proceso,Observado,Aprobado,Finalizado,Incompleto',
            'presupuesto_inicial'  => 'required|numeric',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect('proyecto/create');
            return;
        }

        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', (string) $data['presupuesto_inicial'])
            || !(new Cliente())->find((int) $data['id_cliente'])) {
            Session::flash('error', 'Seleccione un cliente válido y un presupuesto no negativo con hasta dos decimales.');
            $this->redirect('proyecto/create');
            return;
        }

        if (empty($data['id_inmueble'])) {
            $data['id_inmueble'] = null;
        } else {
            // Validar que el inmueble seleccionado pertenezca al cliente
            $inmueble = (new Inmueble())->find((int) $data['id_inmueble']);
            if (!$inmueble || (int) $inmueble['id_cliente'] !== (int) $data['id_cliente']) {
                Session::flash('error', 'El inmueble seleccionado no pertenece al cliente seleccionado.');
                $this->redirect('proyecto/create');
                return;
            }
        }

        $id = $this->model->create($data);
        $this->logActivity('Creó proyecto: ' . $data['nombre_del_proyecto'], 'proyectos', $id);
        Session::flash('success', 'Proyecto creado exitosamente.');
        $this->redirect('proyecto/index');
    }

    public function edit(int $id = 0): void
    {
        $proyecto = $this->model->find($id);
        if (!$proyecto) {
            Session::flash('error', 'Proyecto no encontrado.');
            $this->redirect('proyecto/index');
            return;
        }

        $clientes  = (new Cliente())->all('nombre', 'ASC');
        $inmuebles = (new Inmueble())->allWithCliente();

        $this->view('proyectos/form', [
            'pageTitle'  => 'Editar Proyecto',
            'pageScript' => 'proyectos',
            'proyecto'   => $proyecto,
            'clientes'   => $clientes,
            'inmuebles'  => $inmuebles,
            'action'     => url("proyecto/update/{$id}"),
        ]);
    }

    public function update(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('proyecto/index');
            return;
        }

        $data = array_intersect_key($this->allInput(), array_flip(['id_cliente', 'id_inmueble', 'nombre_del_proyecto', 'fecha_de_inicio', 'estado_del_proyecto', 'presupuesto_inicial']));

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_cliente'           => 'required|numeric',
            'nombre_del_proyecto'  => 'required|max:60|no_numbers',
            'fecha_de_inicio'      => 'required|date',
            'estado_del_proyecto'  => 'required|in:En Proceso,Observado,Aprobado,Finalizado,Incompleto',
            'presupuesto_inicial'  => 'required|numeric',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect("proyecto/edit/{$id}");
            return;
        }

        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', (string) $data['presupuesto_inicial'])
            || !(new Cliente())->find((int) $data['id_cliente'])) {
            Session::flash('error', 'Seleccione un cliente válido y un presupuesto no negativo con hasta dos decimales.');
            $this->redirect("proyecto/edit/{$id}");
            return;
        }

        if (empty($data['id_inmueble'])) {
            $data['id_inmueble'] = null;
        } else {
            // Validar que el inmueble seleccionado pertenezca al cliente
            $inmueble = (new Inmueble())->find((int) $data['id_inmueble']);
            if (!$inmueble || (int) $inmueble['id_cliente'] !== (int) $data['id_cliente']) {
                Session::flash('error', 'El inmueble seleccionado no pertenece al cliente seleccionado.');
                $this->redirect("proyecto/edit/{$id}");
                return;
            }
        }

        try {
            $this->model->actualizarConPagos($id, $data);
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect("proyecto/edit/{$id}");
            return;
        }
        $this->logActivity('Actualizó proyecto: ' . $data['nombre_del_proyecto'], 'proyectos', $id);
        Session::flash('success', 'Proyecto actualizado exitosamente.');
        $this->redirect('proyecto/index');
    }

    /**
     * Ver detalle del proyecto con toda la información relacionada
     */
    public function detalle(int $id = 0): void
    {
        $proyecto = $this->model->getDetail($id);
        if (!$proyecto) {
            Session::flash('error', 'Proyecto no encontrado.');
            $this->redirect('proyecto/index');
            return;
        }

        $empleados = $this->model->getEmpleados($id);

        $this->view('proyectos/detalle', [
            'pageTitle'     => $proyecto['nombre_del_proyecto'],
            'pageScript'    => 'proyectos',
            'proyecto'      => $proyecto,
            'empleados'     => $empleados,
            'disponibles' => (new \App\Models\Empleado())->where('estado', 'Activo'),
        ]);
    }

    /**
     * Asignar empleado a proyecto (AJAX)
     */
    public function asignarEmpleado(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            http_response_code(400); echo 'Petición inválida.'; return;
        }
        $idProyecto = (int) $this->input('id_proyecto', 0);
        $idEmpleado = (int) $this->input('id_empleado', 0);
        $proyecto = $this->model->find($idProyecto);
        $empleado = (new \App\Models\Empleado())->find($idEmpleado);
        if (!$proyecto || $proyecto['estado_del_proyecto'] === 'Finalizado' || !$empleado || $empleado['estado'] !== 'Activo') {
            Session::flash('error', 'Seleccione un proyecto sin finalizar y un empleado activo.');
        } else {
            $this->model->asignarEmpleado($idProyecto, $idEmpleado);
            $this->logActivity('Asignó empleado a proyecto', 'proyecto_empleado', $idProyecto);
            Session::flash('success', 'Empleado asignado correctamente.');
        }
        $this->redirect("proyecto/detalle/{$idProyecto}");
    }

    public function desasignarEmpleado(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            http_response_code(400); echo 'Petición inválida.'; return;
        }
        $idProyecto = (int) $this->input('id_proyecto', 0);
        $idEmpleado = (int) $this->input('id_empleado', 0);
        try {
            $this->model->desasignarEmpleado($idProyecto, $idEmpleado);
            $this->logActivity('Desasignó empleado de proyecto', 'proyecto_empleado', $idProyecto);
            Session::flash('success', 'Empleado desasignado.');
        } catch (\PDOException $e) {
            if ($e->getCode() !== '23000') throw $e;
            Session::flash('error', 'El empleado tiene tareas en este proyecto. Se conserva la asignación y su seguimiento.');
        }
        $this->redirect("proyecto/detalle/{$idProyecto}");
    }

    public function delete(int $id = 0): void
    {
        if (!\Core\Router::isAjax() || !$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('proyecto/index');
            return;
        }

        try {
            $proyecto = $this->model->find($id);
            $this->model->delete($id);
            $this->logActivity('Eliminó proyecto: ' . ($proyecto['nombre_del_proyecto'] ?? ''), 'proyectos', $id);
            $this->json(['success' => true, 'message' => 'Proyecto eliminado exitosamente.']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'No se puede eliminar: el proyecto tiene registros asociados.'], 400);
        }
    }
}
