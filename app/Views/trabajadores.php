<?php
$mode = $mode ?? 'index';
$errors = $_SESSION['_errors'] ?? [];
$rows = $rows ?? [];
$q = $q ?? '';
$areas = $areas ?? [];
$item = $item ?? null;

$partials = [
    'index' => 'index',
    'form' => 'formulario',
];

$partial = $partials[$mode] ?? $partials['index'];
$partialPath = __DIR__.'/trabajadores/'.$partial.'.php';

if (!is_file($partialPath)) {
    abort(500, 'Vista no encontrada: trabajadores/'.$partial);
}

require $partialPath;