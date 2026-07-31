<div class="page-actions">
        <div>
            <div class="eyebrow">Acta de devolución</div>
            <h2><?= e($item['return_number']) ?></h2>
            <p><?= e($item['employee_name']) ?> · Referencia <?= e($item['assignment_number']) ?></p>
        </div>

        <div class="actions">
            <a class="btn btn-light" target="_blank" href="<?= url('devoluciones/'.$item['id'].'/imprimir') ?>">
                Imprimir
            </a>
            <a class="btn btn-primary" href="<?= url('devoluciones/'.$item['id'].'/pdf') ?>">
                Descargar PDF
            </a>
        </div>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div>
                <span>Trabajador</span>
                <strong><?= e($item['employee_name']) ?></strong>
            </div>
            <div>
                <span>Código</span>
                <strong><?= e($item['employee_code']) ?></strong>
            </div>
            <div>
                <span>Área</span>
                <strong><?= e($item['area_name'] ?: '—') ?></strong>
            </div>
            <div>
                <span>Fecha</span>
                <strong><?= datetime_pe($item['returned_at']) ?></strong>
            </div>
        </div>
    </section>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Equipo</th>
                        <th>Condición</th>
                        <th>Daños / faltantes</th>
                        <th>Estado posterior</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($item['items'] as $asset): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$asset['asset_id']) ?>">
                                    <?= e($asset['asset_code']) ?>
                                </a>
                            </td>
                            <td>
                                <?= e($asset['type_name'].' '.trim(($asset['brand_name'] ?? '').' '.($asset['model_name'] ?? ''))) ?>
                                <small>Serie: <?= e($asset['serial_number'] ?: '—') ?></small>
                            </td>
                            <td><?= e($asset['condition_in']) ?></td>
                            <td><?= e($asset['damage_notes'] ?: 'Sin daños') ?></td>
                            <td><?= badge($asset['next_status_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
