<?php

namespace App\Models;

use Core\Model;

/**
 * Modelo Asistencia
 */
class Asistencia extends Model
{
    protected string $table = 'asistencias';
    protected string $primaryKey = 'id_asistencia';

    /**
     * Obtener asistencias con datos del empleado (opcionalmente filtrado por empleado)
     */
    public function allWithEmpleado(string $fecha = '', ?int $idEmpleado = null): array
    {
        $sql = "SELECT a.*, e.nombre_completo, e.cargo
                FROM asistencias a
                LEFT JOIN empleados e ON a.id_empleado = e.id_empleado";
        $params = [];
        $where = [];

        if (!empty($fecha)) {
            $where[] = "a.fecha_de_marcaje = :fecha";
            $params[':fecha'] = $fecha;
        }

        if ($idEmpleado !== null && $idEmpleado > 0) {
            $where[] = "a.id_empleado = :id_empleado";
            $params[':id_empleado'] = $idEmpleado;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY a.fecha_de_marcaje DESC, a.hora_de_entrada ASC";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Obtener marca de hoy para un empleado
     */
    public function getMarcaHoy(int $idEmpleado): array|false
    {
        $sql = "SELECT * FROM asistencias 
                WHERE id_empleado = :id AND fecha_de_marcaje = CURDATE()
                LIMIT 1";
        return $this->db->query($sql, [':id' => $idEmpleado])->fetch();
    }

    /**
     * Consolidado mensual de un empleado
     */
    public function consolidadoMensual(int $idEmpleado, int $mes, int $anio): array
    {
        $sql = "SELECT a.*, e.nombre_completo
                FROM asistencias a
                LEFT JOIN empleados e ON a.id_empleado = e.id_empleado
                WHERE a.id_empleado = :id 
                  AND MONTH(a.fecha_de_marcaje) = :mes 
                  AND YEAR(a.fecha_de_marcaje) = :anio
                ORDER BY a.fecha_de_marcaje ASC";
        return $this->db->query($sql, [
            ':id'   => $idEmpleado,
            ':mes'  => $mes,
            ':anio' => $anio,
        ])->fetchAll();
    }

    /**
     * Total de horas trabajadas en un mes
     */
    public function totalHorasMes(int $idEmpleado, int $mes, int $anio): float
    {
        $sql = "SELECT COALESCE(SUM(horas_trabajadas), 0) as total
                FROM asistencias
                WHERE id_empleado = :id 
                  AND MONTH(fecha_de_marcaje) = :mes 
                  AND YEAR(fecha_de_marcaje) = :anio";
        $result = $this->db->query($sql, [
            ':id'   => $idEmpleado,
            ':mes'  => $mes,
            ':anio' => $anio,
        ])->fetch();
        return (float) $result['total'];
    }
}
