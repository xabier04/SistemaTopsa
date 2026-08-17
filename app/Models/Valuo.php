<?php

namespace App\Models;

use Core\Model;

/**
 * Modelo Valuo (Avalúo)
 */
class Valuo extends Model
{
    protected string $table = 'valuos';
    protected string $primaryKey = 'id_valuo';

    /**
     * Obtener valuaciones con datos del proyecto
     */
    public function allWithProyecto(): array
    {
        $sql = "SELECT v.*, p.nombre_del_proyecto, c.nombre as nombre_cliente
                FROM valuos v
                LEFT JOIN proyectos p ON v.id_proyecto = p.id_proyecto
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                ORDER BY v.fecha_del_valuo DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtener valuaciones de un proyecto
     */
    public function getByProyecto(int $idProyecto): array
    {
        $sql = "SELECT * FROM valuos WHERE id_proyecto = :id ORDER BY fecha_del_valuo DESC";
        return $this->db->query($sql, [':id' => $idProyecto])->fetchAll();
    }
}
