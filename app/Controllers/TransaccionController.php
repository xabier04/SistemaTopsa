<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Transaccion;
use App\Models\Proyecto;

/**
 * Controlador de Transacciones
 */
class TransaccionController extends Controller
{
    private Transaccion $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Transaccion();
    }

    public function index(): void
    {
        if (\Core\Router::isAjax()) {
            $transacciones = $this->model->allWithProyecto();
            $this->json(['success' => true, 'data' => $transacciones]);
            return;
        }

        $transacciones = $this->model->allWithProyecto();
        $this->view('transacciones/index', [
            'pageTitle'     => 'Gestión de Transacciones',
            'pageScript'    => 'transacciones',
            'transacciones' => $transacciones,
            'saldos' => $this->model->saldos(),
        ]);
    }

    public function create(): void
    {
        $proyectos = $this->model->saldos();
        $_SESSION['pago_referencia'] = bin2hex(random_bytes(24));
        $this->view('transacciones/form', [
            'pageTitle'  => 'Nueva Transacción',
            'transaccion' => null,
            'proyectos'  => $proyectos,
            'action'     => url('transaccion/store'),
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('transaccion/index');
            return;
        }

        $data = array_intersect_key($this->allInput(), array_flip(['id_proyecto', 'fecha_de_pago', 'monto_abonado', 'tipo_de_transaccion', 'referencia']));
        if (!hash_equals($_SESSION['pago_referencia'] ?? '', $data['referencia'] ?? '') || empty($data['referencia'])) {
            Session::flash('error', 'Formulario de pago vencido. Abra un nuevo formulario.');
            $this->redirect('transaccion/create');
            return;
        }

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_proyecto'          => 'required|numeric',
            'fecha_de_pago'        => 'required|date',
            'monto_abonado'        => 'required|numeric',
            'tipo_de_transaccion'  => 'required|in:Abono,Pago Completo,Anticipo,Otro',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect('transaccion/create');
            return;
        }

        try {
            $id = $this->model->registrar($data);
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('transaccion/create');
            return;
        }
        $this->logActivity('Registró pago', 'transacciones', $id);
        Session::flash('success', 'Pago registrado correctamente.');
        $this->redirect("transaccion/comprobante/{$id}");
    }

    public function comprobante(int $id = 0): void
    {
        $pago = $this->model->comprobante($id);
        if (!$pago) { http_response_code(404); echo 'Comprobante no encontrado.'; return; }
        $this->view('transacciones/comprobante', ['pageTitle' => 'Comprobante de pago', 'pago' => $pago]);
    }

    /**
     * Reporte de ganancias por mes
     */
    public function reporteGanancias(): void
    {
        $year = (int) ($_GET['year'] ?? date('Y'));
        $ingresos = $this->model->ingresosPorMes($year);
        $total = $this->model->totalIngresos();

        if (\Core\Router::isAjax()) {
            $this->json(['success' => true, 'data' => $ingresos, 'total' => $total, 'year' => $year]);
            return;
        }

        $this->view('transacciones/index', [
            'pageTitle'     => 'Reporte de Ganancias',
            'pageScript'    => 'transacciones',
            'transacciones' => $this->model->allWithProyecto(),
            'saldos' => $this->model->saldos(),
            'ingresos'      => $ingresos,
            'totalIngresos' => $total,
        ]);
    }
}
