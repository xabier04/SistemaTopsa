<?php

namespace App\Models;

use Core\Model;

/**
 * Modelo Cliente
 */
class Cliente extends Model
{
    protected string $table = 'clientes';
    protected string $primaryKey = 'id_cliente';

    /**
     * Obtener clientes con conteo de proyectos
     */
    public function allWithProjectCount(): array
    {
        $sql = "SELECT c.*, COUNT(p.id_proyecto) as total_proyectos
                FROM clientes c
                LEFT JOIN proyectos p ON c.id_cliente = p.id_cliente
                GROUP BY c.id_cliente
                ORDER BY c.nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtener historial de proyectos de un cliente
     */
    public function getProyectos(int $idCliente): array
    {
        $sql = "SELECT p.*
                FROM proyectos p
                WHERE p.id_cliente = :id
                ORDER BY p.fecha_de_inicio DESC";
        return $this->db->query($sql, [':id' => $idCliente])->fetchAll();
    }

    /**
     * Buscar clientes por nombre o DUI
     */
    public function search(string $term): array
    {
        $sql = "SELECT * FROM clientes 
                WHERE nombre LIKE :term OR dui LIKE :term
                ORDER BY nombre ASC";
        return $this->db->query($sql, [':term' => "%{$term}%"])->fetchAll();
    }
}
