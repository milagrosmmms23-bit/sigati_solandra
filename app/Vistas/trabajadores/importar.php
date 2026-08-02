<?php
$preview = $preview ?? null;
$mostrar = static fn ($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="page-actions">
    <div>
        <h2>Importar trabajadores</h2>
        <p>Detecta responsables desde una hoja de personal o desde tu inventario Excel.</p>
    </div>

    <a class="btn btn-light" href="<?= url('trabajadores') ?>">Volver</a>
</div>

<div class="two-columns">
    <form class="form-card compact-card" method="post" enctype="multipart/form-data" action="<?= url('trabajadores/importar') ?>">
        <?= csrf_field() ?>
        <h3>Seleccionar archivo</h3>
        <p>Sube un Excel .xlsx o CSV. Puede ser tu inventario completo.</p>

        <label class="file-drop">
            <input type="file" name="archivo" accept=".xlsx,.csv,text/csv" required>
            <span>Arrastra o selecciona tu archivo</span>
            <small>Se revisan columnas como trabajador, asignado a, encargado, area y telefono.</small>
        </label>

        <button class="btn btn-primary btn-block" type="submit">Analizar archivo</button>
    </form>

    <section class="panel">
        <h3>Columnas reconocidas</h3>
        <div class="code-block">
            codigo_trabajador, nombres, apellidos, trabajador, colaborador, asignado_a,
            asignado_al_colaborador, encargado, responsable, area, cargo, telefono, correo
        </div>
        <div class="notice">
            Si no hay codigo, el sistema genera uno automatico con formato SOL-IMP-00001.
            Los trabajadores existentes se muestran como omitidos para evitar duplicados.
        </div>
    </section>
</div>

<?php if ($preview): ?>
    <section class="panel" style="margin-top:18px;">
        <div class="page-actions">
            <div>
                <h3>Revision de <?= $mostrar($preview['archivo']) ?></h3>
                <p>
                    <?= (int) $preview['resumen']['total'] ?> filas leidas,
                    <?= (int) $preview['resumen']['nuevos'] ?> nuevos,
                    <?= (int) $preview['resumen']['existentes'] ?> existentes/duplicados,
                    <?= (int) $preview['resumen']['errores'] ?> errores.
                </p>
            </div>

            <form method="post" action="<?= url('trabajadores/importar') ?>" style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                <?= csrf_field() ?>
                <button class="btn btn-light" type="submit" name="cancelar" value="1">Cancelar</button>
                <?php if (!$preview['bloqueado']): ?>
                    <button class="btn btn-primary" type="submit" name="confirmar" value="1">Confirmar importacion</button>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($preview['bloqueado']): ?>
            <div class="notice danger">Hay errores que corregir antes de importar. No se guardo nada todavia.</div>
        <?php else: ?>
            <div class="notice success">El archivo esta listo. Se importaran solo los trabajadores nuevos.</div>
        <?php endif; ?>

        <div class="table-responsive" style="margin-top:14px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fila</th>
                        <th>Estado</th>
                        <th>Codigo</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Area</th>
                        <th>Cargo</th>
                        <th>Telefono</th>
                        <th>Correo</th>
                        <th>Origen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($preview['filas'], 0, 100) as $fila): ?>
                        <?php
                        $erroresFila = $fila['errores'] ?? [];
                        $advertenciasFila = $fila['advertencias'] ?? [];
                        $estado = $erroresFila ? implode(' | ', $erroresFila) : ($advertenciasFila ? implode(' | ', $advertenciasFila) : 'Nuevo');
                        ?>
                        <tr>
                            <td><?= $mostrar($fila['numero']) ?></td>
                            <td><?= $mostrar($estado) ?></td>
                            <td><?= $mostrar($fila['datos']['codigo_trabajador'] ?: 'Automatico') ?></td>
                            <td><?= $mostrar($fila['datos']['nombres']) ?></td>
                            <td><?= $mostrar($fila['datos']['apellidos']) ?></td>
                            <td><?= $mostrar($fila['datos']['area']) ?></td>
                            <td><?= $mostrar($fila['datos']['cargo']) ?></td>
                            <td><?= $mostrar($fila['datos']['telefono']) ?></td>
                            <td><?= $mostrar($fila['datos']['correo']) ?></td>
                            <td><?= $mostrar($fila['datos']['origen']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>