<?php
$doc = $doc ?? '';
$item = $item ?? [];

$isAssignment = ($doc ?? '') === 'assignment';
$number = $isAssignment ? $item['assignment_number'] : $item['return_number'];
$date = $isAssignment ? $item['assigned_at'] : $item['returned_at'];
$titleText = $isAssignment
    ? 'ACTA DE ASIGNACIÓN DE EQUIPOS TECNOLÓGICOS'
    : 'ACTA DE DEVOLUCIÓN DE EQUIPOS TECNOLÓGICOS';
?>

<?php if (!empty($pdf)): ?>
    <style>
        @page{margin:20mm 15mm}body{font-family:DejaVu Sans,Arial,sans-serif;color:#182235;font-size:10px}.doc-header{border-bottom:3px solid #0d7369;padding-bottom:10px;margin-bottom:16px}.logo-box{font-size:22px;font-weight:bold;color:#0d7369}.logo-box span{display:block;font-size:9px;color:#666}.doc-meta{text-align:right;margin-top:-35px}.doc-title{text-align:center;margin:20px 0}.doc-title h1{font-size:16px}.info-grid{width:100%;margin-bottom:14px}.info-row{display:inline-block;width:48%;padding:5px 0;border-bottom:1px solid #ddd}.doc-table{width:100%;border-collapse:collapse}.doc-table th,.doc-table td{border:1px solid #aaa;padding:6px}.doc-table th{background:#edf3f2}.terms{font-size:9px;line-height:1.4;text-align:justify;margin-top:14px}.signatures{margin-top:65px;width:100%}.signature{display:inline-block;width:42%;margin:0 3%;text-align:center;border-top:1px solid #222;padding-top:6px}.doc-footer{text-align:center;font-size:8px;color:#777;margin-top:35px}
    </style>
<?php endif; ?>

<?php if (empty($pdf)): ?>
    <div class="print-actions">
        <button onclick="window.print()">Imprimir</button>
        <a href="<?= $isAssignment ? url('assignments/'.$item['id'].'/pdf') : url('returns/'.$item['id'].'/pdf') ?>">
            Descargar PDF
        </a>
    </div>
<?php endif; ?>

<header class="doc-header">
    <div class="logo-box">
        SOLANDRA
        <span>TECNOLOGÍA DE LA INFORMACIÓN</span>
    </div>

    <div class="doc-meta">
        <b>Sede Arequipa</b><br>
        Documento: <?= e($number) ?><br>
        Fecha: <?= date_pe($date) ?>
    </div>
</header>

<div class="doc-title">
    <h1><?= $titleText ?></h1>
    <p>Control interno de activos tecnológicos — SIGATI SOLANDRA</p>
</div>

<div class="info-grid">
    <div class="info-row">
        <b>Trabajador:</b> <?= e($item['employee_name']) ?>
    </div>
    <div class="info-row">
        <b>Código:</b> <?= e($item['employee_code']) ?>
    </div>
    <div class="info-row">
        <b>Cargo:</b> <?= e($item['position'] ?: '—') ?>
    </div>
    <div class="info-row">
        <b>Área:</b> <?= e($item['area_name'] ?: '—') ?>
    </div>
    <div class="info-row">
        <b>Fecha y hora:</b> <?= datetime_pe($date) ?>
    </div>
    <div class="info-row">
        <b>Responsable TI:</b> <?= e($item['created_by_name'] ?: 'TI Arequipa') ?>
    </div>
</div>

<table class="doc-table">
    <thead>
        <tr>
            <th>N.º</th>
            <th>Código</th>
            <th>Descripción</th>
            <th>Serie</th>
            <?php if ($isAssignment): ?>
                <th>Condición de entrega</th>
            <?php else: ?>
                <th>Condición</th>
                <th>Daños / faltantes</th>
                <th>Destino</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($item['items'] as $index => $asset): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= e($asset['asset_code']) ?></td>
                <td><?= e($asset['type_name'].' '.trim(($asset['brand_name'] ?? '').' '.($asset['model_name'] ?? ''))) ?></td>
                <td><?= e($asset['serial_number'] ?: '—') ?></td>

                <?php if ($isAssignment): ?>
                    <td><?= e($asset['condition_out']) ?></td>
                <?php else: ?>
                    <td><?= e($asset['condition_in']) ?></td>
                    <td><?= e($asset['damage_notes'] ?: 'Sin observaciones') ?></td>
                    <td><?= e($asset['next_status_name']) ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="terms">
    <?php if ($isAssignment): ?>
        Declaro haber recibido los equipos descritos en el presente documento, en la condición señalada,
        y asumo la responsabilidad de su uso exclusivamente para actividades autorizadas por SOLANDRA.
        Me comprometo a conservarlos, no transferirlos a terceros sin autorización del área de TI y
        comunicar inmediatamente cualquier pérdida, daño, incidente de seguridad o cambio de ubicación.
    <?php else: ?>
        El área de TI deja constancia de la recepción y evaluación inicial de los equipos detallados.
        La condición, daños, faltantes y estado posterior consignados corresponden a la revisión efectuada
        al momento de la devolución. Cualquier evaluación técnica complementaria será registrada en el
        historial del activo.
    <?php endif; ?>
</div>

<?php if (!empty($item['notes'])): ?>
    <div class="terms">
        <b>Observaciones:</b> <?= nl2br(e($item['notes'])) ?>
    </div>
<?php endif; ?>

<div class="signatures">
    <div class="signature">
        <b><?= e($item['employee_name']) ?></b><br>
        Trabajador responsable<br>
        DNI/Firma
    </div>

    <div class="signature">
        <b><?= e($item['created_by_name'] ?: 'Área de TI') ?></b><br>
        TI SOLANDRA Arequipa<br>
        Firma
    </div>
</div>

<div class="doc-footer">
    Documento generado por SIGATI SOLANDRA · <?= date('d/m/Y H:i') ?> · Uso interno
</div>
