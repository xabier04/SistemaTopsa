<?php
namespace App\Models;

use Core\Model;

class Tarea extends Model
{
    protected string $table = 'tareas';
    protected string $primaryKey = 'id_tarea';

    public function listado(?int $empleado = null): array
    {
        return $this->db->query('SELECT t.*, p.nombre_del_proyecto, p.estado_del_proyecto, e.nombre_completo
            FROM tareas t JOIN proyectos p ON p.id_proyecto = t.id_proyecto
            JOIN empleados e ON e.id_empleado = t.id_empleado'
            . ($empleado !== null ? ' WHERE t.id_empleado = ?' : '')
            . ' ORDER BY t.avance = 100, t.fecha_limite, t.id_tarea', $empleado !== null ? [$empleado] : [])->fetchAll();
    }

    public function proyectos(?int $empleado = null): array
    {
        return $this->db->query('SELECT p.id_proyecto, p.nombre_del_proyecto, p.estado_del_proyecto,
            p.fecha_de_inicio, COUNT(t.id_tarea) AS total_tareas, COALESCE(ROUND(AVG(t.avance)), 0) AS avance
            FROM proyectos p LEFT JOIN tareas t ON t.id_proyecto = p.id_proyecto'
            . ($empleado !== null ? ' AND t.id_empleado = :t_empleado WHERE EXISTS (SELECT 1 FROM proyecto_empleado pe WHERE pe.id_proyecto = p.id_proyecto AND pe.id_empleado = :p_empleado)' : '')
            . ' GROUP BY p.id_proyecto, p.nombre_del_proyecto, p.estado_del_proyecto, p.fecha_de_inicio ORDER BY p.fecha_de_inicio DESC',
            $empleado !== null ? [':t_empleado' => $empleado, ':p_empleado' => $empleado] : [])->fetchAll();
    }

    public function asignaciones(): array
    {
        return $this->db->query("SELECT pe.*, e.nombre_completo, p.nombre_del_proyecto FROM proyecto_empleado pe
            JOIN empleados e ON e.id_empleado = pe.id_empleado JOIN proyectos p ON p.id_proyecto = pe.id_proyecto
            WHERE e.estado = 'Activo' AND p.estado_del_proyecto <> 'Finalizado' ORDER BY p.nombre_del_proyecto, e.nombre_completo")->fetchAll();
    }

    public function asignar(array $data): int
    {
        $valid = $this->db->query("SELECT 1 FROM proyecto_empleado pe JOIN empleados e ON e.id_empleado = pe.id_empleado
            JOIN proyectos p ON p.id_proyecto = pe.id_proyecto WHERE pe.id_proyecto = ? AND pe.id_empleado = ?
            AND e.estado = 'Activo' AND p.estado_del_proyecto <> 'Finalizado'", [$data['id_proyecto'], $data['id_empleado']])->fetchColumn();
        if (!$valid) throw new \DomainException('Seleccione un empleado activo asignado a un proyecto sin finalizar.');
        return $this->create($data);
    }

    public function avanzar(int $id, int $avance, string $observaciones, ?int $empleado): bool
    {
        if ($avance < 0 || $avance > 100 || mb_strlen($observaciones) > 2000) throw new \DomainException('Avance u observaciones inválidos.');
        $tarea = $this->find($id);
        if (!$tarea || ($empleado !== null && (int) $tarea['id_empleado'] !== $empleado)) return false;
        $this->db->query('UPDATE tareas SET avance = ?, observaciones = ? WHERE id_tarea = ?'
            . ($empleado !== null ? ' AND id_empleado = ?' : ''),
            $empleado !== null ? [$avance, $observaciones, $id, $empleado] : [$avance, $observaciones, $id]);
        return true;
    }
}
