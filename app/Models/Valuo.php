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
        $sql = "SELECT v.*, p.nombre_del_proyecto, c.nombre as nombre_cliente, e.referencia, e.estado
                FROM valuos v
                LEFT JOIN valuo_expedientes e ON e.id_valuo = v.id_valuo
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

    public function expediente(int $id): array|false
    {
        $row = $this->db->query('SELECT v.*, e.datos, e.resultados, e.revision, e.estado, e.referencia
            FROM valuos v LEFT JOIN valuo_expedientes e ON e.id_valuo = v.id_valuo WHERE v.id_valuo = :id', [':id' => $id])->fetch();
        if ($row && $row['datos']) {
            $row['datos'] = json_decode($row['datos'], true, 512, JSON_THROW_ON_ERROR);
            $row['resultados'] = json_decode($row['resultados'], true, 512, JSON_THROW_ON_ERROR);
        }
        return $row;
    }

    public function guardarExpediente(array $d, array $r, ?int $id): int
    {
        $this->db->beginTransaction();
        try {
            $revision = 1;
            if ($id !== null) {
                $current = $this->db->query('SELECT v.id_valuo, e.revision FROM valuos v LEFT JOIN valuo_expedientes e ON e.id_valuo=v.id_valuo WHERE v.id_valuo=:id FOR UPDATE', [':id' => $id])->fetch();
                if (!$current || (int) ($current['revision'] ?? 0) !== (int) ($d['revision'] ?? 0)) {
                    throw new \InvalidArgumentException('El avalúo cambió en otra ventana o ya no existe. Abra la versión actual antes de guardar.');
                }
                $revision = (int) ($current['revision'] ?? 0) + 1;
            }
            $base = ['id_proyecto' => (int) $d['id_proyecto'], 'fecha_del_valuo' => $d['fecha_del_valuo'],
                'monto_estimado' => round($r['adoptado'] ?? $r['calculado'], 2), 'observaciones' => $d['conclusion']];
            if ($id === null) {
                $id = $this->create($base);
            } else {
                $this->update($id, $base);
            }
            $this->db->query('INSERT INTO valuo_expedientes (id_valuo, referencia, estado, version_calculo, datos, resultados, revision)
                VALUES (:id, :ref, :estado, :version, :datos, :resultados, :revision)
                ON DUPLICATE KEY UPDATE referencia=VALUES(referencia), estado=VALUES(estado), version_calculo=VALUES(version_calculo),
                    datos=VALUES(datos), resultados=VALUES(resultados), revision=VALUES(revision)',
                [':id' => $id, ':ref' => $d['referencia'], ':estado' => $d['estado'], ':version' => ValuoCalculo::VERSION,
                 ':datos' => json_encode($d, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                 ':resultados' => json_encode($r, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), ':revision' => $revision]);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function anexos(int $id): array
    {
        return $this->db->query('SELECT * FROM valuo_anexos WHERE id_valuo=:id ORDER BY id_anexo', [':id' => $id])->fetchAll();
    }

    public function anexo(int $id): array|false
    {
        return $this->db->query('SELECT * FROM valuo_anexos WHERE id_anexo=:id', [':id' => $id])->fetch();
    }

    public function guardarAnexo(int $id, string $nombre, string $archivo, string $tipo, string $descripcion): void
    {
        $this->db->query('INSERT INTO valuo_anexos (id_valuo,nombre,archivo,tipo,descripcion) VALUES (:id,:nombre,:archivo,:tipo,:descripcion)',
            [':id' => $id, ':nombre' => $nombre, ':archivo' => $archivo, ':tipo' => $tipo, ':descripcion' => $descripcion]);
    }
}
