<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Cliente;

/**
 * Controlador de Clientes
 */
class ClienteController extends Controller
{
    private Cliente $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Cliente();
    }

    /**
     * Listar clientes (soporta AJAX para búsqueda)
     */
    public function index(): void
    {
        if (\Core\Router::isAjax()) {
            $search = $_GET['search'] ?? '';
            $clientes = !empty($search)
                ? $this->model->search($search)
                : $this->model->allWithProjectCount();
            $this->json(['success' => true, 'data' => $clientes]);
            return;
        }

        $clientes = $this->model->allWithProjectCount();
        $this->view('clientes/index', [
            'pageTitle'  => 'Gestión de Clientes',
            'pageScript' => 'clientes',
            'clientes'   => $clientes,
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create(): void
    {
        $this->view('clientes/form', [
            'pageTitle'  => 'Nuevo Cliente',
            'pageScript' => 'clientes',
            'cliente'    => null,
            'action'     => url('cliente/store'),
        ]);
    }

    /**
     * Guardar nuevo cliente
     */
    public function store(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('cliente/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        // Normalizar DUI y Teléfono si vienen en números planos
        if (!empty($data['dui'])) {
            $duiDigits = preg_replace('/\D/', '', (string) $data['dui']);
            if (strlen($duiDigits) === 9 && !str_contains((string) $data['dui'], '-')) {
                $data['dui'] = substr($duiDigits, 0, 8) . '-' . substr($duiDigits, 8, 1);
            }
        }
        if (!empty($data['telefono'])) {
            $telDigits = preg_replace('/\D/', '', (string) $data['telefono']);
            if (strlen($telDigits) === 8 && !str_contains((string) $data['telefono'], '-')) {
                $data['telefono'] = substr($telDigits, 0, 4) . '-' . substr($telDigits, 4, 4);
            }
        }

        $validator = new Validator($data);
        if (!$validator->validate([
            'nombre'             => 'required|max:90|no_numbers',
            'dui'                => 'required|dui|unique:clientes',
            'telefono'           => 'phone',
            'correo_electronico' => 'email|max:50',
            'direccion'          => 'required',
        ])) {
            \Core\FormState::save(url('cliente/store'), $data, $validator->getErrors());
            Session::flash('error', $validator->firstError());
            $this->redirect('cliente/create');
            return;
        }

        $this->model->create($data);
        $this->logActivity('Creó un nuevo cliente: ' . $data['nombre'], 'clientes');
        Session::flash('success', 'Cliente registrado exitosamente.');
        $this->redirect('cliente/index');
    }

    /**
     * Formulario de edición
     */
    public function edit(int $id = 0): void
    {
        $cliente = $this->model->find($id);
        if (!$cliente) {
            Session::flash('error', 'Cliente no encontrado.');
            $this->redirect('cliente/index');
            return;
        }

        $this->view('clientes/form', [
            'pageTitle'  => 'Editar Cliente',
            'pageScript' => 'clientes',
            'cliente'    => $cliente,
            'action'     => url("cliente/update/{$id}"),
        ]);
    }

    /**
     * Actualizar cliente
     */
    public function update(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('cliente/index');
            return;
        }

        $data = $this->allInput();
        unset($data['_csrf_token']);

        // Normalizar DUI y Teléfono si vienen en números planos
        if (!empty($data['dui'])) {
            $duiDigits = preg_replace('/\D/', '', (string) $data['dui']);
            if (strlen($duiDigits) === 9 && !str_contains((string) $data['dui'], '-')) {
                $data['dui'] = substr($duiDigits, 0, 8) . '-' . substr($duiDigits, 8, 1);
            }
        }
        if (!empty($data['telefono'])) {
            $telDigits = preg_replace('/\D/', '', (string) $data['telefono']);
            if (strlen($telDigits) === 8 && !str_contains((string) $data['telefono'], '-')) {
                $data['telefono'] = substr($telDigits, 0, 4) . '-' . substr($telDigits, 4, 4);
            }
        }

        $validator = new Validator($data);
        if (!$validator->validate([
            'nombre'             => 'required|max:90|no_numbers',
            'dui'                => "required|dui|unique:clientes,{$id}",
            'telefono'           => 'phone',
            'correo_electronico' => 'email|max:50',
            'direccion'          => 'required',
        ])) {
            \Core\FormState::save(url("cliente/update/{$id}"), $data, $validator->getErrors());
            Session::flash('error', $validator->firstError());
            $this->redirect("cliente/edit/{$id}");
            return;
        }

        $this->model->update($id, $data);
        $this->logActivity('Actualizó cliente: ' . $data['nombre'], 'clientes', $id);
        Session::flash('success', 'Cliente actualizado exitosamente.');
        $this->redirect('cliente/index');
    }

    /**
     * Eliminar cliente (AJAX)
     */
    public function delete(int $id = 0): void
    {
        if (!\Core\Router::isAjax()) {
            $this->redirect('cliente/index');
            return;
        }

        try {
            $cliente = $this->model->find($id);
            $this->model->delete($id);
            $this->logActivity('Eliminó cliente: ' . ($cliente['nombre'] ?? ''), 'clientes', $id);
            $this->json(['success' => true, 'message' => 'Cliente eliminado exitosamente.']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'No se puede eliminar: el cliente tiene proyectos asociados.'], 400);
        }
    }

    /**
     * Ver historial de proyectos del cliente
     */
    public function historial(int $id = 0): void
    {
        $cliente = $this->model->find($id);
        if (!$cliente) {
            Session::flash('error', 'Cliente no encontrado.');
            $this->redirect('cliente/index');
            return;
        }

        $proyectos = $this->model->getProyectos($id);

        if (\Core\Router::isAjax()) {
            $this->json(['success' => true, 'cliente' => $cliente, 'proyectos' => $proyectos]);
            return;
        }

        $this->view('clientes/index', [
            'pageTitle'  => 'Historial — ' . $cliente['nombre'],
            'pageScript' => 'clientes',
            'clientes'   => $this->model->allWithProjectCount(),
            'clienteDetalle' => $cliente,
            'proyectos'  => $proyectos,
        ]);
    }
}
