<?php
$modo = $modo ?? $mode ?? 'listado';
$rows = $rows ?? [];
$trabajadores = $trabajadores ?? [];
$activos = $activos ?? [];
$item = $item ?? null;

$parciales = [
    'listado' => 'index',
    'detalle' => 'detalle',
    'formulario' => 'formulario',
];

$parcial = $parciales[$modo] ?? $parciales['listado'];
$rutaParcial = __DIR__.'/asignaciones/'.$parcial.'.php';

if (!is_file($rutaParcial)) {
    abort(500, 'Vista no encontrada: asignaciones/'.$parcial);
}

require $rutaParcial;