<?php
    $elementos = $registro['items'] ?? [];
    $phoneItems = array_values(array_filter($elementos, $isPhoneAsset));
    $equipos = array_values(array_filter($elementos, static fn (array $activo): bool => !$isPhoneAsset($activo)));
    $equipo = $equipos[0] ?? null;
    $celular = $phoneItems[0] ?? null;
    $extraEquipment = array_slice($equipos, 1);
    $equipmentAccessories = $assetText($equipo, 'condition_out');
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
                <td class="quality-title" colspan="3" rowspan="2">SISTEMA DE GESTIÃ“N DE CALIDAD</td>
                <td class="quality-meta"><div>CÃ³digo:<br><strong>SOL-TI-FO-01</strong></div></td>
            </tr>
            <tr>
                <td class="quality-meta"><div>VersiÃ³n:<br><strong>02</strong></div></td>
            </tr>
            <tr>
                <td class="quality-subtitle" colspan="3" rowspan="2">ASIGNACIÃ“N DE EQUIPOS INFORMÃTICOS</td>
                <td class="quality-meta"><div>Fecha de aprobaciÃ³n:<br><strong>23/04/2024</strong></div></td>
            </tr>
            <tr>
                <td class="quality-meta"><div>PÃ¡gina:<br><strong>1 de 1</strong></div></td>
            </tr>
            <tr class="quality-sign">
                <td colspan="2">Elaborado por:<span>Jhonny Fernandez</span></td>
                <td>Revisado por:<span>BenjamÃ­n Urbano</span></td>
                <td colspan="2">Aprobado por:<span>RubÃ©n Camargo</span></td>
            </tr>
        </table>

        <table class="quality-table equipment-table asset-table">
            <colgroup><col><col><col><col><col><col><col></colgroup>
            <tr><th class="section-title" colspan="7">Datos del Equipo</th></tr>
            <tr>
                <td class="field-label">Nombre Equipo</td>
                <td class="field-value<?= $cellClass($assetText($equipo, 'asset_code')) ?>" colspan="3"><?= e($assetText($equipo, 'asset_code')) ?></td>
                <td class="field-label">CÃ³digo</td>
                <td class="field-value<?= $cellClass($assetText($equipo, 'asset_code')) ?>" colspan="2"><?= e($assetText($equipo, 'asset_code')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Marca</td>
                <td class="field-value<?= $cellClass($assetText($equipo, 'brand_name')) ?>"><?= e($assetText($equipo, 'brand_name')) ?></td>
                <td class="field-label">Serie</td>
                <td class="field-value<?= $cellClass($assetText($equipo, 'serial_number')) ?>"><?= e($assetText($equipo, 'serial_number')) ?></td>
                <td class="field-label" colspan="2">Modelo</td>
                <td class="field-value<?= $cellClass($assetText($equipo, 'model_name')) ?>"><?= e($assetText($equipo, 'model_name')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Tipo de Equipo</td>
                <td class="field-value<?= $cellClass($assetText($equipo, 'type_name')) ?>" colspan="6"><?= e($assetText($equipo, 'type_name')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Accesorios</td>
                <td class="<?= trim($cellClass($equipmentAccessories)) ?>" colspan="6"><?= e($equipmentAccessories) ?></td>
            </tr>
            <tr>
                <td class="field-label">Observaciones</td>
                <td class="observations" colspan="6"><?= nl2br(e($assetObservations($equipo, $registro))) ?></td>
            </tr>
        </table>

        <table class="quality-table equipment-table phone-table">
            <colgroup><col><col><col><col><col><col><col><col></colgroup>
            <tr><th class="section-title" colspan="8">DescripciÃ³n de Celular y SIM CARD (cuando aplique)</th></tr>
            <tr>
                <td class="field-label">Chip de LÃ­nea</td>
                <td class="field-value<?= $cellClass($assetText($celular, 'phone_number')) ?>" colspan="2"><?= e($assetText($celular, 'phone_number')) ?></td>
                <td class="field-label">Marca</td>
                <td class="field-value<?= $cellClass($assetText($celular, 'brand_name')) ?>" colspan="2"><?= e($assetText($celular, 'brand_name')) ?></td>
                <td class="field-label">IMEI</td>
                <td class="field-value<?= $cellClass($assetText($celular, 'imei1') !== '-' ? $assetText($celular, 'imei1') : $assetText($celular, 'imei2')) ?>"><?= e($assetText($celular, 'imei1') !== '-' ? $assetText($celular, 'imei1') : $assetText($celular, 'imei2')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Modelo</td>
                <td class="field-value<?= $cellClass($assetText($celular, 'model_name')) ?>"><?= e($assetText($celular, 'model_name')) ?></td>
                <td class="field-label" colspan="3">Accesorios</td>
                <td class="<?= trim($cellClass($assetText($celular, 'condition_out'))) ?>" colspan="3"><?= e($assetText($celular, 'condition_out')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Observaciones</td>
                <td class="observations<?= $cellClass($assetObservations($celular, $registro)) ?>" colspan="7"><?= nl2br(e($assetObservations($celular, $registro))) ?></td>
            </tr>
        </table>

        <div class="assigned-title">ASIGNADO A:</div>
        <table class="quality-table assigned-table assignment-table">
            <colgroup><col><col><col><col><col><col></colgroup>
            <tr>
                <td class="field-label">Nombre y Apellidos</td>
                <td class="field-value" colspan="3"><?= e($upper($registro['employee_name'] ?? '')) ?></td>
                <td class="field-label">Fecha</td>
                <td class="field-value"><?= e($shortDate($fecha)) ?></td>
            </tr>
            <tr>
                <td class="field-label">Sede</td>
                <td class="field-value"><?= e($upper(config('app.site', ''))) ?></td>
                <td class="field-label">Ãrea</td>
                <td class="field-value" colspan="3"><?= e($upper($registro['area_name'] ?? '')) ?></td>
            </tr>
        </table>

        <p class="legal-text">El usuario, declara conocer y asume la responsabilidad del adecuado uso del equipo en menciÃ³n el cual solo debe ser usado con fines laborales, y por lo tanto puede ser solicitado en cualquier momento por SOLANDRA S.A.C., para su revisiÃ³n, sin lo cual no se estarÃ¡ afectando derecho alguno.</p>
        <p class="legal-text">El o los equipo(s) recepcionado(s) es y serÃ¡ propiedad de la empresa en todo momento; y en caso de concluido el contrato de trabajo de incremento de actividad, ME COMPROMETO a hacer la devoluciÃ³n inmediata del bien, y que en caso no lo haga tengo pleno conocimiento que estarÃ© incurriendo en el DELITO DE APROPIACIÃ“N ILÃCITA.</p>
        <p class="legal-text">En caso de daÃ±o por falta de deber de cuidado, extravÃ­o, pÃ©rdida o sustracciÃ³n del equipo, el usuario serÃ¡ el Ãºnico responsable para su reposiciÃ³n de igual o superior caracterÃ­sticas. AsÃ­ mismo en caso no lo reponga en un plazo de 72 horas, AUTORIZO EXPRESAMENTE a la empresa mediante este documento a descontar de mi salario o de mi pago por locaciÃ³n de servicios, por el valor total del costo de reposiciÃ³n del equipo cuando en cualesquiera de los casos no lo devuelva a la empresa.</p>
        <p class="legal-text">En tal sentido se procede a firmar la presente acta en seÃ±al de conformidad.</p>

        <div class="signature-line">Usuario</div>
        <div class="quality-footer">Este documento es propiedad de SOLANDRA SAC. Queda prohibido su reproducciÃ³n total o parcial</div>
    </section>
