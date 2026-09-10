<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Proyecto;
use App\Models\Cliente;
use App\Models\Inmueble;

/**
 * Controlador del Panel Principal
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $proyectoModel  = new Proyecto();
        $clienteModel   = new Cliente();
        $inmuebleModel  = new Inmueble();

        // KPIs
        $totalProyectos   = $proyectoModel->count();
        $totalClientes    = $clienteModel->count();
        $totalInmuebles   = $inmuebleModel->count();
        $totalPresupuesto = $proyectoModel->totalPresupuestos();
        $estadosProyecto  = $proyectoModel->countByEstado();
        $proyectosRecientes = $proyectoModel->recent(5);

        $this->view('dashboard/index', [
            'pageTitle'          => 'Panel Principal',
            'totalProyectos'     => $totalProyectos,
            'totalClientes'      => $totalClientes,
            'totalInmuebles'     => $totalInmuebles,
            'totalPresupuesto'   => $totalPresupuesto,
            'estadosProyecto'    => $estadosProyecto,
            'proyectosRecientes' => $proyectosRecientes,
        ]);
    }
}
