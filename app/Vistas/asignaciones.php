<?php
$modo = $modo ?? 'listado';
$filas = $filas ?? [];
$trabajadores = $trabajadores ?? [];
$activos = $activos ?? [];
$registro = $registro ?? null;

$parciales = [
    'listado' => 'index',
    'detalle' => 'detalle',
    'formulario' => 'formulario',
    'importar' => 'importar',
];

$parcial = $parciales[$modo] ?? $parciales['listado'];
$rutaParcial = __DIR__.'/asignaciones/'.$parcial.'.php';

if (!is_file($rutaParcial)) {
    abort(500, 'Vista no encontrada: asignaciones/'.$parcial);
}

require $rutaParcial;