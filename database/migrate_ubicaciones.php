<?php
// php database/migrate_ubicaciones.php (aditiva y repetible).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../core/Database.php';
$db = \Core\Database::getInstance()->getConnection();
$columns = $db->query('SHOW COLUMNS FROM inmuebles')->fetchAll(PDO::FETCH_COLUMN);
foreach (['departamento', 'municipio', 'distrito'] as $column) {
    if (!in_array($column, $columns, true)) {
        $db->exec("ALTER TABLE inmuebles ADD COLUMN `$column` VARCHAR(100) DEFAULT NULL");
    }
}
echo "Campos de ubicación disponibles; registros existentes conservados.\n";
