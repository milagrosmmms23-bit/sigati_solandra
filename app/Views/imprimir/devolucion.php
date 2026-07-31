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
