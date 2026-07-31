<?php
$mode = $mode ?? 'index';
$rows = $rows ?? [];
$asignaciones = $asignaciones ?? [];
$assignment = $assignment ?? null;
$statuses = $statuses ?? [];
$item = $item ?? null;

$partials = [
    'index' => 'index',
    'show' => 'detalle',
    'form' => 'formulario',
];

$partial = $partials[$mode] ?? $partials['index'];
$partialPath = __DIR__.'/devoluciones/'.$partial.'.php';

if (!is_file($partialPath)) {
    abort(500, 'Vista no encontrada: devoluciones/'.$partial);
}

require $partialPath;