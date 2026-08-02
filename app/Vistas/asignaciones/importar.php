<?php
$preview = $preview ?? null;
$mostrar = static fn ($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="page-actions">
    <div>
        <h2>Importar asignaciones</h2>
        <p>Relaciona trabajadores con activos disponibles desde Excel o CSV.</p>
    </div>

    <a class="btn btn-light" href="<?= url('asignaciones') ?>">Volver</a>
</div>

<div class="two-columns">
    <form class="form-card compact-card" method="post" enctype="multipart/form-data" action="<?= url('asignaciones/importar') ?>">
        <?= csrf_field() ?>
        <h3>Seleccionar archivo</h3>
        <p>Usa tu inventario Excel o un CSV con trabajador y equipo.</p>

        <label class="file-drop">
            <input type="file" name="archivo" accept=".xlsx,.csv,text/csv" required>
            <span>Arrastra o selecciona tu archivo</span>
            <small>Primero importa activos y trabajadores para que el cruce encuentre coincidencias.</small>
        </label>

        <button class="btn btn-primary btn-block" type="submit">Analizar archivo</button>
    </form>

    <section class="panel">
        <h3>Columnas reconocidas</h3>
        <div class="code-block">
            asignado_a, asignado_al_colaborador, encargado, responsable, trabajador,
            codigo, codigo_activo, codigo_anterior, cod_monitor, serie, imei,
            telefono, nombre_equipo, area, fecha_entrega, condicion, observaciones
        </div>
        <div class="notice">
            El sistema agrupa por trabajador y fecha. Si una persona recibio equipos en fechas distintas,
            se generaran actas separadas.
        </div>
    </section>
</div>

<?php if ($preview): ?>
    <section class="panel" style="margin-top:18px;">
        <div class="page-actions">
            <div>
                <h3>Revision de <?= $mostrar($preview['archivo']) ?></h3>
                <p>
                    <?= (int) $preview['resumen']['filas'] ?> filas,
                    <?= (int) $preview['resumen']['asignaciones'] ?> actas,
                    <?= (int) $preview['resumen']['activos'] ?> activos,
                    <?= (int) $preview['resumen']['errores'] ?> errores.
                </p>
            </div>

            <form method="post" action="<?= url('asignaciones/importar') ?>" style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                <?= csrf_field() ?>
                <button class="btn btn-light" type="submit" name="cancelar" value="1">Cancelar</button>
                <?php if (!$preview['bloqueado']): ?>
                    <button class="btn btn-primary" type="submit" name="confirmar" value="1">Crear asignaciones</button>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($preview['bloqueado']): ?>
            <div class="notice danger">Hay errores que corregir. No se guardo nada todavia.</div>
        <?php else: ?>
            <div class="notice success">El archivo esta listo para crear las actas.</div>
        <?php endif; ?>

        <div class="table-responsive" style="margin-top:14px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fila</th>
                        <th>Estado</th>
                        <th>Trabajador</th>
                        <th>Codigo</th>
                        <th>Serie/IMEI/Tel.</th>
                        <th>Fecha</th>
                        <th>Condicion</th>
                        <th>Origen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($preview['filas'], 0, 120) as $fila): ?>
                        <?php
                        $erroresFila = $fila['errores'] ?? [];
                        $advertenciasFila = $fila['advertencias'] ?? [];
                        $estado = $erroresFila ? implode(' | ', $erroresFila) : ($advertenciasFila ? implode(' | ', $advertenciasFila) : 'Listo');
                        ?>
                        <tr>
                            <td><?= $mostrar($fila['numero']) ?></td>
                            <td><?= $mostrar($estado) ?></td>
                            <td><?= $mostrar($fila['datos']['trabajador']) ?></td>
                            <td><?= $mostrar($fila['datos']['codigo_activo']) ?></td>
                            <td><?= $mostrar($fila['datos']['serie']) ?></td>
                            <td><?= $mostrar($fila['datos']['fecha'] ?: 'Actual') ?></td>
                            <td><?= $mostrar($fila['datos']['condicion']) ?></td>
                            <td><?= $mostrar($fila['origen']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>