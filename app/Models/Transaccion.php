<?php

namespace App\Models;

use Core\Model;

/**
 * Modelo Transaccion
 */
class Transaccion extends Model
{
    protected string $table = 'transacciones';
    protected string $primaryKey = 'id_transaccion';

    /**
     * Obtener transacciones con datos del proyecto
     */
    public function allWithProyecto(): array
    {
        $sql = "SELECT t.*, p.nombre_del_proyecto, c.nombre as nombre_cliente
                FROM transacciones t
                LEFT JOIN proyectos p ON t.id_proyecto = p.id_proyecto
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                ORDER BY t.fecha_de_pago DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Obtener transacciones de un proyecto específico
     */
    public function getByProyecto(int $idProyecto): array
    {
        $sql = "SELECT * FROM transacciones WHERE id_proyecto = :id ORDER BY fecha_de_pago DESC";
        return $this->db->query($sql, [':id' => $idProyecto])->fetchAll();
    }

    /**
     * Calcular saldo pendiente de un proyecto
     */
    public function calcularSaldo(int $idProyecto): float
    {
        $sql = "SELECT p.presupuesto_inicial, COALESCE(SUM(t.monto_abonado), 0) as total_abonado
                FROM proyectos p
                LEFT JOIN transacciones t ON p.id_proyecto = t.id_proyecto
                WHERE p.id_proyecto = :id
                GROUP BY p.id_proyecto";
        $result = $this->db->query($sql, [':id' => $idProyecto])->fetch();

        if (!$result) return 0;
        return (float) $result['presupuesto_inicial'] - (float) $result['total_abonado'];
    }

    /**
     * Total de ingresos por mes (para reportes)
     */
    public function ingresosPorMes(int $year): array
    {
        $sql = "SELECT MONTH(fecha_de_pago) as mes, SUM(monto_abonado) as total
                FROM transacciones
                WHERE YEAR(fecha_de_pago) = :year
                GROUP BY MONTH(fecha_de_pago)
                ORDER BY mes";
        return $this->db->query($sql, [':year' => $year])->fetchAll();
    }

    /**
     * Total general de ingresos
     */
    public function totalIngresos(): float
    {
        $sql = "SELECT COALESCE(SUM(monto_abonado), 0) as total FROM transacciones";
        $result = $this->db->query($sql)->fetch();
        return (float) $result['total'];
    }
}
