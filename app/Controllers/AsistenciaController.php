<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Asistencia;
use App\Models\Empleado;

/**
 * Controlador de Asistencia
 */
class AsistenciaController extends Controller
{
    private Asistencia $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Asistencia();
    }

    public function index(): void
    {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $user = Session::getUser();
        $isAdmin = Session::isAdmin();

        if ($isAdmin) {
            $asistencias = $this->model->allWithEmpleado($fecha);
            $empleados = (new Empleado())->activos();
            $currentEmpleado = null;
        } else {
            $idEmpleado = (int) ($user['id_empleado'] ?? 0);
            $asistencias = $this->model->allWithEmpleado($fecha, $idEmpleado);
            $empleados = [];
            $currentEmpleado = (new Empleado())->find($idEmpleado);
        }

        $this->view('asistencias/index', [
            'pageTitle'         => 'Control de Asistencia',
            'pageScript'        => 'asistencias',
            'asistencias'       => $asistencias,
            'empleados'         => $empleados,
            'fechaFiltro'       => $fecha,
            'isAdmin'           => $isAdmin,
            'currentEmpleado'   => $currentEmpleado,
        ]);
    }

    /**
     * Marcar entrada
     */
    public function marcarEntrada(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('asistencia/index');
            return;
        }

        $user = Session::getUser();
        if (Session::isAdmin()) {
            $idEmpleado = (int) $this->input('id_empleado', 0);
        } else {
            $idEmpleado = (int) ($user['id_empleado'] ?? 0);
        }

        if ($idEmpleado === 0) {
            Session::flash('error', 'No se ha podido identificar al empleado.');
            $this->redirect('asistencia/index');
            return;
        }

        // Verificar si ya marcó hoy
        $marca = $this->model->getMarcaHoy($idEmpleado);
        if ($marca) {
            Session::flash('warning', 'Ya existe marca de entrada para el día de hoy.');
            $this->redirect('asistencia/index');
            return;
        }

        $this->model->create([
            'id_empleado'      => $idEmpleado,
            'fecha_de_marcaje' => date('Y-m-d'),
            'hora_de_entrada'  => date('H:i:s'),
        ]);

        $this->logActivity('Marcó entrada de empleado #' . $idEmpleado, 'asistencias');
        Session::flash('success', 'Entrada registrada exitosamente.');
        $this->redirect('asistencia/index');
    }

    /**
     * Marcar salida
     */
    public function marcarSalida(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('asistencia/index');
            return;
        }

        $user = Session::getUser();
        if (Session::isAdmin()) {
            $idEmpleado = (int) $this->input('id_empleado', 0);
        } else {
            $idEmpleado = (int) ($user['id_empleado'] ?? 0);
        }

        if ($idEmpleado === 0) {
            Session::flash('error', 'No se ha podido identificar al empleado.');
            $this->redirect('asistencia/index');
            return;
        }

        $marca = $this->model->getMarcaHoy($idEmpleado);

        if (!$marca) {
            Session::flash('error', 'No existe marca de entrada para hoy.');
            $this->redirect('asistencia/index');
            return;
        }

        if (!empty($marca['hora_de_salida'])) {
            Session::flash('warning', 'La salida ya fue registrada.');
            $this->redirect('asistencia/index');
            return;
        }

        $horaSalida = date('H:i:s');
        $entrada = new \DateTime($marca['hora_de_entrada']);
        $salida  = new \DateTime($horaSalida);
        $diff    = $entrada->diff($salida);
        $horas   = $diff->h + ($diff->i / 60);

        $this->model->update($marca['id_asistencia'], [
            'hora_de_salida'   => $horaSalida,
            'horas_trabajadas' => round($horas, 2),
        ]);

        $this->logActivity('Marcó salida de empleado #' . $idEmpleado, 'asistencias');
        Session::flash('success', 'Salida registrada. Horas trabajadas: ' . number_format($horas, 2));
        $this->redirect('asistencia/index');
    }

    /**
     * Consolidado mensual
     */
    public function consolidado(): void
    {
        $user = Session::getUser();
        $isAdmin = Session::isAdmin();
        $mes  = (int) ($_GET['mes'] ?? date('m'));
        $anio = (int) ($_GET['anio'] ?? date('Y'));

        if ($isAdmin) {
            $idEmpleado = (int) ($_GET['id_empleado'] ?? 0);
            $empleados = (new Empleado())->activos();
        } else {
            $idEmpleado = (int) ($user['id_empleado'] ?? 0);
            $empleados = [];
        }

        $registros = [];
        $totalHoras = 0;
        $empleadoSeleccionado = null;

        if ($idEmpleado > 0) {
            $registros = $this->model->consolidadoMensual($idEmpleado, $mes, $anio);
            $totalHoras = $this->model->totalHorasMes($idEmpleado, $mes, $anio);
            $empleadoSeleccionado = (new Empleado())->find($idEmpleado);
        }

        $this->view('asistencias/consolidado', [
            'pageTitle'            => 'Consolidado de Asistencia',
            'pageScript'           => 'asistencias',
            'empleados'            => $empleados,
            'registros'            => $registros,
            'totalHoras'           => $totalHoras,
            'mesActual'            => $mes,
            'anioActual'           => $anio,
            'idEmpleado'           => $idEmpleado,
            'empleadoSeleccionado' => $empleadoSeleccionado,
            'isAdmin'              => $isAdmin,
        ]);
    }
}
