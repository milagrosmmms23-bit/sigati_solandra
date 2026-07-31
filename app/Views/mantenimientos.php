<?php
$modo = $modo ?? $mode ?? 'listado';
$rows = $rows ?? [];
$activos = $activos ?? [];

$parciales = [
    'listado' => 'index',
    'formulario' => 'formulario',
];

$parcial = $parciales[$modo] ?? $parciales['listado'];
$rutaParcial = __DIR__.'/mantenimientos/'.$parcial.'.php';

if (!is_file($rutaParcial)) {
    abort(500, 'Vista no encontrada: mantenimientos/'.$parcial);
}

require $rutaParcial;