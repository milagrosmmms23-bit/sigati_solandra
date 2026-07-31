<?php
$doc = $doc ?? '';
$item = $item ?? [];
$isAssignment = $doc === 'assignment';
$date = $isAssignment ? ($item['assigned_at'] ?? '') : ($item['returned_at'] ?? '');

$shortDate = static function (?string $value): string {
    if (!$value || strtotime($value) === false) {
        return '-';
    }

    return date('d/m/y', strtotime($value));
};

$clean = static function (mixed $value, string $default = '-'): string {
    $text = trim((string) $value);

    return $text !== '' ? $text : $default;
};

$upper = static function (mixed $value) use ($clean): string {
    return mb_strtoupper($clean($value), 'UTF-8');
};

$assetText = static function (?array $asset, string $key, string $default = '-') use ($clean): string {
    return $asset ? $clean($asset[$key] ?? '', $default) : $default;
};

$cellClass = static function (mixed $value): string {
    return trim((string) $value) === '-' ? ' dash' : '';
};

$assetDescription = static function (?array $asset) use ($clean): string {
    if (!$asset) {
        return '-';
    }

    $parts = array_filter([
        $asset['type_name'] ?? '',
        $asset['brand_name'] ?? '',
        $asset['model_name'] ?? '',
    ], static fn ($value): bool => trim((string) $value) !== '');

    return $clean(implode(' ', $parts));
};

$assetObservations = static function (?array $asset, array $assignment, string $default = '-') use ($clean): string {
    if (!$asset) {
        return $default;
    }

    $parts = [];

    foreach (['specs_text', 'asset_notes'] as $key) {
        $text = trim((string) ($asset[$key] ?? ''));

        if ($text !== '') {
            $parts[] = $text;
        }
    }

    if (!$parts) {
        $note = trim((string) ($assignment['notes'] ?? ''));

        if ($note !== '') {
            $parts[] = $note;
        }
    }

    return $clean(implode(', ', array_unique($parts)), $default);
};

$isPhoneAsset = static function (array $asset): bool {
    $text = mb_strtoupper(implode(' ', [
        $asset['type_name'] ?? '',
        $asset['brand_name'] ?? '',
        $asset['model_name'] ?? '',
        $asset['phone_number'] ?? '',
        $asset['imei1'] ?? '',
        $asset['imei2'] ?? '',
    ]), 'UTF-8');

    if (trim((string) ($asset['phone_number'] ?? '')) !== '' || trim((string) ($asset['imei1'] ?? '')) !== '' || trim((string) ($asset['imei2'] ?? '')) !== '') {
        return true;
    }

    foreach (['CELULAR', 'SMARTPHONE', 'TELEFONO', 'TELÉFONO', 'MOVIL', 'MÓVIL', 'SIM CARD', 'SIM'] as $needle) {
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