<h1 class="return-title">ACTA DE DEVOLUCIÃ“N DE EQUIPOS TECNOLÃ“GICOS</h1>
    <table class="quality-table assigned-table">
        <tr><td class="field-label">Trabajador</td><td class="field-value"><?= e($registro['employee_name'] ?? '') ?></td><td class="field-label">Fecha</td><td class="field-value"><?= e(date_pe($fecha)) ?></td></tr>
        <tr><td class="field-label">CÃ³digo</td><td><?= e($registro['employee_code'] ?? '') ?></td><td class="field-label">Ãrea</td><td><?= e($registro['area_name'] ?? '') ?></td></tr>
    </table>
    <table class="doc-table">
        <thead><tr><th>N.Âº</th><th>CÃ³digo</th><th>DescripciÃ³n</th><th>Serie</th><th>CondiciÃ³n</th><th>DaÃ±os / faltantes</th><th>Destino</th></tr></thead>
        <tbody>
            <?php foreach (($registro['items'] ?? []) as $indice => $activo): ?>
                <tr>
                    <td><?= $indice + 1 ?></td>
                    <td><?= e($activo['asset_code'] ?? '') ?></td>
                    <td><?= e(($activo['type_name'] ?? '').' '.trim(($activo['brand_name'] ?? '').' '.($activo['model_name'] ?? ''))) ?></td>
                    <td><?= e($activo['serial_number'] ?? '') ?></td>
                    <td><?= e($activo['condition_in'] ?? '') ?></td>
                    <td><?= e(($activo['damage_notes'] ?? '') ?: 'Sin observaciones') ?></td>
                    <td><?= e($activo['next_status_name'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
