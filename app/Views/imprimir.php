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
@page{size:A4 portrait;margin:2mm}
*{box-sizing:border-box}
body{font-family:Arial,DejaVu Sans,sans-serif;color:#000;font-size:14.2px;margin:0;background:#fff}
.print-actions{max-width:176mm;margin:0 auto 10px;padding:8px;background:#eef3f7;border-radius:4px}
.print-actions a,.print-actions button{padding:7px 11px;border:0;border-radius:4px;text-decoration:none;background:#086a62;color:white;cursor:pointer}
.quality-sheet{width:100%;max-width:205mm;min-height:293mm;margin:0 auto;background:#fff;padding:7.5mm 5mm 24mm;position:relative;display:block;overflow:hidden}
.quality-table{width:100%;border-collapse:collapse;table-layout:fixed}
.quality-table td,.quality-table th{border:0.75pt solid #222;padding:4.6px 5.8px;vertical-align:middle;line-height:1.16}
.quality-header{width:171mm;margin:0 auto 8.5mm;table-layout:fixed}
.quality-header col:nth-child(1){width:41mm}
.quality-header col:nth-child(2){width:16mm}
.quality-header col:nth-child(3){width:57mm}
.quality-header col:nth-child(4){width:20mm}
.quality-header col:nth-child(5){width:37mm}
.quality-header td{height:7mm;padding:0 3px}
.quality-logo{text-align:center;overflow:hidden;padding:0 4px}
.solandra-logo{width:36mm;max-width:100%;height:auto;display:block;margin:0 auto}
.quality-title{text-align:center;font-size:16.4px;font-weight:bold;color:#000;letter-spacing:0;white-space:nowrap}
.quality-subtitle{text-align:center;font-size:15.8px;font-weight:bold;color:#000;letter-spacing:0;white-space:nowrap}
.quality-meta{font-size:11.5px;padding:0;line-height:0.98;text-align:center}
.quality-meta div{padding:0.8px 3px;text-align:left}.quality-meta strong{display:block;text-align:center;font-weight:bold}
.quality-sign td{font-size:12.3px;height:7mm;line-height:1;vertical-align:bottom;padding:1px 4px;color:#000;text-align:left;font-weight:bold}
.quality-sign span{display:block;color:#000;text-decoration:none;text-align:center;font-weight:bold}
.section-title{background:#e3e7ec;text-align:center;font-weight:bold;font-size:14px}
.field-label{font-weight:bold;width:20%;font-size:13.8px}
.field-value{font-weight:500}
.dash{text-align:center!important}
.equipment-table{margin-bottom:4.2mm;font-size:15.2px}
.equipment-table td{height:auto;min-height:0}
.asset-table{margin-bottom:7.5mm}
.asset-table,.phone-table{width:172.5mm;margin-left:auto;margin-right:auto}
.assignment-table{width:170mm;margin-left:auto;margin-right:auto}
.asset-table col:nth-child(1){width:34.9mm}
.asset-table col:nth-child(2){width:32.5mm}
.asset-table col:nth-child(3){width:15mm}
.asset-table col:nth-child(4){width:35mm}
.asset-table col:nth-child(5){width:17.5mm}
.asset-table col:nth-child(6){width:2.5mm}
.asset-table col:nth-child(7){width:35mm}
.phone-table col:nth-child(1){width:34.9mm}
.phone-table col:nth-child(2){width:30mm}
.phone-table col:nth-child(3){width:2.5mm}
.phone-table col:nth-child(4){width:17.5mm}
.phone-table col:nth-child(5){width:5mm}
.phone-table col:nth-child(6){width:25mm}
.phone-table col:nth-child(7){width:12.5mm}
.phone-table col:nth-child(8){width:45mm}
.assignment-table col:nth-child(1){width:41.9mm}
.assignment-table col:nth-child(2){width:53.1mm}
.assignment-table col:nth-child(3){width:20mm}
.assignment-table col:nth-child(4){width:10mm}
.assignment-table col:nth-child(5){width:17.5mm}
.assignment-table col:nth-child(6){width:27.5mm}
.asset-table .field-label,.phone-table .field-label,.assignment-table .field-label{width:auto}
.asset-table .dash{text-align:center!important}
.observations{height:10mm;vertical-align:middle;line-height:1.16}
.assigned-title{width:170mm;margin:4.2mm auto 0.9mm;font-weight:bold;font-size:15.2px}
.assigned-table{margin-bottom:4.2mm;font-size:15.2px}
.assignment-table tr:first-child td:nth-child(3),.assignment-table tr:first-child td:nth-child(4),.assignment-table tr:nth-child(2) td:nth-child(3){text-align:center}
.assignment-table .field-label{white-space:nowrap}
.legal-text{width:170mm;margin:0 auto 1.55mm;font-size:14.4px;line-height:1.32;text-align:justify}
.signature-line{width:43mm;border-top:1px solid #000;text-align:center;font-weight:bold;margin:0;padding-top:1px;font-size:15px;position:absolute;right:9mm;bottom:22mm}
.quality-footer{width:172mm;border:1px solid #000;text-align:center;color:#000;font-size:12px;padding:2px 1.4px;margin:0;position:absolute;left:50%;margin-left:-86mm;bottom:9mm}
.return-title{text-align:center;font-weight:bold;font-size:14px;margin:8mm 0 4mm}
.doc-table{width:100%;border-collapse:collapse;margin:4mm 0}
.doc-table th,.doc-table td{border:1px solid #555;padding:5px;text-align:left;vertical-align:top}
.doc-table th{background:#e8edf2}
@media print{.print-actions{display:none}body{background:#fff}.quality-sheet{margin:0 auto;max-width:none;width:205mm}}
</style>
CSS;

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

<?php if (!empty($pdf)): ?>
    <?= $assignmentCss ?>
<?php endif; ?>

<?php if (empty($pdf)): ?>
    <div class="print-actions">
        <button onclick="window.print()">Imprimir</button>
        <a href="<?= $isAssignment ? url('asignaciones/'.$item['id'].'/pdf') : url('returns/'.$item['id'].'/pdf') ?>">
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
    $equipmentAccessories = $assetText($equipment, 'condition_out');
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
            <colgroup><col><col><col><col><col></colgroup>
            <tr>
                <td class="quality-logo" rowspan="4">
                    <?php if ($logoSrc !== ''): ?>
                        <img class="solandra-logo" src="<?= e($logoSrc) ?>" alt="Solandra">
                    <?php else: ?>
                        <strong>Solandra</strong>
                    <?php endif; ?>
                </td>
                <td class="quality-title" colspan="3" rowspan="2">SISTEMA DE GESTIÓN DE CALIDAD</td>
                <td class="quality-meta"><div>Código:<br><strong>SOL-TI-FO-01</strong></div></td>
            </tr>
            <tr>
                <td class="quality-meta"><div>Versión:<br><strong>02</strong></div></td>
            </tr>
            <tr>
                <td class="quality-subtitle" colspan="3" rowspan="2">ASIGNACIÓN DE EQUIPOS INFORMÁTICOS</td>
                <td class="quality-meta"><div>Fecha de aprobación:<br><strong>23/04/2024</strong></div></td>
            </tr>
            <tr>
                <td class="quality-meta"><div>Página:<br><strong>1 de 1</strong></div></td>
            </tr>
            <tr class="quality-sign">
                <td colspan="2">Elaborado por:<span>Jhonny Fernandez</span></td>
                <td>Revisado por:<span>Benjamín Urbano</span></td>
                <td colspan="2">Aprobado por:<span>Rubén Camargo</span></td>
            </tr>
        </table>

        <table class="quality-table equipment-table asset-table">
            <colgroup><col><col><col><col><col><col><col></colgroup>
            <tr><th class="section-title" colspan="7">Datos del Equipo</th></tr>
            <tr>
                <td class="field-label">Nombre Equipo</td>
                <td class="field-value<?= $cellClass($assetText($equipment, 'asset_code')) ?>" colspan="3"><?= e($assetText($equipment, 'asset_code')) ?></td>
                <td class="field-label">Código</td>
                <td class="field-value<?= $cellClass($assetText($equipment, 'asset_code')) ?>" colspan="2"><?= e($assetText($equipment, 'asset_code')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Marca</td>
                <td class="field-value<?= $cellClass($assetText($equipment, 'brand_name')) ?>"><?= e($assetText($equipment, 'brand_name')) ?></td>
                <td class="field-label">Serie</td>
                <td class="field-value<?= $cellClass($assetText($equipment, 'serial_number')) ?>"><?= e($assetText($equipment, 'serial_number')) ?></td>
                <td class="field-label" colspan="2">Modelo</td>
                <td class="field-value<?= $cellClass($assetText($equipment, 'model_name')) ?>"><?= e($assetText($equipment, 'model_name')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Tipo de Equipo</td>
                <td class="field-value<?= $cellClass($assetText($equipment, 'type_name')) ?>" colspan="6"><?= e($assetText($equipment, 'type_name')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Accesorios</td>
                <td class="<?= trim($cellClass($equipmentAccessories)) ?>" colspan="6"><?= e($equipmentAccessories) ?></td>
            </tr>
            <tr>
                <td class="field-label">Observaciones</td>
                <td class="observations" colspan="6"><?= nl2br(e($assetObservations($equipment, $item))) ?></td>
            </tr>
        </table>

        <table class="quality-table equipment-table phone-table">
            <colgroup><col><col><col><col><col><col><col><col></colgroup>
            <tr><th class="section-title" colspan="8">Descripción de Celular y SIM CARD (cuando aplique)</th></tr>
            <tr>
                <td class="field-label">Chip de Línea</td>
                <td class="field-value<?= $cellClass($assetText($phone, 'phone_number')) ?>" colspan="2"><?= e($assetText($phone, 'phone_number')) ?></td>
                <td class="field-label">Marca</td>
                <td class="field-value<?= $cellClass($assetText($phone, 'brand_name')) ?>" colspan="2"><?= e($assetText($phone, 'brand_name')) ?></td>
                <td class="field-label">IMEI</td>
                <td class="field-value<?= $cellClass($assetText($phone, 'imei1') !== '-' ? $assetText($phone, 'imei1') : $assetText($phone, 'imei2')) ?>"><?= e($assetText($phone, 'imei1') !== '-' ? $assetText($phone, 'imei1') : $assetText($phone, 'imei2')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Modelo</td>
                <td class="field-value<?= $cellClass($assetText($phone, 'model_name')) ?>"><?= e($assetText($phone, 'model_name')) ?></td>
                <td class="field-label" colspan="3">Accesorios</td>
                <td class="<?= trim($cellClass($assetText($phone, 'condition_out'))) ?>" colspan="3"><?= e($assetText($phone, 'condition_out')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Observaciones</td>
                <td class="observations<?= $cellClass($assetObservations($phone, $item)) ?>" colspan="7"><?= nl2br(e($assetObservations($phone, $item))) ?></td>
            </tr>
        </table>

        <div class="assigned-title">ASIGNADO A:</div>
        <table class="quality-table assigned-table assignment-table">
            <colgroup><col><col><col><col><col><col></colgroup>
            <tr>
                <td class="field-label">Nombre y Apellidos</td>
                <td class="field-value" colspan="3"><?= e($upper($item['employee_name'] ?? '')) ?></td>
                <td class="field-label">Fecha</td>
                <td class="field-value"><?= e($shortDate($date)) ?></td>
            </tr>
            <tr>
                <td class="field-label">Sede</td>
                <td class="field-value"><?= e($upper(config('app.site', ''))) ?></td>
                <td class="field-label">Área</td>
                <td class="field-value" colspan="3"><?= e($upper($item['area_name'] ?? '')) ?></td>
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
