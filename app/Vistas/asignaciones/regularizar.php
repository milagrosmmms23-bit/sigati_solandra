<?php
$preview = $preview ?? null;
$mostrar = static fn ($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
?>

<div class="page-actions">
    <div>
        <h2>Regularizar responsables del Excel</h2>
        <p>Convierte la referencia importada en actas reales dentro de SIGATI.</p>
    </div>

    <a class="btn btn-light" href="<?= url('asignaciones') ?>">Volver</a>
</div>

<?php if (!$preview || !$preview['resumen']['filas']): ?>
    <section class="panel">
        <div class="empty">
            No hay activos pendientes con Responsable en Excel.
        </div>
    </section>
<?php else: ?>
    <section class="panel report-summary">
        <div>
            <span>Filas revisadas</span>
            <strong><?= (int) $preview['resumen']['filas'] ?></strong>
        </div>
        <div>
            <span>Actas a crear</span>
            <strong><?= (int) $preview['resumen']['asignaciones'] ?></strong>
        </div>
        <div>
            <span>Activos</span>
            <strong><?= (int) $preview['resumen']['activos'] ?></strong>
        </div>
        <div>
            <span>Trabajadores nuevos</span>
            <strong><?= (int) $preview['resumen']['trabajadores_nuevos'] ?></strong>
        </div>
        <div>
            <span>Errores</span>
            <strong><?= (int) $preview['resumen']['errores'] ?></strong>
        </div>
    </section>

    <section class="panel">
        <div class="page-actions">
            <div>
                <h3>Revision antes de crear actas</h3>
                <p>
                    <?= (int) $preview['resumen']['advertencias'] ?> advertencias.
                    <?php if ($preview['bloqueado']): ?>
                        Corrige los errores antes de confirmar.
                    <?php else: ?>
                        Todo esta listo para crear las asignaciones.
                    <?php endif; ?>
                </p>
            </div>

            <form method="post" action="<?= url('asignaciones/regularizar-excel') ?>" style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                <?= csrf_field() ?>
                <button class="btn btn-light" type="submit" name="cancelar" value="1">Cancelar</button>
                <?php if (!$preview['bloqueado']): ?>
                    <button class="btn btn-primary" type="submit" name="confirmar" value="1">Crear actas reales</button>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($preview['bloqueado']): ?>
            <div class="notice notice-warning">
                No se guardo nada todavia. Los activos deben estar disponibles y sin acta vigente.
            </div>
        <?php else: ?>
            <div class="notice notice-success">
                Al confirmar, SIGATI creara actas agrupadas por trabajador y marcara los activos como asignados.
            </div>
        <?php endif; ?>

        <div class="table-responsive" style="margin-top:14px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Estado</th>
                        <th>Responsable Excel</th>
                        <th>Activo</th>
                        <th>Serie / IMEI</th>
                        <th>Area</th>
                        <th>Trabajador</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preview['filas'] as $fila): ?>
                        <?php
                        $erroresFila = $fila['errores'] ?? [];
                        $advertenciasFila = $fila['advertencias'] ?? [];
                        $estado = $erroresFila ? implode(' | ', $erroresFila) : ($advertenciasFila ? implode(' | ', $advertenciasFila) : 'Listo');
                        ?>
                        <tr>
                            <td><?= (int) $fila['numero'] ?></td>
                            <td><?= $mostrar($estado) ?></td>
                            <td><?= $mostrar($fila['responsable']) ?></td>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$fila['activo_id']) ?>">
                                    <?= $mostrar($fila['codigo_activo']) ?>
                                </a>
                                <small><?= $mostrar($fila['codigo_anterior']) ?></small>
                                <small><?= $mostrar($fila['equipo']) ?></small>
                            </td>
                            <td><?= $mostrar($fila['serie']) ?></td>
                            <td><?= $mostrar($fila['area']) ?></td>
                            <td><?= $fila['trabajador_existente'] ? 'Existente' : 'Se creara' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>