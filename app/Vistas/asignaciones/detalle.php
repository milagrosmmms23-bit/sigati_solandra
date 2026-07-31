<div class="page-actions">
        <div>
            <div class="eyebrow">Acta de asignación</div>
            <h2><?= e($item['assignment_number']) ?></h2>
            <p><?= e($item['employee_name']) ?> · <?= e($item['area_name'] ?: 'Sin área') ?></p>
        </div>

        <div class="actions">
            <a class="btn btn-light" target="_blank" href="<?= url('asignaciones/'.$item['id'].'/imprimir') ?>">
                Imprimir
            </a>
            <a class="btn btn-primary" href="<?= url('asignaciones/'.$item['id'].'/pdf') ?>">
                Descargar PDF
            </a>
        </div>
    </div>

    <div class="two-columns wide-left">
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
                    <span>Cargo</span>
                    <strong><?= e($item['position'] ?: '—') ?></strong>
                </div>
                <div>
                    <span>Área</span>
                    <strong><?= e($item['area_name'] ?: '—') ?></strong>
                </div>
                <div>
                    <span>Estado</span>
                    <strong><?= badge($item['status']) ?></strong>
                </div>
                <div>
                    <span>Fecha</span>
                    <strong><?= datetime_pe($item['assigned_at']) ?></strong>
                </div>
            </div>

            <div class="notes-box">
                <span>Observaciones</span>
                <p><?= nl2br(e($item['notes'] ?: 'Sin observaciones.')) ?></p>
            </div>
        </section>

        <aside class="panel action-panel">
            <h3>Acciones</h3>
            <a class="action-link" href="<?= url('devoluciones/crear?assignment_id='.$item['id']) ?>">
                <b>↩ Registrar devolución</b>
                <small>Devolver uno o varios equipos</small>
            </a>
            <a class="action-link" target="_blank" href="<?= url('asignaciones/'.$item['id'].'/imprimir') ?>">
                <b>▤ Vista imprimible</b>
                <small>Firmar manualmente el documento</small>
            </a>
        </aside>
    </div>

    <section class="panel table-panel">
        <div class="panel-head">
            <div>
                <h3>Equipos asignados</h3>
                <p><?= count($item['items']) ?> elementos en el acta</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Marca / modelo</th>
                        <th>Serie</th>
                        <th>Condición</th>
                        <th>Situación</th>
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
                            <td><?= e($asset['type_name']) ?></td>
                            <td><?= e(trim(($asset['brand_name'] ?? '').' '.($asset['model_name'] ?? '')) ?: '—') ?></td>
                            <td><?= e($asset['serial_number'] ?: '—') ?></td>
                            <td><?= e($asset['condition_out']) ?></td>
                            <td>
                                <?php if ($asset['returned_at']): ?>
                                    <span class="badge badge-dark">Devuelto</span>
                                <?php else: ?>
                                    <span class="badge badge-primary">Asignado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
