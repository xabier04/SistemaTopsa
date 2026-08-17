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
     * Obtener inmuebles con datos del cliente
     */
    public function allWithCliente(): array
    {
        $sql = "SELECT i.*, c.nombre as nombre_cliente
                FROM inmuebles i
                LEFT JOIN clientes c ON i.id_cliente = c.id_cliente
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
}
