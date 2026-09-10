<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Inmueble;
use App\Models\Cliente;

/**
 * Controlador de Inmuebles
 */
class InmuebleController extends Controller
{
    private Inmueble $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Inmueble();
    }

    public function index(): void
    {
        if (\Core\Router::isAjax()) {
            $idCliente = $_GET['id_cliente'] ?? '';
            $inmuebles = !empty($idCliente)
                ? $this->model->getByCliente((int) $idCliente)
                : $this->model->allWithCliente();
            $this->json(['success' => true, 'data' => $inmuebles]);
            return;
        }

        $inmuebles = $this->model->allWithCliente();
        $clientes = (new Cliente())->all('nombre', 'ASC');

        $this->view('inmuebles/index', [
            'pageTitle'  => 'Gestión de Inmuebles',
            'pageScript' => 'inmuebles',
            'inmuebles'  => $inmuebles,
            'clientes'   => $clientes,
        ]);
    }

    public function create(): void
    {
        $clientes = (new Cliente())->all('nombre', 'ASC');
        $this->view('inmuebles/form', [
            'pageTitle' => 'Nuevo Inmueble',
            'inmueble'  => null,
            'clientes'  => $clientes,
            'action'    => url('inmueble/store'),
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('inmueble/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        // Limpiar matrícula: solo permitir números
        if (!empty($data['matricula'])) {
            $data['matricula'] = preg_replace('/\D/', '', (string) $data['matricula']);
        }

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_cliente'    => 'required|numeric',
            'matricula'     => 'required|matricula|unique:inmuebles',
            'direccion'     => 'required',
            'area'          => 'required|decimal',
            'tipo_inmueble' => 'required|max:30',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect('inmueble/create');
            return;
        }

        $this->model->create($data);
        $this->logActivity('Registró inmueble: ' . $data['matricula'], 'inmuebles');
        Session::flash('success', 'Inmueble registrado exitosamente.');
        $this->redirect('inmueble/index');
    }

    public function edit(int $id = 0): void
    {
        $inmueble = $this->model->find($id);
        if (!$inmueble) {
            Session::flash('error', 'Inmueble no encontrado.');
            $this->redirect('inmueble/index');
            return;
        }

        $clientes = (new Cliente())->all('nombre', 'ASC');
        $this->view('inmuebles/form', [
            'pageTitle' => 'Editar Inmueble',
            'inmueble'  => $inmueble,
            'clientes'  => $clientes,
            'action'    => url("inmueble/update/{$id}"),
        ]);
    }

    public function update(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('inmueble/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        // Limpiar matrícula: solo permitir números
        if (!empty($data['matricula'])) {
            $data['matricula'] = preg_replace('/\D/', '', (string) $data['matricula']);
        }

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_cliente'    => 'required|numeric',
            'matricula'     => "required|matricula|unique:inmuebles,{$id}",
            'direccion'     => 'required',
            'area'          => 'required|decimal',
            'tipo_inmueble' => 'required|max:30',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect("inmueble/edit/{$id}");
            return;
        }

        $this->model->update($id, $data);
        $this->logActivity('Actualizó inmueble: ' . $data['matricula'], 'inmuebles', $id);
        Session::flash('success', 'Inmueble actualizado exitosamente.');
        $this->redirect('inmueble/index');
    }

    public function delete(int $id = 0): void
    {
        if (!\Core\Router::isAjax()) {
            $this->redirect('inmueble/index');
            return;
        }

        try {
            $inmueble = $this->model->find($id);
            $this->model->delete($id);
            $this->logActivity('Eliminó inmueble: ' . ($inmueble['matricula'] ?? ''), 'inmuebles', $id);
            $this->json(['success' => true, 'message' => 'Inmueble eliminado exitosamente.']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'No se puede eliminar: el inmueble tiene proyectos asociados.'], 400);
        }
    }
}
