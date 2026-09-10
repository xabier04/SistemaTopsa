<?php
// Ejecutar únicamente por CLI: php database/migrate_avaluos.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../core/Database.php';
$db = \Core\Database::getInstance()->getConnection();
$sql = file_get_contents(__DIR__ . '/migrations/20260906_avaluos.sql');
$db->exec($sql);
echo "Migración de avalúos aplicada (tablas aditivas, sin eliminar registros).\n";
