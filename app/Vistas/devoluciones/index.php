<div class="page-actions">
        <div>
            <h2>Devoluciones</h2>
            <p>Recepcion y evaluacion de equipos entregados.</p>
        </div>

        <a class="btn btn-primary" href="<?= url('devoluciones/crear') ?>">+ Nueva devolucion</a>
    </div>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Acta</th>
                        <th>Asignacion</th>
                        <th>Trabajador</th>
                        <th>Equipos</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filas as $devolucion): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('devoluciones/'.$devolucion['id']) ?>">
                                    <?= e($devolucion['numero_devolucion']) ?>
                                </a>
                            </td>
                            <td><?= e($devolucion['numero_asignacion']) ?></td>
                            <td><?= e($devolucion['nombre_trabajador']) ?></td>
                            <td><?= (int) $devolucion['cantidad_items'] ?></td>
                            <td><?= datetime_pe($devolucion['devuelto_en']) ?></td>
                            <td class="text-right">
                                <a class="icon-btn" target="_blank" href="<?= url('devoluciones/'.$devolucion['id'].'/imprimir') ?>">
                                    Imprimir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$filas): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty">No hay devoluciones.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
