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
