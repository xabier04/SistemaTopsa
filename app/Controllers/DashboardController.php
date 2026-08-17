<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Proyecto;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Transaccion;

/**
 * Controlador del Dashboard
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $proyectoModel     = new Proyecto();
        $clienteModel      = new Cliente();
        $empleadoModel     = new Empleado();
        $transaccionModel  = new Transaccion();

        // KPIs
        $totalProyectos   = $proyectoModel->count();
        $totalClientes    = $clienteModel->count();
        $totalEmpleados   = $empleadoModel->count('estado', 'Activo');
        $totalIngresos    = $transaccionModel->totalIngresos();
        $totalPresupuesto = $proyectoModel->totalPresupuestos();
        $estadosProyecto  = $proyectoModel->countByEstado();
        $proyectosRecientes = $proyectoModel->recent(5);

        // Ingresos por mes del año actual
        $ingresosMensuales = $transaccionModel->ingresosPorMes((int) date('Y'));

        $this->view('dashboard/index', [
            'pageTitle'          => 'Dashboard',
            'totalProyectos'     => $totalProyectos,
            'totalClientes'      => $totalClientes,
            'totalEmpleados'     => $totalEmpleados,
            'totalIngresos'      => $totalIngresos,
            'totalPresupuesto'   => $totalPresupuesto,
            'estadosProyecto'    => $estadosProyecto,
            'proyectosRecientes' => $proyectosRecientes,
            'ingresosMensuales'  => $ingresosMensuales,
        ]);
    }
}
