<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Validator;
use App\Models\Tarea;

class TareaController extends Controller
{
    public function getRequiredRole(string $action): ?string
    {
        return in_array($action, ['index', 'avance'], true) ? null : 'Administrador';
    }

    public function index(): void
    {
        $model = new Tarea();
        $empleado = Session::isAdmin() ? null : (int) Session::getUser()['id_empleado'];
        $this->view('tareas/index', [
            'pageTitle' => Session::isAdmin() ? 'Tareas y avances del personal' : 'Mis proyectos y tareas',
            'tareas' => $model->listado($empleado), 'proyectos' => $model->proyectos($empleado),
            'asignaciones' => Session::isAdmin() ? $model->asignaciones() : [],
        ]);
    }

    public function store(): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) { $this->redirect('tarea/index'); return; }
        $data = array_intersect_key($this->allInput(), array_flip(['titulo', 'descripcion', 'fecha_limite']));
        $asignacion = explode(':', $this->input('asignacion', ''));
        $data['id_proyecto'] = (int) ($asignacion[0] ?? 0);
        $data['id_empleado'] = (int) ($asignacion[1] ?? 0);
        $validator = new Validator($data);
        if (!$validator->validate(['titulo' => 'required|max:150', 'descripcion' => 'max:2000', 'fecha_limite' => 'required|date'])) {
            Session::flash('error', $validator->firstError());
            $this->redirect('tarea/index'); return;
        }
        try {
            $id = (new Tarea())->asignar($data);
            $this->logActivity('Asignó tarea a empleado', 'tareas', $id);
            Session::flash('success', 'Tarea asignada correctamente.');
        } catch (\DomainException $e) { Session::flash('error', $e->getMessage()); }
        $this->redirect('tarea/index');
    }

    public function avance(int $id = 0): void
    {
        if (!$this->isPost() || !$this->validateCsrf()) { $this->redirect('tarea/index'); return; }
        $avance = filter_var($this->input('avance'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
        $observaciones = $this->input('observaciones', '');
        if ($avance === false || mb_strlen($observaciones) > 2000) {
            Session::flash('error', 'Use un avance entero de 0 a 100 y observaciones de hasta 2000 caracteres.');
        } elseif (!(new Tarea())->avanzar($id, $avance, $observaciones, Session::isAdmin() ? null : (int) Session::getUser()['id_empleado'])) {
            http_response_code(403); echo 'No tiene acceso a esta tarea.'; return;
        } else {
            $this->logActivity('Actualizó avance de tarea a ' . $avance . '%', 'tareas', $id);
            Session::flash('success', 'Avance actualizado.');
        }
        $this->redirect('tarea/index');
    }
}
