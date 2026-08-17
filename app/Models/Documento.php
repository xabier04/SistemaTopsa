<?php

namespace App\Models;

use Core\Model;

/**
 * Modelo Documento
 */
class Documento extends Model
{
    protected string $table = 'documentos';
    protected string $primaryKey = 'id_documento';

    /**
     * Obtener documentos con datos del proyecto
     */
    public function allWithProyecto(): array
    {
        $sql = "SELECT d.*, p.nombre_del_proyecto
                FROM documentos d
                LEFT JOIN proyectos p ON d.id_proyecto = p.id_proyecto
                ORDER BY d.fecha_de_subida DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtener documentos de un proyecto
     */
    public function getByProyecto(int $idProyecto): array
    {
        $sql = "SELECT * FROM documentos WHERE id_proyecto = :id ORDER BY fecha_de_subida DESC";
        return $this->db->query($sql, [':id' => $idProyecto])->fetchAll();
    }

    /**
     * Contar documentos por tipo
     */
    public function countByTipo(): array
    {
        $sql = "SELECT tipo_de_documento, COUNT(*) as total FROM documentos GROUP BY tipo_de_documento";
        return $this->db->query($sql)->fetchAll();
    }
}
