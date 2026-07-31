<?php
$mode = $mode ?? 'index';
$rows = $rows ?? [];
$activos = $activos ?? [];

$partials = [
    'index' => 'index',
    'form' => 'formulario',
];

$partial = $partials[$mode] ?? $partials['index'];
$partialPath = __DIR__.'/mantenimientos/'.$partial.'.php';

if (!is_file($partialPath)) {
    abort(500, 'Vista no encontrada: mantenimientos/'.$partial);
}

require $partialPath;