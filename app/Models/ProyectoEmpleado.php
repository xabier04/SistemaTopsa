<?php

namespace App\Models;

use Core\Model;

/**
 * Modelo ProyectoEmpleado (tabla pivote)
 */
class ProyectoEmpleado extends Model
{
    protected string $table = 'proyecto_empleado';
    protected string $primaryKey = 'id_empleado'; // Clave compuesta, usar métodos custom

    /**
     * Obtener empleados de un proyecto
     */
    public function getByProyecto(int $idProyecto): array
    {
        return $this->where('id_proyecto', $idProyecto);
    }

    /**
     * Obtener proyectos de un empleado
     */
    public function getByEmpleado(int $idEmpleado): array
    {
        return $this->where('id_empleado', $idEmpleado);
    }
}
