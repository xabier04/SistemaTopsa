<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../core/Database.php';
$db = \Core\Database::getInstance()->getConnection();
$columns = $db->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('requiere_cambio_contrasena', $columns, true)) {
    $db->exec('ALTER TABLE usuarios ADD COLUMN requiere_cambio_contrasena TINYINT(1) NOT NULL DEFAULT 0');
}
echo "Estado de contraseña temporal disponible.\n";
