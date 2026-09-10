<?php

namespace App\Models;

use Core\Model;

/**
 * Modelo Inmueble
 */
class Inmueble extends Model
{
    protected string $table = 'inmuebles';
    protected string $primaryKey = 'id_inmueble';

    /**
     * Obtener inmuebles con datos del cliente y conteo de proyectos
     */
    public function allWithCliente(): array
    {
        $sql = "SELECT i.*, c.nombre as nombre_cliente, COUNT(p.id_proyecto) as total_proyectos
                FROM inmuebles i
                LEFT JOIN clientes c ON i.id_cliente = c.id_cliente
                LEFT JOIN proyectos p ON i.id_inmueble = p.id_inmueble
                GROUP BY i.id_inmueble, c.nombre
                ORDER BY i.id_inmueble DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtener inmuebles de un cliente específico
     */
    public function getByCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente);
    }

    /**
     * Contar proyectos asociados a un inmueble
     */
    public function countProyectos(int $idInmueble): int
    {
        $sql = "SELECT COUNT(*) as total FROM proyectos WHERE id_inmueble = :id";
        $result = $this->db->query($sql, [':id' => $idInmueble])->fetch();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Obtener los proyectos asociados a un inmueble
     */
    public function getProyectos(int $idInmueble): array
    {
        $sql = "SELECT p.*, c.nombre as nombre_cliente
                FROM proyectos p
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                WHERE p.id_inmueble = :id
                ORDER BY p.fecha_de_inicio DESC";
        return $this->db->query($sql, [':id' => $idInmueble])->fetchAll();
    }
}
