<?php

namespace App\Models;

use Core\Model;

/**
 * Modelo Empleado
 */
class Empleado extends Model
{
    protected string $table = 'empleados';
    protected string $primaryKey = 'id_empleado';

    /**
     * Obtener empleados activos
     */
    public function activos(): array
    {
        return $this->where('estado', 'Activo', 'nombre_completo ASC');
    }

    /**
     * Obtener empleados con conteo de proyectos asignados
     */
    public function allWithProjectCount(): array
    {
        $sql = "SELECT e.*, COUNT(pe.id_proyecto) as total_proyectos
                FROM empleados e
                LEFT JOIN proyecto_empleado pe ON e.id_empleado = pe.id_empleado
                GROUP BY e.id_empleado
                ORDER BY e.nombre_completo ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Buscar empleados
     */
    public function search(string $term): array
    {
        $sql = "SELECT * FROM empleados 
                WHERE nombre_completo LIKE :term OR dui LIKE :term OR cargo LIKE :term
                ORDER BY nombre_completo ASC";
        return $this->db->query($sql, [':term' => "%{$term}%"])->fetchAll();
    }
}
