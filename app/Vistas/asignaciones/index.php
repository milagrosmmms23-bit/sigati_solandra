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
                    <?php foreach ($rows as $assignment): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('asignaciones/'.$assignment['id']) ?>">
                                    <?= e($assignment['assignment_number']) ?>
                                </a>
                            </td>
                            <td><strong><?= e($assignment['employee_name']) ?></strong></td>
                            <td><?= e($assignment['area_name'] ?: '—') ?></td>
                            <td><?= (int) $assignment['item_count'] ?></td>
                            <td><?= badge($assignment['status']) ?></td>
                            <td><?= datetime_pe($assignment['assigned_at']) ?></td>
                            <td class="text-right">
                                <a
                                    class="icon-btn"
                                    href="<?= url('asignaciones/'.$assignment['id'].'/imprimir') ?>"
                                    target="_blank"
                                >
                                    Imprimir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
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
