<?php $filas = $filas ?? []; ?>

<div class="page-actions">
    <div>
        <h2>Reporte de inventario</h2>
        <p><?= number_format(count($filas)) ?> activos registrados en SIGATI.</p>
    </div>

    <div class="actions">
        <a class="btn btn-light" href="javascript:window.print()">Imprimir</a>
        <a class="btn btn-primary" href="<?= url('reportes/inventario/csv') ?>">Descargar CSV</a>
    </div>
</div>

<section class="panel report-summary">
    <div>
        <span>Total</span>
        <strong><?= count($filas) ?></strong>
    </div>
    <div>
        <span>Con responsable</span>
        <strong><?= count(array_filter($filas, fn ($fila) => !empty($fila['responsable']))) ?></strong>
    </div>
    <div>
        <span>Sin serie</span>
        <strong><?= count(array_filter($filas, fn ($fila) => empty($fila['serie']))) ?></strong>
    </div>
    <div>
        <span>Sin área</span>
        <strong><?= count(array_filter($filas, fn ($fila) => empty($fila['area']))) ?></strong>
    </div>
</section>

<section class="panel table-panel">
    <div class="table-responsive">
        <table class="data-table dense">
            <thead>
                <tr>
                    <?php if ($filas): ?>
                        <?php foreach (array_keys($filas[0]) as $encabezado): ?>
                            <th><?= e(ucwords(str_replace('_', ' ', $encabezado))) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filas as $fila): ?>
                    <tr>
                        <?php foreach ($fila as $valor): ?>
                            <td><?= e($valor ?? '—') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
