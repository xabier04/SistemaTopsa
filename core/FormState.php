<?php
namespace Core;

/** Datos de un intento fallido, separados por formulario y registro. */
class FormState
{
    public static function save(string $action, array $data, array $errors): void
    {
        unset($data['_csrf_token']);
        foreach ($errors as $field => $message) {
            $data[$field] = '';
        }
        $_SESSION['form_state'][$action] = ['values' => $data, 'errors' => $errors];
    }

    public static function take(string $action): array
    {
        $state = $_SESSION['form_state'][$action] ?? [];
        unset($_SESSION['form_state'][$action]);
        return $state;
    }
}
