<?php

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Database;

/**
 * Controlador de Backups (solo Administrador)
 */
class BackupController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getRequiredRole(string $action): ?string
    {
        return 'Administrador';
    }

    public function index(): void
    {
        $backupDir = $this->appConfig['paths']['backups'];
        $backups = [];

        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.sql');
            foreach ($files as $file) {
                $backups[] = [
                    'nombre'  => basename($file),
                    'tamano'  => filesize($file),
                    'fecha'   => date('Y-m-d H:i:s', filemtime($file)),
                ];
            }
            // Ordenar por fecha descendente
            usort($backups, fn($a, $b) => strtotime($b['fecha']) - strtotime($a['fecha']));
        }

        $this->view('backups/index', [
            'pageTitle'  => 'Copias de Seguridad',
            'pageScript' => 'backups',
            'backups'    => $backups,
        ]);
    }

    /**
     * Generar backup de la base de datos
     */
    public function generar(): void
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $backupDir = $this->appConfig['paths']['backups'];

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $fileName = 'topsa_backup_' . date('Y-m-d_His') . '.sql';
        $filePath = $backupDir . '/' . $fileName;

        // Construir comando mysqldump
        $command = sprintf(
            'mysqldump --host=%s --port=%d --user=%s %s %s > %s 2>&1',
            escapeshellarg($config['host']),
            $config['port'],
            escapeshellarg($config['username']),
            !empty($config['password']) ? '--password=' . escapeshellarg($config['password']) : '',
            escapeshellarg($config['database']),
            escapeshellarg($filePath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($filePath) && filesize($filePath) > 0) {
            $this->logActivity('Generó copia de seguridad: ' . $fileName, 'backups');
            Session::flash('success', 'Backup generado exitosamente: ' . $fileName);
        } else {
            // Intento alternativo: backup via PHP/PDO
            $this->backupWithPDO($filePath);
        }

        $this->redirect('backup/index');
    }

    /**
     * Backup alternativo usando PDO (por si mysqldump no está disponible)
     */
    private function backupWithPDO(string $filePath): void
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            $sql = "-- Backup TOPSA DB\n-- Fecha: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // CREATE TABLE
                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $createStmt['Create Table'] . ";\n\n";

                // INSERT DATA
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll();
                foreach ($rows as $row) {
                    $values = array_map(function ($val) use ($pdo) {
                        return $val === null ? 'NULL' : $pdo->quote($val);
                    }, array_values($row));
                    $sql .= "INSERT INTO `{$table}` VALUES (" . implode(',', $values) . ");\n";
                }
                $sql .= "\n";
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            file_put_contents($filePath, $sql);

            $this->logActivity('Generó backup (PDO): ' . basename($filePath), 'backups');
            Session::flash('success', 'Backup generado exitosamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al generar el backup: ' . $e->getMessage());
        }
    }

    /**
     * Descargar un backup
     */
    public function descargar(string $nombre = ''): void
    {
        $filePath = $this->appConfig['paths']['backups'] . '/' . basename($nombre);

        if (!file_exists($filePath)) {
            Session::flash('error', 'Backup no encontrado.');
            $this->redirect('backup/index');
            return;
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Eliminar un backup (AJAX)
     */
    public function eliminar(): void
    {
        if (!$this->isPost() || !\Core\Router::isAjax()) {
            $this->json(['success' => false, 'message' => 'Petición inválida.'], 400);
            return;
        }

        $nombre = $this->input('nombre', '');
        $filePath = $this->appConfig['paths']['backups'] . '/' . basename($nombre);

        if (file_exists($filePath)) {
            unlink($filePath);
            $this->logActivity('Eliminó backup: ' . $nombre, 'backups');
            $this->json(['success' => true, 'message' => 'Backup eliminado.']);
        } else {
            $this->json(['success' => false, 'message' => 'Archivo no encontrado.'], 404);
        }
    }
}
