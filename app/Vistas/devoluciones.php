<?php
$modo = $modo ?? $mode ?? 'listado';
$rows = $rows ?? [];
$asignaciones = $asignaciones ?? [];
$assignment = $assignment ?? null;
$statuses = $statuses ?? [];
$item = $item ?? null;

$parciales = [
    'listado' => 'index',
    'detalle' => 'detalle',
    'formulario' => 'formulario',
];

$parcial = $parciales[$modo] ?? $parciales['listado'];
$rutaParcial = __DIR__.'/devoluciones/'.$parcial.'.php';

if (!is_file($rutaParcial)) {
    abort(500, 'Vista no encontrada: devoluciones/'.$parcial);
}

require $rutaParcial;