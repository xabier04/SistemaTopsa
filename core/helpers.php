<?php

/**
 * Funciones utilitarias globales
 * Sistema TOPSA — Oficina de Ingeniería Civil
 */

use Core\Session;
use Core\Validator;

/**
 * Escapar contenido para prevenir XSS
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Generar URL completa del sistema
 */
function url(string $path = ''): string
{
    $config = require dirname(__DIR__) . '/config/app.php';
    return rtrim($config['base_url'], '/') . '/' . ltrim($path, '/');
}

/**
 * Generar URL para assets (CSS, JS, imágenes)
 */
function asset(string $path): string
{
    return url($path);
}

/**
 * Generar campo oculto con token CSRF
 */
function csrf_field(): string
{
    $token = Session::generateCsrfToken();
    return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
}

/**
 * Obtener el token CSRF actual
 */
function csrf_token(): string
{
    return Session::generateCsrfToken();
}

/**
 * Formatear fecha al formato local (dd/mm/aaaa)
 */
function formatDate(?string $date): string
{
    if (empty($date)) return '—';
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt ? $dt->format('d/m/Y') : $date;
}

/**
 * Formatear hora (HH:MM)
 */
function formatTime(?string $time): string
{
    if (empty($time)) return '—';
    return substr($time, 0, 5);
}

/**
 * Formatear monto monetario
 */
function formatMoney(mixed $amount): string
{
    return '$' . number_format((float) $amount, 2, '.', ',');
}

/**
 * Formatear porcentaje
 */
function formatPercent(mixed $value): string
{
    return number_format((float) $value, 2) . '%';
}

/**
 * Obtener la clase CSS para un estado de proyecto
 */
function estadoClass(string $estado): string
{
    return match (strtolower($estado)) {
        'en proceso', 'en_proceso' => 'badge-info',
        'incompleto'               => 'badge-warning',
        'finalizado'               => 'badge-success',
        'activo'                   => 'badge-success',
        'inactivo'                 => 'badge-danger',
        'bloqueado'                => 'badge-danger',
        default                    => 'badge-secondary',
    };
}

/**
 * Verificar si la ruta actual coincide (para active state del sidebar)
 */
function isActiveRoute(string $route): string
{
    $currentUrl = $_GET['url'] ?? '';
    return str_starts_with($currentUrl, $route) ? 'active' : '';
}

/**
 * Truncar texto largo
 */
function truncate(string $text, int $length = 50): string
{
    if (strlen($text) <= $length) return e($text);
    return e(substr($text, 0, $length)) . '...';
}

/**
 * Obtener las iniciales de un nombre (para avatares)
 */
function initials(string $name): string
{
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $initials;
}

/**
 * Generar un color hexadecimal consistente basado en un string
 */
function stringToColor(string $str): string
{
    $hash = crc32($str);
    $hue = abs($hash) % 360;
    return "hsl({$hue}, 60%, 45%)";
}
