<?php
$modo = $modo ?? 'listado';
$errores = $_SESSION['_errors'] ?? [];
$resultado = $resultado ?? ['filas' => [], 'total' => 0, 'pagina' => 1, 'paginas' => 1];
$resumen = $resumen ?? [];
$filtros = $filtros ?? ['q' => '', 'type_id' => '', 'estado_id' => '', 'area_id' => ''];
$tipos = $tipos ?? [];
$estados = $estados ?? [];
$marcas = $marcas ?? [];
$modelos = $modelos ?? [];
$areas = $areas ?? [];
$ubicaciones = $ubicaciones ?? [];
$proveedores = $proveedores ?? [];
$registro = $registro ?? null;

$parciales = [
    'listado' => 'index',
    'importar' => 'importar',
    'detalle' => 'detalle',
    'formulario' => 'formulario',
];

$parcial = $parciales[$modo] ?? $parciales['listado'];
$rutaParcial = __DIR__.'/activos/'.$parcial.'.php';

if (!is_file($rutaParcial)) {
    abort(500, 'Vista no encontrada: activos/'.$parcial);
}

require $rutaParcial;
