<?php
$modo = $modo ?? $mode ?? 'listado';
$errors = $_SESSION['_errors'] ?? [];
$result = $result ?? ['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
$filters = $filters ?? ['q' => '', 'type_id' => '', 'status_id' => '', 'area_id' => ''];
$types = $types ?? [];
$statuses = $statuses ?? [];
$marcas = $marcas ?? [];
$modelos = $modelos ?? [];
$areas = $areas ?? [];
$ubicaciones = $ubicaciones ?? [];
$proveedores = $proveedores ?? [];
$item = $item ?? null;

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