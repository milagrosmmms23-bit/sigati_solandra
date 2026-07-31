<?php
$mode = $mode ?? 'index';
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

$partials = [
    'index' => 'index',
    'import' => 'importar',
    'show' => 'detalle',
    'form' => 'formulario',
];

$partial = $partials[$mode] ?? $partials['index'];
$partialPath = __DIR__.'/activos/'.$partial.'.php';

if (!is_file($partialPath)) {
    abort(500, 'Vista no encontrada: activos/'.$partial);
}

require $partialPath;