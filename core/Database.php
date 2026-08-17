<?php

namespace Core;

/**
 * Clase Database — Conexión PDO Singleton
 * 
 * Garantiza una única instancia de conexión a la base de datos
 * durante todo el ciclo de vida de la petición.
 */
class Database
{
    private static ?Database $instance = null;
    private \PDO $pdo;

    /**
     * Constructor privado — patrón Singleton
     */
    private function __construct()
    {
        $config = require dirname(__DIR__) . '/config/database.php';

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new \PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (\PDOException $e) {
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            die('Error interno del servidor. Contacte al administrador.');
        }
    }

    /**
     * Evitar la clonación del objeto
     */
    private function __clone() {}

    /**
     * Evitar la deserialización del objeto
     */
    public function __wakeup()
    {
        throw new \Exception('No se puede deserializar una instancia Singleton.');
    }

    /**
     * Obtener la instancia única de Database
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Ejecutar una consulta con sentencias preparadas
     *
     * @param string $sql      Consulta SQL con placeholders
     * @param array  $params   Parámetros para la sentencia preparada
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            error_log('Error en consulta SQL: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Iniciar una transacción
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirmar una transacción
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Revertir una transacción
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
