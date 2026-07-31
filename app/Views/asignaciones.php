<?php
$mode = $mode ?? 'index';
$rows = $rows ?? [];
$trabajadores = $trabajadores ?? [];
$activos = $activos ?? [];
$item = $item ?? null;

$partials = [
    'index' => 'index',
    'show' => 'detalle',
    'form' => 'formulario',
];

$partial = $partials[$mode] ?? $partials['index'];
$partialPath = __DIR__.'/asignaciones/'.$partial.'.php';

if (!is_file($partialPath)) {
    abort(500, 'Vista no encontrada: asignaciones/'.$partial);
}

require $partialPath;