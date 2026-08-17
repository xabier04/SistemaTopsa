<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Proyecto;
use App\Models\Cliente;
use App\Models\Inmueble;
use App\Models\Empleado;
use App\Models\Transaccion;
use App\Models\Valuo;
use App\Models\Documento;

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

        $data = $this->allInput();
        unset($data['_csrf_token']);

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_cliente'           => 'required|numeric',
            'nombre_del_proyecto'  => 'required|max:60',
            'fecha_de_inicio'      => 'required|date',
            'estado_del_proyecto'  => 'required|in:En Proceso,Incompleto,Finalizado',
            'presupuesto_inicial'  => 'required|numeric',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect('proyecto/create');
            return;
        }

        if (empty($data['id_inmueble'])) {
            $data['id_inmueble'] = null;
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

        $data = $this->allInput();
        unset($data['_csrf_token']);

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_cliente'           => 'required|numeric',
            'nombre_del_proyecto'  => 'required|max:60',
            'fecha_de_inicio'      => 'required|date',
            'estado_del_proyecto'  => 'required|in:En Proceso,Incompleto,Finalizado',
            'presupuesto_inicial'  => 'required|numeric',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect("proyecto/edit/{$id}");
            return;
        }

        if (empty($data['id_inmueble'])) {
            $data['id_inmueble'] = null;
        }

        $this->model->update($id, $data);
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

        $empleados     = $this->model->getEmpleados($id);
        $transacciones = (new Transaccion())->getByProyecto($id);
        $valuaciones   = (new Valuo())->getByProyecto($id);
        $documentos    = (new Documento())->getByProyecto($id);

        $this->view('proyectos/detalle', [
            'pageTitle'     => $proyecto['nombre_del_proyecto'],
            'pageScript'    => 'proyectos',
            'proyecto'      => $proyecto,
            'empleados'     => $empleados,
            'transacciones' => $transacciones,
            'valuaciones'   => $valuaciones,
            'documentos'    => $documentos,
        ]);
    }

    /**
     * Asignar empleado a proyecto (AJAX)
     */
    public function asignarEmpleado(): void
    {
        if (!$this->isPost() || !\Core\Router::isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida.'], 400);
            return;
        }

        $idProyecto  = (int) $this->input('id_proyecto', 0);
        $idEmpleado  = (int) $this->input('id_empleado', 0);

        if ($idProyecto === 0 || $idEmpleado === 0) {
            $this->json(['success' => false, 'message' => 'Datos incompletos.'], 400);
            return;
        }

        $this->model->asignarEmpleado($idProyecto, $idEmpleado);
        $this->logActivity('Asignó empleado a proyecto', 'proyecto_empleado');
        $this->json(['success' => true, 'message' => 'Empleado asignado exitosamente.']);
    }

    /**
     * Desasignar empleado de proyecto (AJAX)
     */
    public function desasignarEmpleado(): void
    {
        if (!$this->isPost() || !\Core\Router::isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida.'], 400);
            return;
        }

        $idProyecto = (int) $this->input('id_proyecto', 0);
        $idEmpleado = (int) $this->input('id_empleado', 0);

        $this->model->desasignarEmpleado($idProyecto, $idEmpleado);
        $this->logActivity('Desasignó empleado de proyecto', 'proyecto_empleado');
        $this->json(['success' => true, 'message' => 'Empleado desasignado.']);
    }

    public function delete(int $id = 0): void
    {
        if (!\Core\Router::isAjax()) {
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
