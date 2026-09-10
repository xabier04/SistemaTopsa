<?php
namespace App\Models;

class Ubicacion
{
    public static function catalogo(): array
    {
        static $catalogo;
        return $catalogo ??= json_decode(file_get_contents(dirname(__DIR__, 2) . '/config/el_salvador.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public static function errores(array $data): array
    {
        $catalogo = self::catalogo();
        $departamento = $data['departamento'] ?? '';
        $municipio = $data['municipio'] ?? '';
        $distrito = $data['distrito'] ?? '';
        if (!is_string($departamento) || !isset($catalogo[$departamento])) {
            return ['departamento' => 'Seleccione un departamento de El Salvador.'];
        }
        if (!is_string($municipio) || !isset($catalogo[$departamento][$municipio])) {
            return ['municipio' => 'Seleccione un municipio del departamento indicado.'];
        }
        if (!in_array($distrito, $catalogo[$departamento][$municipio], true)) {
            return ['distrito' => 'Seleccione un distrito del municipio indicado.'];
        }
        return [];
    }
}
