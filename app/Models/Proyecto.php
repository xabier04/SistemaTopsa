<?php

namespace App\Models;

use Core\Model;

/**
 * Modelo Proyecto
 */
class Proyecto extends Model
{
    protected string $table = 'proyectos';
    protected string $primaryKey = 'id_proyecto';

    /**
     * Obtener todos los proyectos con datos de cliente e inmueble
     */
    public function allWithRelations(): array
    {
        $sql = "SELECT p.*, 
                       c.nombre as nombre_cliente,
                       i.direccion as direccion_inmueble,
                       i.tipo_inmueble
                FROM proyectos p
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                LEFT JOIN inmuebles i ON p.id_inmueble = i.id_inmueble
                ORDER BY p.fecha_de_inicio DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtener detalle completo de un proyecto
     */
    public function getDetail(int $id): array|false
    {
        $sql = "SELECT p.*, 
                       c.nombre as nombre_cliente, c.telefono as telefono_cliente, c.correo_electronico,
                       i.matricula, i.direccion as direccion_inmueble, i.area, i.tipo_inmueble
                FROM proyectos p
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                LEFT JOIN inmuebles i ON p.id_inmueble = i.id_inmueble
                WHERE p.id_proyecto = :id";
        return $this->db->query($sql, [':id' => $id])->fetch();
    }

    /**
     * Obtener empleados asignados a un proyecto
     */
    public function getEmpleados(int $idProyecto): array
    {
        $sql = "SELECT e.*, pe.fecha_asignacion
                FROM proyecto_empleado pe
                JOIN empleados e ON pe.id_empleado = e.id_empleado
                WHERE pe.id_proyecto = :id
                ORDER BY pe.fecha_asignacion DESC";
        return $this->db->query($sql, [':id' => $idProyecto])->fetchAll();
    }

    /**
     * Asignar empleado a proyecto
     */
    public function asignarEmpleado(int $idProyecto, int $idEmpleado): bool
    {
        $sql = "INSERT IGNORE INTO proyecto_empleado (id_proyecto, id_empleado, fecha_asignacion) 
                VALUES (:proyecto, :empleado, CURDATE())";
        $this->db->query($sql, [':proyecto' => $idProyecto, ':empleado' => $idEmpleado]);
        return true;
    }

    /**
     * Desasignar empleado de proyecto
     */
    public function desasignarEmpleado(int $idProyecto, int $idEmpleado): bool
    {
        $sql = "DELETE FROM proyecto_empleado WHERE id_proyecto = :proyecto AND id_empleado = :empleado";
        $this->db->query($sql, [':proyecto' => $idProyecto, ':empleado' => $idEmpleado]);
        return true;
    }

    /**
     * Contar proyectos por estado
     */
    public function countByEstado(): array
    {
        $sql = "SELECT estado_del_proyecto, COUNT(*) as total
                FROM proyectos
                GROUP BY estado_del_proyecto";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtener los proyectos recientes
     */
    public function recent(int $limit = 5): array
    {
        $sql = "SELECT p.*, c.nombre as nombre_cliente
                FROM proyectos p
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                ORDER BY p.fecha_de_inicio DESC
                LIMIT :limit";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener total de presupuestos
     */
    public function totalPresupuestos(): float
    {
        $sql = "SELECT COALESCE(SUM(presupuesto_inicial), 0) as total FROM proyectos";
        $result = $this->db->query($sql)->fetch();
        return (float) $result['total'];
    }

    /**
     * Búsqueda de proyectos
     */
    public function search(string $term): array
    {
        $sql = "SELECT p.*, c.nombre as nombre_cliente
                FROM proyectos p
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                WHERE p.nombre_del_proyecto LIKE :term OR c.nombre LIKE :term
                ORDER BY p.fecha_de_inicio DESC";
        return $this->db->query($sql, [':term' => "%{$term}%"])->fetchAll();
    }
}
