<?php
$modo = $modo ?? 'listado';
$errores = $_SESSION['_errors'] ?? [];
$filas = $filas ?? [];
$q = $q ?? '';
$areas = $areas ?? [];
$registro = $registro ?? null;

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