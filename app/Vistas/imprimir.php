<?php
$doc = $doc ?? '';
$registro = $registro ?? [];
$esAsignacion = $doc === 'assignment';
$fecha = $esAsignacion ? ($registro['assigned_at'] ?? '') : ($registro['returned_at'] ?? '');

$fechaCorta = static function (?string $valor): string {
    if (!$valor || strtotime($valor) === false) {
        return '-';
    }

    return date('d/m/y', strtotime($valor));
};

$limpiar = static function (mixed $valor, string $default = '-'): string {
    $texto = trim((string) $valor);

    return $texto !== '' ? $texto : $default;
};

$mayusculas = static function (mixed $valor) use ($limpiar): string {
    return mb_strtoupper($limpiar($valor), 'UTF-8');
};

$textoActivo = static function (?array $activo, string $clave, string $default = '-') use ($limpiar): string {
    return $activo ? $limpiar($activo[$clave] ?? '', $default) : $default;
};

$claseCelda = static function (mixed $valor): string {
    return trim((string) $valor) === '-' ? ' dash' : '';
};

$descripcionActivo = static function (?array $activo) use ($limpiar): string {
    if (!$activo) {
        return '-';
    }

    $partes = array_filter([
        $activo['type_name'] ?? '',
        $activo['brand_name'] ?? '',
        $activo['model_name'] ?? '',
    ], static fn ($valor): bool => trim((string) $valor) !== '');

    return $limpiar(implode(' ', $partes));
};

$observacionesActivo = static function (?array $activo, array $asignacion, string $default = '-') use ($limpiar): string {
    if (!$activo) {
        return $default;
    }

    $partes = [];

    foreach (['specs_text', 'asset_notes'] as $clave) {
        $texto = trim((string) ($activo[$clave] ?? ''));

        if ($texto !== '') {
            $partes[] = $texto;
        }
    }

    if (!$partes) {
        $nota = trim((string) ($asignacion['notes'] ?? ''));

        if ($nota !== '') {
            $partes[] = $nota;
        }
    }

    return $limpiar(implode(', ', array_unique($partes)), $default);
};

$esCelular = static function (array $activo): bool {
    $texto = mb_strtoupper(implode(' ', [
        $activo['type_name'] ?? '',
        $activo['brand_name'] ?? '',
        $activo['model_name'] ?? '',
        $activo['phone_number'] ?? '',
        $activo['imei1'] ?? '',
        $activo['imei2'] ?? '',
    ]), 'UTF-8');

    if (trim((string) ($activo['phone_number'] ?? '')) !== '' || trim((string) ($activo['imei1'] ?? '')) !== '' || trim((string) ($activo['imei2'] ?? '')) !== '') {
        return true;
    }

    foreach (['CELULAR', 'SMARTPHONE', 'TELEFONO', 'MOVIL', 'SIM CARD', 'SIM'] as $aguja) {
        if (str_contains($texto, $aguja)) {
            return true;
        }
    }

    return false;
};

require __DIR__.'/imprimir/estilos.php';

$logoSrc = '';
$logos = [
    dirname(__DIR__, 2).'/public/recursos/img/solandra-logo.png',
    dirname(__DIR__, 2).'/public/recursos/img/solandra-logo.jpg',
    dirname(__DIR__, 2).'/public/recursos/img/solandra-logo.jpeg',
];

foreach ($logos as $logoPath) {
    if (!is_file($logoPath)) {
        continue;
    }

    $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
    $mime = $extension === 'png' ? 'image/png' : 'image/jpeg';
    $logoSrc = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($logoPath));
    break;
}

if (!empty($pdf)) {
    echo $assignmentCss;
}

if (empty($pdf)) {
    require __DIR__.'/imprimir/acciones.php';
}

require $esAsignacion
    ? __DIR__.'/imprimir/asignacion.php'
    : __DIR__.'/imprimir/devolucion.php';