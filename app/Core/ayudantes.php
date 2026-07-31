<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

function url(string $path = ''): string
{
    $base = rtrim((string) config('app.base_url'), '/');

    return $base.($path !== '' ? '/'.ltrim($path, '/') : '');
}

function asset(string $path): string
{
    return url('activos/'.ltrim($path, '/'));
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function selected(mixed $current, mixed $expected): string
{
    return (string) $current === (string) $expected ? 'selected' : '';
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="'.e(Csrf::token()).'">';
}

function redirect(string $path): never
{
    header('Location: '.url($path));
    exit;
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    echo '<h1>Error '.$code.'</h1><p>'.e($message ?: 'No se pudo completar la solicitud.').'</p>';
    exit;
}

function date_pe(?string $date): string
{
    return format_date_pe($date, 'd/m/Y');
}

function datetime_pe(?string $date): string
{
    return format_date_pe($date, 'd/m/Y H:i');
}

function format_date_pe(?string $date, string $format): string
{
    if ($date === null || trim($date) === '') {
        return '—';
    }

    $timestamp = strtotime($date);

    return $timestamp === false ? '—' : date($format, $timestamp);
}

function money(mixed $value): string
{
    if ($value === '' || $value === null) {
        return '—';
    }

    return 'S/ '.number_format((float) $value, 2);
}

function badge(string $status): string
{
    $colors = [
        'DISPONIBLE' => 'success',
        'ASIGNADO' => 'primary',
        'MANTENIMIENTO' => 'warning',
        'REPARACION' => 'danger',
        'CONFIRMADA' => 'success',
        'PARCIAL' => 'warning',
        'CERRADA' => 'dark',
        'ABIERTO' => 'warning',
        'CERRADO' => 'success',
    ];

    $color = $colors[strtoupper($status)] ?? 'secondary';

    return '<span class="badge badge-'.$color.'">'.e($status).'</span>';
}
