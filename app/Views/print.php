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

$assignmentCss = <<<'CSS'
<style>
@page{size:A4 portrait;margin:5mm 7mm 4mm}
*{box-sizing:border-box}
body{font-family:Arial,DejaVu Sans,sans-serif;color:#000;font-size:14px;margin:0;background:#fff}
.print-actions{max-width:180mm;margin:0 auto 10px;padding:8px;background:#eef3f7;border-radius:4px}
.print-actions a,.print-actions button{padding:7px 11px;border:0;border-radius:4px;text-decoration:none;background:#086a62;color:white;cursor:pointer}
.quality-sheet{width:100%;max-width:196mm;min-height:0;margin:0 auto;background:#fff;display:block}
.quality-table{width:100%;border-collapse:collapse;table-layout:fixed}
.quality-table td,.quality-table th{border:1px solid #222;padding:4.5px 6px;vertical-align:middle;line-height:1.14}
.quality-header{margin-bottom:5.5mm}
.quality-logo{width:24%;text-align:center;padding:0 3px;overflow:hidden}
.solandra-logo{width:44mm;max-width:100%;height:auto;display:block;margin:0 auto}
.quality-title{text-align:center;font-size:18px;font-weight:bold;color:#555;letter-spacing:0}
.quality-subtitle{text-align:center;font-size:17px;font-weight:bold;color:#555;letter-spacing:0}
.quality-meta{width:22%;font-size:12.5px;padding:0;line-height:0.98;text-align:center}
.quality-meta div{border-bottom:1px solid #222;padding:1px 4px;text-align:center}
.quality-meta div:last-child{border-bottom:0}
.quality-sign td{font-size:14px;height:auto;line-height:1.05;vertical-align:bottom;padding:2px 4px;color:#000}
.quality-sign span{display:block;color:#000;text-decoration:none;text-align:center;font-weight:bold}
.section-title{background:#dfe4ea;text-align:center;font-weight:bold;font-size:14.5px}
.field-label{font-weight:bold;width:20%;font-size:14.5px}
.field-value{font-weight:bold}
.dash{text-align:center!important}
.equipment-table{margin-bottom:4.5mm;font-size:17px}
.equipment-table td{height:auto;min-height:0}
.observations{height:auto;vertical-align:top;line-height:1.1}
.assigned-title{font-weight:bold;margin:4.5mm 0 1mm 5mm;font-size:17px}
.assigned-table{margin-bottom:4.5mm;font-size:17px}
.legal-text{font-size:15.2px;line-height:1.16;text-align:justify;margin:0 0 1.3mm}
.signature-line{width:42mm;border-top:1px solid #000;text-align:center;font-weight:bold;margin:4.5mm 3mm 1mm auto;padding-top:1px;font-size:14px}
.quality-footer{border:1px solid #999;text-align:center;color:#777;font-size:10px;padding:1px;margin:0 4mm}
.return-title{text-align:center;font-weight:bold;font-size:14px;margin:8mm 0 4mm}
.doc-table{width:100%;border-collapse:collapse;margin:4mm 0}
.doc-table th,.doc-table td{border:1px solid #555;padding:5px;text-align:left;vertical-align:top}
.doc-table th{background:#e8edf2}
@media print{.print-actions{display:none}.quality-sheet{margin:0 auto}}
</style>
CSS;

$logoSrc = '';
$logoCandidates = [
    dirname(__DIR__, 2).'/public/assets/img/solandra-logo.png',
    dirname(__DIR__, 2).'/public/assets/img/solandra-logo.jpg',
    dirname(__DIR__, 2).'/public/assets/img/solandra-logo.jpeg',
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

<?php if (!empty($pdf)): ?>
    <?= $assignmentCss ?>
<?php endif; ?>

<?php if (empty($pdf)): ?>
    <div class="print-actions">
        <button onclick="window.print()">Imprimir</button>
        <a href="<?= $isAssignment ? url('assignments/'.$item['id'].'/pdf') : url('returns/'.$item['id'].'/pdf') ?>">
            Descargar PDF
        </a>
    </div>
<?php endif; ?>

<?php if ($isAssignment): ?>
    <?php
    $items = $item['items'] ?? [];
    $phoneItems = array_values(array_filter($items, $isPhoneAsset));
    $equipmentItems = array_values(array_filter($items, static fn (array $asset): bool => !$isPhoneAsset($asset)));
    $equipment = $equipmentItems[0] ?? null;
    $phone = $phoneItems[0] ?? null;
    $extraEquipment = array_slice($equipmentItems, 1);
    $equipmentAccessories = $assetText($equipment, 'condition_out', '');
    $extraDescriptions = [];

    foreach ($extraEquipment as $extraAsset) {
        $detail = $assetDescription($extraAsset);
        $serial = $clean($extraAsset['serial_number'] ?? '', '');

        if ($serial !== '') {
            $detail .= ' S/N '.$serial;
        }

        $extraDescriptions[] = $detail;
    }

    if ($extraDescriptions) {
        $equipmentAccessories = $equipmentAccessories === '' || $equipmentAccessories === '-'
            ? implode(', ', $extraDescriptions)
            : $equipmentAccessories.', '.implode(', ', $extraDescriptions);
    }
    ?>

    <section class="quality-sheet">
        <table class="quality-table quality-header">
            <tr>                <td class="quality-logo" rowspan="2">
                    <?php if ($logoSrc !== ''): ?>
                        <img class="solandra-logo" src="<?= e($logoSrc) ?>" alt="Solandra">
                    <?php else: ?>
                        <svg class="solandra-logo" viewBox="0 0 340 82" role="img" aria-label="Solandra">
                            <circle cx="41" cy="41" r="36" fill="#78BE43"/>
                            <path d="M24 20c17 3 26 12 29 27" fill="none" stroke="#071B16" stroke-width="5" stroke-linecap="round"/>
                            <path d="M23 38c10 3 17 9 21 18" fill="none" stroke="#071B16" stroke-width="5" stroke-linecap="round"/>
                            <path d="M54 22c-9 11-12 22-7 35" fill="none" stroke="#071B16" stroke-width="5" stroke-linecap="round"/>
                            <circle cx="26" cy="58" r="4" fill="#071B16"/>
                            <text x="88" y="55" fill="#00416B" font-family="Arial, Helvetica, sans-serif" font-size="46" font-weight="700">Solandra</text>
                        </svg>
                    <?php endif; ?>
                </td>
                <td class="quality-title">SISTEMA DE GESTIÓN DE CALIDAD</td>
                <td class="quality-meta" rowspan="2">
                    <div>Código:<br><strong>SOL-TI-FO-01</strong></div>
                    <div>Versión:<br><strong>02</strong></div>
                    <div>Fecha de aprobación:<br><strong>23/04/2024</strong></div>
                    <div>Página:<br><strong>1 de 1</strong></div>
                </td>
            </tr>
            <tr><td class="quality-subtitle">ASIGNACIÓN DE EQUIPOS INFORMÁTICOS</td></tr>
            <tr class="quality-sign">
                <td>Elaborado por:<span>Jhonny Fernandez</span></td>
                <td>Revisado por:<span>Benjamín Urbano</span></td>
                <td>Aprobado por:<span>Rubén Camargo</span></td>
            </tr>
        </table>

        <table class="quality-table equipment-table">
            <tr><th class="section-title" colspan="6">Datos del Equipo</th></tr>
            <tr>
                <td class="field-label">Nombre Equipo</td>
                <td class="field-value" colspan="3"><?= e($assetText($equipment, 'asset_code', '')) ?></td>
                <td class="field-label">Código</td>
                <td class="field-value"><?= e($assetText($equipment, 'asset_code', '')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Marca</td>
                <td class="field-value"><?= e($assetText($equipment, 'brand_name', '')) ?></td>
                <td class="field-label">Serie</td>
                <td class="field-value"><?= e($assetText($equipment, 'serial_number', '')) ?></td>
                <td class="field-label">Modelo</td>
                <td class="field-value"><?= e($assetText($equipment, 'model_name', '')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Tipo de Equipo</td>
                <td class="field-value" colspan="5"><?= e($assetText($equipment, 'type_name', '')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Accesorios</td>
                <td colspan="5"><?= e($equipmentAccessories) ?></td>
            </tr>
            <tr>
                <td class="field-label">Observaciones</td>
                <td class="observations" colspan="5"><?= nl2br(e($assetObservations($equipment, $item, ''))) ?></td>
            </tr>
        </table>

        <table class="quality-table equipment-table">
            <tr><th class="section-title" colspan="6">Descripción de Celular y SIM CARD (cuando aplique)</th></tr>
            <tr>
                <td class="field-label">Chip de Línea</td>
                <td class="field-value<?= $cellClass($assetText($phone, 'phone_number')) ?>"><?= e($assetText($phone, 'phone_number')) ?></td>
                <td class="field-label">Marca</td>
                <td class="field-value<?= $cellClass($assetText($phone, 'brand_name')) ?>"><?= e($assetText($phone, 'brand_name')) ?></td>
                <td class="field-label">IMEI</td>
                <td class="field-value<?= $cellClass($assetText($phone, 'imei1') !== '-' ? $assetText($phone, 'imei1') : $assetText($phone, 'imei2')) ?>"><?= e($assetText($phone, 'imei1') !== '-' ? $assetText($phone, 'imei1') : $assetText($phone, 'imei2')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Modelo</td>
                <td class="field-value<?= $cellClass($assetText($phone, 'model_name')) ?>" colspan="2"><?= e($assetText($phone, 'model_name')) ?></td>
                <td class="field-label">Accesorios</td>
                <td class="<?= trim($cellClass($assetText($phone, 'condition_out'))) ?>" colspan="2"><?= e($assetText($phone, 'condition_out')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Observaciones</td>
                <td class="observations<?= $cellClass($assetObservations($phone, $item)) ?>" colspan="5"><?= nl2br(e($assetObservations($phone, $item))) ?></td>
            </tr>
        </table>

        <div class="assigned-title">ASIGNADO A:</div>
        <table class="quality-table assigned-table">
            <tr>
                <td class="field-label">Nombre y Apellidos</td>
                <td class="field-value" colspan="3"><?= e($upper($item['employee_name'] ?? '')) ?></td>
                <td class="field-label">Fecha</td>
                <td class="field-value"><?= e($shortDate($date)) ?></td>
            </tr>
            <tr>
                <td class="field-label">Sede</td>
                <td class="field-value" colspan="2"><?= e($upper(config('app.site', ''))) ?></td>
                <td class="field-label">Área</td>
                <td class="field-value" colspan="2"><?= e($upper($item['area_name'] ?? '')) ?></td>
            </tr>
        </table>

        <p class="legal-text">El usuario, declara conocer y asume la responsabilidad del adecuado uso del equipo en mención el cual solo debe ser usado con fines laborales, y por lo tanto puede ser solicitado en cualquier momento por SOLANDRA S.A.C., para su revisión, sin lo cual no se estará afectando derecho alguno.</p>
        <p class="legal-text">El o los equipo(s) recepcionado(s) es y será propiedad de la empresa en todo momento; y en caso de concluido el contrato de trabajo de incremento de actividad, ME COMPROMETO a hacer la devolución inmediata del bien, y que en caso no lo haga tengo pleno conocimiento que estaré incurriendo en el DELITO DE APROPIACIÓN ILÍCITA.</p>
        <p class="legal-text">En caso de daño por falta de deber de cuidado, extravío, pérdida o sustracción del equipo, el usuario será el único responsable para su reposición de igual o superior características. Así mismo en caso no lo reponga en un plazo de 72 horas, AUTORIZO EXPRESAMENTE a la empresa mediante este documento a descontar de mi salario o de mi pago por locación de servicios, por el valor total del costo de reposición del equipo cuando en cualesquiera de los casos no lo devuelva a la empresa.</p>
        <p class="legal-text">En tal sentido se procede a firmar la presente acta en señal de conformidad.</p>

        <div class="signature-line">Usuario</div>
        <div class="quality-footer">Este documento es propiedad de SOLANDRA SAC. Queda prohibido su reproducción total o parcial</div>
    </section>
<?php else: ?>
    <h1 class="return-title">ACTA DE DEVOLUCIÓN DE EQUIPOS TECNOLÓGICOS</h1>
    <table class="quality-table assigned-table">
        <tr><td class="field-label">Trabajador</td><td class="field-value"><?= e($item['employee_name'] ?? '') ?></td><td class="field-label">Fecha</td><td class="field-value"><?= e(date_pe($date)) ?></td></tr>
        <tr><td class="field-label">Código</td><td><?= e($item['employee_code'] ?? '') ?></td><td class="field-label">Área</td><td><?= e($item['area_name'] ?? '') ?></td></tr>
    </table>
    <table class="doc-table">
        <thead><tr><th>N.º</th><th>Código</th><th>Descripción</th><th>Serie</th><th>Condición</th><th>Daños / faltantes</th><th>Destino</th></tr></thead>
        <tbody>
            <?php foreach (($item['items'] ?? []) as $index => $asset): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= e($asset['asset_code'] ?? '') ?></td>
                    <td><?= e(($asset['type_name'] ?? '').' '.trim(($asset['brand_name'] ?? '').' '.($asset['model_name'] ?? ''))) ?></td>
                    <td><?= e($asset['serial_number'] ?? '') ?></td>
                    <td><?= e($asset['condition_in'] ?? '') ?></td>
                    <td><?= e(($asset['damage_notes'] ?? '') ?: 'Sin observaciones') ?></td>
                    <td><?= e($asset['next_status_name'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
