<div class="page-actions">
        <div>
            <h2>Asignaciones</h2>
            <p>Actas de entrega de equipos a trabajadores.</p>
        </div>

        <a class="btn btn-primary" href="<?= url('asignaciones/crear') ?>">+ Nueva asignación</a>
    </div>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Acta</th>
                        <th>Trabajador</th>
                        <th>Área</th>
                        <th>Equipos</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filas as $asignacion): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('asignaciones/'.$asignacion['id']) ?>">
                                    <?= e($asignacion['numero_asignacion']) ?>
                                </a>
                            </td>
                            <td><strong><?= e($asignacion['nombre_trabajador']) ?></strong></td>
                            <td><?= e($asignacion['nombre_area'] ?: '—') ?></td>
                            <td><?= (int) $asignacion['cantidad_items'] ?></td>
                            <td><?= badge($asignacion['estado']) ?></td>
                            <td><?= datetime_pe($asignacion['asignado_en']) ?></td>
                            <td class="text-right">
                                <a
                                    class="icon-btn"
                                    href="<?= url('asignaciones/'.$asignacion['id'].'/imprimir') ?>"
                                    target="_blank"
                                >
                                    Imprimir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$filas): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty">No hay asignaciones.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
