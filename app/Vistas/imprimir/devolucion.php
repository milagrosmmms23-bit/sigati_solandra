<h1 class="return-title">ACTA DE DEVOLUCION DE EQUIPOS TECNOLOGICOS</h1>
    <table class="quality-table assigned-table">
        <tr><td class="field-label">Trabajador</td><td class="field-value"><?= e($registro['nombre_trabajador'] ?? '') ?></td><td class="field-label">Fecha</td><td class="field-value"><?= e(date_pe($fecha)) ?></td></tr>
        <tr><td class="field-label">Codigo</td><td><?= e($registro['codigo_trabajador'] ?? '') ?></td><td class="field-label">Area</td><td><?= e($registro['nombre_area'] ?? '') ?></td></tr>
    </table>
    <table class="doc-table">
        <thead><tr><th>N.&deg;</th><th>Codigo</th><th>Descripcion</th><th>Serie</th><th>Condicion</th><th>Da&ntilde;os / faltantes</th><th>Destino</th></tr></thead>
        <tbody>
            <?php foreach (($registro['elementos'] ?? []) as $indice => $activo): ?>
                <tr>
                    <td><?= $indice + 1 ?></td>
                    <td><?= e($activo['codigo_activo'] ?? '') ?></td>
                    <td><?= e(($activo['nombre_tipo'] ?? '').' '.trim(($activo['nombre_marca'] ?? '').' '.($activo['nombre_modelo'] ?? ''))) ?></td>
                    <td><?= e($activo['numero_serie'] ?? '') ?></td>
                    <td><?= e($activo['condicion_entrada'] ?? '') ?></td>
                    <td><?= e(($activo['observaciones_danos'] ?? '') ?: 'Sin observaciones') ?></td>
                    <td><?= e($activo['nombre_siguiente_estado'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>