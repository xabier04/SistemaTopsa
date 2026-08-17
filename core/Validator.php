<?php

namespace Core;

/**
 * Clase Validator — Validación de Datos de Entrada
 * 
 * Proporciona reglas de validación y sanitización con
 * mensajes de error en español.
 */
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validar los datos según reglas definidas
     *
     * @param array $rules Reglas de validación (campo => 'regla1|regla2:param')
     * @return bool true si la validación pasa
     */
    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            $fieldLabel = $this->humanize($field);

            foreach ($rulesArray as $rule) {
                $params = [];

                if (str_contains($rule, ':')) {
                    [$rule, $paramString] = explode(':', $rule, 2);
                    $params = explode(',', $paramString);
                }

                $value = $this->data[$field] ?? null;

                match ($rule) {
                    'required'  => $this->validateRequired($field, $value, $fieldLabel),
                    'max'       => $this->validateMax($field, $value, $fieldLabel, (int) $params[0]),
                    'min'       => $this->validateMin($field, $value, $fieldLabel, (int) $params[0]),
                    'email'     => $this->validateEmail($field, $value, $fieldLabel),
                    'numeric'   => $this->validateNumeric($field, $value, $fieldLabel),
                    'decimal'   => $this->validateDecimal($field, $value, $fieldLabel),
                    'date'      => $this->validateDate($field, $value, $fieldLabel),
                    'in'        => $this->validateIn($field, $value, $fieldLabel, $params),
                    'unique'    => $this->validateUnique($field, $value, $fieldLabel, $params[0], $params[1] ?? null),
                    'phone'     => $this->validatePhone($field, $value, $fieldLabel),
                    'dui'       => $this->validateDui($field, $value, $fieldLabel),
                    default     => null,
                };
            }
        }

        return empty($this->errors);
    }

    /**
     * Campo obligatorio
     */
    private function validateRequired(string $field, mixed $value, string $label): void
    {
        if ($value === null || $value === '' || $value === []) {
            $this->errors[$field] = "El campo {$label} es obligatorio.";
        }
    }

    /**
     * Longitud máxima
     */
    private function validateMax(string $field, mixed $value, string $label, int $max): void
    {
        if ($value !== null && strlen((string) $value) > $max) {
            $this->errors[$field] = "El campo {$label} no debe exceder {$max} caracteres.";
        }
    }

    /**
     * Longitud mínima
     */
    private function validateMin(string $field, mixed $value, string $label, int $min): void
    {
        if ($value !== null && $value !== '' && strlen((string) $value) < $min) {
            $this->errors[$field] = "El campo {$label} debe tener al menos {$min} caracteres.";
        }
    }

    /**
     * Formato de correo electrónico
     */
    private function validateEmail(string $field, mixed $value, string $label): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "El campo {$label} debe ser un correo válido.";
        }
    }

    /**
     * Valor numérico
     */
    private function validateNumeric(string $field, mixed $value, string $label): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field] = "El campo {$label} debe ser numérico.";
        }
    }

    /**
     * Valor decimal
     */
    private function validateDecimal(string $field, mixed $value, string $label): void
    {
        if ($value !== null && $value !== '' && !preg_match('/^\d+(\.\d{1,2})?$/', (string) $value)) {
            $this->errors[$field] = "El campo {$label} debe ser un número decimal válido.";
        }
    }

    /**
     * Formato de fecha
     */
    private function validateDate(string $field, mixed $value, string $label): void
    {
        if ($value !== null && $value !== '') {
            $date = \DateTime::createFromFormat('Y-m-d', (string) $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->errors[$field] = "El campo {$label} debe ser una fecha válida (AAAA-MM-DD).";
            }
        }
    }

    /**
     * Valor dentro de una lista permitida
     */
    private function validateIn(string $field, mixed $value, string $label, array $allowed): void
    {
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = "El valor del campo {$label} no es válido.";
        }
    }

    /**
     * Valor único en una tabla (para validación contra BD)
     */
    private function validateUnique(string $field, mixed $value, string $label, string $table, ?string $excludeId = null): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$field} = :value";
        $params = [':value' => $value];

        if ($excludeId !== null) {
            $pk = 'id_' . rtrim($table, 's');
            $sql .= " AND {$pk} != :exclude";
            $params[':exclude'] = $excludeId;
        }

        $result = $db->query($sql, $params)->fetch();
        if ((int) $result['total'] > 0) {
            $this->errors[$field] = "El valor del campo {$label} ya existe en el sistema.";
        }
    }

    /**
     * Formato de teléfono salvadoreño (8 dígitos con guion: 0000-0000)
     */
    private function validatePhone(string $field, mixed $value, string $label): void
    {
        if ($value !== null && $value !== '' && !preg_match('/^\d{4}-?\d{4}$/', (string) $value)) {
            $this->errors[$field] = "El campo {$label} debe tener formato de teléfono válido (0000-0000).";
        }
    }

    /**
     * Formato de DUI salvadoreño (00000000-0)
     */
    private function validateDui(string $field, mixed $value, string $label): void
    {
        if ($value !== null && $value !== '' && !preg_match('/^\d{8}-\d$/', (string) $value)) {
            $this->errors[$field] = "El campo {$label} debe tener formato de DUI válido (00000000-0).";
        }
    }

    /**
     * Obtener los errores de validación
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener el primer error
     */
    public function firstError(): string
    {
        return !empty($this->errors) ? reset($this->errors) : '';
    }

    /**
     * Convertir nombre de campo a formato legible
     */
    private function humanize(string $field): string
    {
        return str_replace('_', ' ', $field);
    }

    /**
     * Sanitizar un string contra XSS
     */
    public static function sanitize(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar un array completo
     */
    public static function sanitizeArray(array $data): array
    {
        return array_map(fn($value) => is_string($value) ? self::sanitize($value) : $value, $data);
    }
}
