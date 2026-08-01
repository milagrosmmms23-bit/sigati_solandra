<?php
$doc = $doc ?? '';
$registro = $registro ?? [];
$isAssignment = $doc === 'assignment';
$fecha = $isAssignment ? ($registro['assigned_at'] ?? '') : ($registro['returned_at'] ?? '');

$shortDate = static function (?string $valor): string {
    if (!$valor || strtotime($valor) === false) {
        return '-';
    }

    return date('d/m/y', strtotime($valor));
};

$clean = static function (mixed $valor, string $default = '-'): string {
    $text = trim((string) $valor);

    return $text !== '' ? $text : $default;
};

$upper = static function (mixed $valor) use ($clean): string {
    return mb_strtoupper($clean($valor), 'UTF-8');
};

$assetText = static function (?array $activo, string $clave, string $default = '-') use ($clean): string {
    return $activo ? $clean($activo[$clave] ?? '', $default) : $default;
};

$cellClass = static function (mixed $valor): string {
    return trim((string) $valor) === '-' ? ' dash' : '';
};

$assetDescription = static function (?array $activo) use ($clean): string {
    if (!$activo) {
        return '-';
    }

    $parts = array_filter([
        $activo['type_name'] ?? '',
        $activo['brand_name'] ?? '',
        $activo['model_name'] ?? '',
    ], static fn ($valor): bool => trim((string) $valor) !== '');

    return $clean(implode(' ', $parts));
};

$assetObservations = static function (?array $activo, array $asignacion, string $default = '-') use ($clean): string {
    if (!$activo) {
        return $default;
    }

    $parts = [];

    foreach (['specs_text', 'asset_notes'] as $clave) {
        $text = trim((string) ($activo[$clave] ?? ''));

        if ($text !== '') {
            $parts[] = $text;
        }
    }

    if (!$parts) {
        $note = trim((string) ($asignacion['notes'] ?? ''));

        if ($note !== '') {
            $parts[] = $note;
        }
    }

    return $clean(implode(', ', array_unique($parts)), $default);
};

$isPhoneAsset = static function (array $activo): bool {
    $text = mb_strtoupper(implode(' ', [
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

    foreach (['CELULAR', 'SMARTPHONE', 'TELEFONO', 'TELÃ‰FONO', 'MOVIL', 'MÃ“VIL', 'SIM CARD', 'SIM'] as $needle) {
        if (str_contains($text, $needle)) {
            return true;
        }
    }

    return false;
};

require __DIR__.'/imprimir/estilos.php';

$logoSrc = '';
$logoCandidates = [
    dirname(__DIR__, 2).'/public/activos/img/solandra-logo.png',
    dirname(__DIR__, 2).'/public/activos/img/solandra-logo.jpg',
    dirname(__DIR__, 2).'/public/activos/img/solandra-logo.jpeg',
];

foreach ($logoCandidates as $logoPath) {
    if (!is_file($logoPath)) {
        continue;
    }

    $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
    $mime = $extension === 'png' ? 'image/png' : 'image/jpeg';
    $logoSrc = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($logoPath));
    break;
}
?>

if (!empty($pdf)) {
    echo $assignmentCss;
}

if (empty($pdf)) {
    require __DIR__.'/imprimir/acciones.php';
}

require $isAssignment
    ? __DIR__.'/imprimir/asignacion.php'
    : __DIR__.'/imprimir/devolucion.php';
