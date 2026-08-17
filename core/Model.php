<?php

namespace Core;

/**
 * Clase Model — Modelo Base Abstracto
 * 
 * Proporciona operaciones CRUD genéricas con sentencias preparadas.
 * Cada modelo hijo define su tabla y clave primaria.
 */
abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todos los registros
     *
     * @param string $orderBy  Columna de ordenamiento
     * @param string $direction Dirección (ASC o DESC)
     * @return array
     */
    public function all(string $orderBy = '', string $direction = 'ASC'): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($orderBy)) {
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY {$orderBy} {$direction}";
        }

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Buscar un registro por su clave primaria
     *
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->query($sql, [':id' => $id])->fetch();
    }

    /**
     * Buscar registros por una condición
     *
     * @param string $column  Columna a filtrar
     * @param mixed  $value   Valor a buscar
     * @param string $orderBy Columna de ordenamiento
     * @return array
     */
    public function where(string $column, mixed $value, string $orderBy = ''): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";

        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }

        return $this->db->query($sql, [':value' => $value])->fetchAll();
    }

    /**
     * Buscar un único registro por una condición
     *
     * @param string $column Columna a filtrar
     * @param mixed  $value  Valor a buscar
     * @return array|false
     */
    public function findBy(string $column, mixed $value): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        return $this->db->query($sql, [':value' => $value])->fetch();
    }

    /**
     * Crear un nuevo registro
     *
     * @param array $data Datos del registro (columna => valor)
     * @return int ID del registro creado
     */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $params = [];
        foreach ($data as $key => $value) {
            $params[":{$key}"] = $value;
        }

        $this->db->query($sql, $params);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar un registro existente
     *
     * @param int   $id   ID del registro
     * @param array $data Datos a actualizar (columna => valor)
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $setParts = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }

        $setClause = implode(', ', $setParts);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";

        $this->db->query($sql, $params);
        return true;
    }

    /**
     * Eliminar un registro
     *
     * @param int $id ID del registro
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $this->db->query($sql, [':id' => $id]);
        return true;
    }

    /**
     * Contar registros (total o con filtro)
     *
     * @param string $column Columna de filtro (opcional)
     * @param mixed  $value  Valor del filtro (opcional)
     * @return int
     */
    public function count(string $column = '', mixed $value = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($column) && $value !== null) {
            $sql .= " WHERE {$column} = :value";
            $params[':value'] = $value;
        }

        $result = $this->db->query($sql, $params)->fetch();
        return (int) $result['total'];
    }

    /**
     * Ejecutar una consulta SQL personalizada
     *
     * @param string $sql    Consulta SQL
     * @param array  $params Parámetros
     * @return array
     */
    public function raw(string $sql, array $params = []): array
    {
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Verificar si un valor existe en una columna (para validación de unicidad)
     *
     * @param string   $column   Columna a verificar
     * @param mixed    $value    Valor a buscar
     * @param int|null $excludeId ID a excluir (para edición)
     * @return bool
     */
    public function exists(string $column, mixed $value, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$column} = :value";
        $params = [':value' => $value];

        if ($excludeId !== null) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $result = $this->db->query($sql, $params)->fetch();
        return (int) $result['total'] > 0;
    }
}
