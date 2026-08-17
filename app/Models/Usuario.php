<?php

namespace App\Models;

use Core\Model;
use Core\Database;

/**
 * Modelo Usuario
 * 
 * Gestiona la autenticación y datos de usuarios del sistema.
 */
class Usuario extends Model
{
    protected string $table = 'usuarios';
    protected string $primaryKey = 'id_usuario';

    /**
     * Autenticar usuario por correo y contraseña
     *
     * @param string $correo
     * @param string $contrasena
     * @return array|false Datos del usuario o false si falla
     */
    public function authenticate(string $correo, string $contrasena): array|false
    {
        $user = $this->findBy('correo', $correo);

        if (!$user) {
            return false;
        }

        // Verificar estado de la cuenta
        if ($user['estado_de_cuenta'] !== 'Activo') {
            return false;
        }

        // Verificar contraseña
        if (!password_verify($contrasena, $user['contrasena'])) {
            return false;
        }

        return $user;
    }

    /**
     * Crear un usuario con contraseña encriptada
     */
    public function createUser(array $data): int
    {
        $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_BCRYPT);
        return $this->create($data);
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, [
            'contrasena' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);
    }

    /**
     * Obtener usuarios con datos de empleado
     */
    public function allWithEmpleado(): array
    {
        $sql = "SELECT u.*, e.nombre_completo, e.cargo 
                FROM usuarios u 
                LEFT JOIN empleados e ON u.id_empleado = e.id_empleado 
                ORDER BY u.nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }
}
