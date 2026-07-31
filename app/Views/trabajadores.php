<?php
$modo = $modo ?? $mode ?? 'listado';
$errors = $_SESSION['_errors'] ?? [];
$rows = $rows ?? [];
$q = $q ?? '';
$areas = $areas ?? [];
$item = $item ?? null;

$parciales = [
    'listado' => 'index',
    'formulario' => 'formulario',
];

$parcial = $parciales[$modo] ?? $parciales['listado'];
$rutaParcial = __DIR__.'/trabajadores/'.$parcial.'.php';

if (!is_file($rutaParcial)) {
    abort(500, 'Vista no encontrada: trabajadores/'.$parcial);
}

require $rutaParcial;