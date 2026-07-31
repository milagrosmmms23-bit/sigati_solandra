<?php $rows = $rows ?? []; ?>

<div class="page-actions">
    <div>
        <h2>Reporte de inventario</h2>
        <p><?= number_format(count($rows)) ?> activos registrados en SIGATI.</p>
    </div>

    <div class="actions">
        <a class="btn btn-light" href="javascript:window.print()">Imprimir</a>
        <a class="btn btn-primary" href="<?= url('reportes/inventario/csv') ?>">Descargar CSV</a>
    </div>
</div>

<section class="panel report-summary">
    <div>
        <span>Total</span>
        <strong><?= count($rows) ?></strong>
    </div>
    <div>
        <span>Con responsable</span>
        <strong><?= count(array_filter($rows, fn ($row) => !empty($row['responsable']))) ?></strong>
    </div>
    <div>
        <span>Sin serie</span>
        <strong><?= count(array_filter($rows, fn ($row) => empty($row['serie']))) ?></strong>
    </div>
    <div>
        <span>Sin área</span>
        <strong><?= count(array_filter($rows, fn ($row) => empty($row['area']))) ?></strong>
    </div>
</section>

<section class="panel table-panel">
    <div class="table-responsive">
        <table class="data-table dense">
            <thead>
                <tr>
                    <?php if ($rows): ?>
                        <?php foreach (array_keys($rows[0]) as $heading): ?>
                            <th><?= e(ucwords(str_replace('_', ' ', $heading))) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?= e($value ?? '—') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
