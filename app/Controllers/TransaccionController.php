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
        ]);
    }

    public function create(): void
    {
        $proyectos = (new Proyecto())->allWithRelations();
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

        $data = $this->allInput();
        unset($data['_csrf_token']);

        $validator = new Validator($data);
        if (!$validator->validate([
            'id_proyecto'          => 'required|numeric',
            'fecha_de_pago'        => 'required|date',
            'monto_abonado'        => 'required|numeric',
            'tipo_de_transaccion'  => 'required|max:30',
        ])) {
            Session::flash('error', $validator->firstError());
            $this->redirect('transaccion/create');
            return;
        }

        // Calcular saldo pendiente automáticamente
        $saldoActual = $this->model->calcularSaldo((int) $data['id_proyecto']);
        $data['saldo_pendiente'] = $saldoActual - (float) $data['monto_abonado'];

        $this->model->create($data);
        $this->logActivity('Registró transacción por ' . formatMoney($data['monto_abonado']), 'transacciones');
        Session::flash('success', 'Transacción registrada. Saldo pendiente: ' . formatMoney($data['saldo_pendiente']));
        $this->redirect('transaccion/index');
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
            'ingresos'      => $ingresos,
            'totalIngresos' => $total,
        ]);
    }
}
