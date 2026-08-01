<?php
    $elementos = $registro['items'] ?? [];
    $celulares = array_values(array_filter($elementos, $esCelular));
    $equipos = array_values(array_filter($elementos, static fn (array $activo): bool => !$esCelular($activo)));
    $equipo = $equipos[0] ?? null;
    $celular = $celulares[0] ?? null;
    $equiposExtra = array_slice($equipos, 1);
    $accesoriosEquipo = $textoActivo($equipo, 'condition_out');
    $descripcionesExtra = [];

    foreach ($equiposExtra as $equipoExtra) {
        $detalle = $descripcionActivo($equipoExtra);
        $serie = $limpiar($equipoExtra['serial_number'] ?? '', '');

        if ($serie !== '') {
            $detalle .= ' S/N '.$serie;
        }

        $descripcionesExtra[] = $detalle;
    }

    if ($descripcionesExtra) {
        $accesoriosEquipo = $accesoriosEquipo === '' || $accesoriosEquipo === '-'
            ? implode(', ', $descripcionesExtra)
            : $accesoriosEquipo.', '.implode(', ', $descripcionesExtra);
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
                <td class="quality-title" colspan="3" rowspan="2">SISTEMA DE GESTI&Oacute;N DE CALIDAD</td>
                <td class="quality-meta"><div>C&oacute;digo:<br><strong>SOL-TI-FO-01</strong></div></td>
            </tr>
            <tr>
                <td class="quality-meta"><div>Versi&oacute;n:<br><strong>02</strong></div></td>
            </tr>
            <tr>
                <td class="quality-subtitle" colspan="3" rowspan="2">ASIGNACI&Oacute;N DE EQUIPOS INFORM&Aacute;TICOS</td>
                <td class="quality-meta"><div>Fecha de aprobaci&oacute;n:<br><strong>23/04/2024</strong></div></td>
            </tr>
            <tr>
                <td class="quality-meta"><div>P&aacute;gina:<br><strong>1 de 1</strong></div></td>
            </tr>
            <tr class="quality-sign">
                <td colspan="2">Elaborado por:<span>Jhonny Fernandez</span></td>
                <td>Revisado por:<span>Benjam&iacute;n Urbano</span></td>
                <td colspan="2">Aprobado por:<span>Rub&eacute;n Camargo</span></td>
            </tr>
        </table>

        <table class="quality-table equipment-table asset-table">
            <colgroup><col><col><col><col><col><col><col></colgroup>
            <tr><th class="section-title" colspan="7">Datos del Equipo</th></tr>
            <tr>
                <td class="field-label">Nombre Equipo</td>
                <td class="field-value<?= $claseCelda($textoActivo($equipo, 'asset_code')) ?>" colspan="3"><?= e($textoActivo($equipo, 'asset_code')) ?></td>
                <td class="field-label">C&oacute;digo</td>
                <td class="field-value<?= $claseCelda($textoActivo($equipo, 'asset_code')) ?>" colspan="2"><?= e($textoActivo($equipo, 'asset_code')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Marca</td>
                <td class="field-value<?= $claseCelda($textoActivo($equipo, 'brand_name')) ?>"><?= e($textoActivo($equipo, 'brand_name')) ?></td>
                <td class="field-label">Serie</td>
                <td class="field-value<?= $claseCelda($textoActivo($equipo, 'serial_number')) ?>"><?= e($textoActivo($equipo, 'serial_number')) ?></td>
                <td class="field-label" colspan="2">Modelo</td>
                <td class="field-value<?= $claseCelda($textoActivo($equipo, 'model_name')) ?>"><?= e($textoActivo($equipo, 'model_name')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Tipo de Equipo</td>
                <td class="field-value<?= $claseCelda($textoActivo($equipo, 'type_name')) ?>" colspan="6"><?= e($textoActivo($equipo, 'type_name')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Accesorios</td>
                <td class="<?= trim($claseCelda($accesoriosEquipo)) ?>" colspan="6"><?= e($accesoriosEquipo) ?></td>
            </tr>
            <tr>
                <td class="field-label">Observaciones</td>
                <td class="observations" colspan="6"><?= nl2br(e($observacionesActivo($equipo, $registro))) ?></td>
            </tr>
        </table>

        <table class="quality-table equipment-table phone-table">
            <colgroup><col><col><col><col><col><col><col><col></colgroup>
            <tr><th class="section-title" colspan="8">Descripci&oacute;n de Celular y SIM CARD (cuando aplique)</th></tr>
            <tr>
                <td class="field-label">Chip de L&iacute;nea</td>
                <td class="field-value<?= $claseCelda($textoActivo($celular, 'phone_number')) ?>" colspan="2"><?= e($textoActivo($celular, 'phone_number')) ?></td>
                <td class="field-label">Marca</td>
                <td class="field-value<?= $claseCelda($textoActivo($celular, 'brand_name')) ?>" colspan="2"><?= e($textoActivo($celular, 'brand_name')) ?></td>
                <td class="field-label">IMEI</td>
                <td class="field-value<?= $claseCelda($textoActivo($celular, 'imei1') !== '-' ? $textoActivo($celular, 'imei1') : $textoActivo($celular, 'imei2')) ?>"><?= e($textoActivo($celular, 'imei1') !== '-' ? $textoActivo($celular, 'imei1') : $textoActivo($celular, 'imei2')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Modelo</td>
                <td class="field-value<?= $claseCelda($textoActivo($celular, 'model_name')) ?>"><?= e($textoActivo($celular, 'model_name')) ?></td>
                <td class="field-label" colspan="3">Accesorios</td>
                <td class="<?= trim($claseCelda($textoActivo($celular, 'condition_out'))) ?>" colspan="3"><?= e($textoActivo($celular, 'condition_out')) ?></td>
            </tr>
            <tr>
                <td class="field-label">Observaciones</td>
                <td class="observations<?= $claseCelda($observacionesActivo($celular, $registro)) ?>" colspan="7"><?= nl2br(e($observacionesActivo($celular, $registro))) ?></td>
            </tr>
        </table>

        <div class="assigned-title">ASIGNADO A:</div>
        <table class="quality-table assigned-table assignment-table">
            <colgroup><col><col><col><col><col><col></colgroup>
            <tr>
                <td class="field-label">Nombre y Apellidos</td>
                <td class="field-value" colspan="3"><?= e($mayusculas($registro['employee_name'] ?? '')) ?></td>
                <td class="field-label">Fecha</td>
                <td class="field-value"><?= e($fechaCorta($fecha)) ?></td>
            </tr>
            <tr>
                <td class="field-label">Sede</td>
                <td class="field-value"><?= e($mayusculas(config('aplicacion.sede', ''))) ?></td>
                <td class="field-label">&Aacute;rea</td>
                <td class="field-value" colspan="3"><?= e($mayusculas($registro['area_name'] ?? '')) ?></td>
            </tr>
        </table>

        <p class="legal-text">El usuario, declara conocer y asume la responsabilidad del adecuado uso del equipo en menci&oacute;n el cual solo debe ser usado con fines laborales, y por lo tanto puede ser solicitado en cualquier momento por SOLANDRA S.A.C., para su revisi&oacute;n, sin lo cual no se estar&aacute; afectando derecho alguno.</p>
        <p class="legal-text">El o los equipo(s) recepcionado(s) es y ser&aacute; propiedad de la empresa en todo momento; y en caso de concluido el contrato de trabajo de incremento de actividad, ME COMPROMETO a hacer la devoluci&oacute;n inmediata del bien, y que en caso no lo haga tengo pleno conocimiento que estar&eacute; incurriendo en el DELITO DE APROPIACI&Oacute;N IL&Iacute;CITA.</p>
        <p class="legal-text">En caso de da&ntilde;o por falta de deber de cuidado, extrav&iacute;o, p&eacute;rdida o sustracci&oacute;n del equipo, el usuario ser&aacute; el &uacute;nico responsable para su reposici&oacute;n de igual o superior caracter&iacute;sticas. As&iacute; mismo en caso no lo reponga en un plazo de 72 horas, AUTORIZO EXPRESAMENTE a la empresa mediante este documento a descontar de mi salario o de mi pago por locaci&oacute;n de servicios, por el valor total del costo de reposici&oacute;n del equipo cuando en cualesquiera de los casos no lo devuelva a la empresa.</p>
        <p class="legal-text">En tal sentido se procede a firmar la presente acta en se&ntilde;al de conformidad.</p>

        <div class="signature-line">Usuario</div>
        <div class="quality-footer">Este documento es propiedad de SOLANDRA SAC. Queda prohibido su reproducci&oacute;n total o parcial</div>
    </section>