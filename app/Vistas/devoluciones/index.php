<div class="page-actions">
        <div>
            <h2>Devoluciones</h2>
            <p>Recepción y evaluación de equipos entregados.</p>
        </div>

        <a class="btn btn-primary" href="<?= url('devoluciones/crear') ?>">+ Nueva devolución</a>
    </div>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Acta</th>
                        <th>Asignación</th>
                        <th>Trabajador</th>
                        <th>Equipos</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $return): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('devoluciones/'.$return['id']) ?>">
                                    <?= e($return['return_number']) ?>
                                </a>
                            </td>
                            <td><?= e($return['assignment_number']) ?></td>
                            <td><?= e($return['employee_name']) ?></td>
                            <td><?= (int) $return['item_count'] ?></td>
                            <td><?= datetime_pe($return['returned_at']) ?></td>
                            <td class="text-right">
                                <a class="icon-btn" target="_blank" href="<?= url('devoluciones/'.$return['id'].'/imprimir') ?>">
                                    Imprimir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
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
