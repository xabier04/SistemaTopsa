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

    public function saldos(): array
    {
        return $this->db->query('SELECT p.*, c.nombre AS nombre_cliente,
            COALESCE(t.abonado, 0) AS total_abonado,
            p.presupuesto_inicial - COALESCE(t.abonado, 0) AS saldo_pendiente
            FROM proyectos p JOIN clientes c ON c.id_cliente = p.id_cliente
            LEFT JOIN (SELECT id_proyecto, SUM(monto_abonado) AS abonado FROM transacciones GROUP BY id_proyecto) t
            ON t.id_proyecto = p.id_proyecto ORDER BY c.nombre, p.nombre_del_proyecto')->fetchAll();
    }

    public function registrar(array $data): int
    {
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', (string) ($data['monto_abonado'] ?? ''))
            || (float) $data['monto_abonado'] <= 0) {
            throw new \DomainException('Ingrese un monto positivo con un máximo de dos decimales.');
        }
        $this->db->beginTransaction();
        try {
            $project = $this->db->query('SELECT * FROM proyectos WHERE id_proyecto = ? FOR UPDATE', [(int) $data['id_proyecto']])->fetch();
            if (!$project) throw new \DomainException('El proyecto no existe.');
            $existing = $this->findBy('referencia', $data['referencia']);
            if ($existing) {
                if ((int) $existing['id_proyecto'] !== (int) $data['id_proyecto']
                    || (float) $existing['monto_abonado'] !== (float) $data['monto_abonado']
                    || $existing['fecha_de_pago'] !== $data['fecha_de_pago']
                    || $existing['tipo_de_transaccion'] !== $data['tipo_de_transaccion']) {
                    throw new \DomainException('Este formulario ya registró otro pago. Abra un nuevo formulario.');
                }
                $this->db->commit(); return (int) $existing['id_transaccion'];
            }
            $paid = $this->db->query('SELECT COALESCE(SUM(monto_abonado), 0) FROM transacciones WHERE id_proyecto = ?', [$project['id_proyecto']])->fetchColumn();
            $balance = (int) round((float) $project['presupuesto_inicial'] * 100) - (int) round((float) $paid * 100);
            $amount = (int) round((float) $data['monto_abonado'] * 100);
            if ($amount > $balance) throw new \DomainException('El pago supera el saldo pendiente del proyecto.');
            $data['saldo_pendiente'] = number_format(($balance - $amount) / 100, 2, '.', '');
            $id = $this->create($data);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function comprobante(int $id): array|false
    {
        return $this->db->query('SELECT t.*, p.nombre_del_proyecto, c.nombre AS nombre_cliente,
            c.dui, c.correo_electronico FROM transacciones t JOIN proyectos p ON p.id_proyecto = t.id_proyecto
            JOIN clientes c ON c.id_cliente = p.id_cliente WHERE t.id_transaccion = ?', [$id])->fetch();
    }

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
