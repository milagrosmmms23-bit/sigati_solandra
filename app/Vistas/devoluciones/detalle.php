<div class="page-actions">
        <div>
            <div class="eyebrow">Acta de devolución</div>
            <h2><?= e($registro['return_number']) ?></h2>
            <p><?= e($registro['employee_name']) ?> · Referencia <?= e($registro['assignment_number']) ?></p>
        </div>

        <div class="actions">
            <a class="btn btn-light" target="_blank" href="<?= url('devoluciones/'.$registro['id'].'/imprimir') ?>">
                Imprimir
            </a>
            <a class="btn btn-primary" href="<?= url('devoluciones/'.$registro['id'].'/pdf') ?>">
                Descargar PDF
            </a>
        </div>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div>
                <span>Trabajador</span>
                <strong><?= e($registro['employee_name']) ?></strong>
            </div>
            <div>
                <span>Código</span>
                <strong><?= e($registro['employee_code']) ?></strong>
            </div>
            <div>
                <span>Área</span>
                <strong><?= e($registro['area_name'] ?: '—') ?></strong>
            </div>
            <div>
                <span>Fecha</span>
                <strong><?= datetime_pe($registro['returned_at']) ?></strong>
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
                    <?php foreach ($registro['items'] as $activo): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$activo['asset_id']) ?>">
                                    <?= e($activo['asset_code']) ?>
                                </a>
                            </td>
                            <td>
                                <?= e($activo['type_name'].' '.trim(($activo['brand_name'] ?? '').' '.($activo['model_name'] ?? ''))) ?>
                                <small>Serie: <?= e($activo['serial_number'] ?: '—') ?></small>
                            </td>
                            <td><?= e($activo['condition_in']) ?></td>
                            <td><?= e($activo['damage_notes'] ?: 'Sin daños') ?></td>
                            <td><?= badge($activo['next_status_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
