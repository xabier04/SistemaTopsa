<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Valuo;
use App\Models\Proyecto;

/**
 * Controlador de Valuaciones (Avalúos)
 */
class ValuoController extends Controller
{
    private Valuo $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Valuo();
    }

    public function index(): void
    {
        $valuos = $this->model->allWithProyecto();
        $this->view('valuos/index', [
            'pageTitle'  => 'Gestión de Valuaciones',
            'pageScript' => 'valuos',
            'valuos'     => $valuos,
        ]);
    }

    public function create(): void
    {
        $proyectos = (new Proyecto())->allWithRelations();
        $this->view('valuos/form', [
            'pageTitle'  => 'Nueva Valuación',
            'valuo'      => null,
            'proyectos'  => $proyectos,
            'action'     => url('valuo/store'),
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('valuo/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_proyecto'           => 'required|numeric',
            'fecha_del_valuo'       => 'required|date',
            'monto_estimado'        => 'required|numeric',
            'porcentaje_de_avance'  => 'required|numeric',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect('valuo/create');
            return;
        }

        $this->model->create($data);
        $this->logActivity('Registró valuación para proyecto #' . $data['id_proyecto'], 'valuos');
        Session::flash('success', 'Valuación registrada exitosamente.');
        $this->redirect('valuo/index');
    }

    public function edit(int $id = 0): void
    {
        $valuo = $this->model->find($id);
        if (!$valuo) {
            Session::flash('error', 'Valuación no encontrada.');
            $this->redirect('valuo/index');
            return;
        }

        $proyectos = (new Proyecto())->allWithRelations();
        $this->view('valuos/form', [
            'pageTitle'  => 'Editar Valuación',
            'valuo'      => $valuo,
            'proyectos'  => $proyectos,
            'action'     => url("valuo/update/{$id}"),
        ]);
    }

    public function update(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('valuo/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        $this->model->update($id, $data);
        $this->logActivity('Actualizó valuación #' . $id, 'valuos', $id);
        Session::flash('success', 'Valuación actualizada exitosamente.');
        $this->redirect('valuo/index');
    }
}
