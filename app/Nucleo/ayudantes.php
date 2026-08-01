<?php
declare(strict_types=1);

use App\Nucleo\Config;
use App\Nucleo\Csrf;

function config(string $clave, mixed $default = null): mixed
{
    return Config::obtener($clave, $default);
}

function url(string $ruta = ''): string
{
    $base = rtrim((string) config('aplicacion.url_base'), '/');

    return $base.($ruta !== '' ? '/'.ltrim($ruta, '/') : '');
}

function recurso(string $ruta): string
{
    return url('recursos/'.ltrim($ruta, '/'));
}

function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function old(string $clave, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$clave] ?? $default;
}

function selected(mixed $actual, mixed $esperado): string
{
    return (string) $actual === (string) $esperado ? 'selected' : '';
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="'.e(Csrf::token()).'">';
}

function redirect(string $ruta): never
{
    header('Location: '.url($ruta));
    exit;
}

function abort(int $codigo, string $mensaje = ''): never
{
    http_response_code($codigo);
    echo '<h1>Error '.$codigo.'</h1><p>'.e($mensaje ?: 'No se pudo completar la solicitud.').'</p>';
    exit;
}

function date_pe(?string $fecha): string
{
    return format_date_pe($fecha, 'd/m/Y');
}

function datetime_pe(?string $fecha): string
{
    return format_date_pe($fecha, 'd/m/Y H:i');
}

function format_date_pe(?string $fecha, string $formato): string
{
    if ($fecha === null || trim($fecha) === '') {
        return '-';
    }

    $marcaTiempo = strtotime($fecha);

    return $marcaTiempo === false ? '-' : date($formato, $marcaTiempo);
}

function money(mixed $valor): string
{
    if ($valor === '' || $valor === null) {
        return '-';
    }

    return 'S/ '.number_format((float) $valor, 2);
}

function badge(string $estado): string
{
    $colores = [
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

    $color = $colores[strtoupper($estado)] ?? 'secondary';

    return '<span class="badge badge-'.$color.'">'.e($estado).'</span>';
}
