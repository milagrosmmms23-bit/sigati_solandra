<div class="page-actions">
        <div>
            <div class="eyebrow">Acta de asignación</div>
            <h2><?= e($registro['assignment_number']) ?></h2>
            <p><?= e($registro['employee_name']) ?> · <?= e($registro['area_name'] ?: 'Sin área') ?></p>
        </div>

        <div class="actions">
            <a class="btn btn-light" target="_blank" href="<?= url('asignaciones/'.$registro['id'].'/imprimir') ?>">
                Imprimir
            </a>
            <a class="btn btn-primary" href="<?= url('asignaciones/'.$registro['id'].'/pdf') ?>">
                Descargar PDF
            </a>
        </div>
    </div>

    <div class="two-columns wide-left">
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
                    <span>Cargo</span>
                    <strong><?= e($registro['position'] ?: '—') ?></strong>
                </div>
                <div>
                    <span>Área</span>
                    <strong><?= e($registro['area_name'] ?: '—') ?></strong>
                </div>
                <div>
                    <span>Estado</span>
                    <strong><?= badge($registro['status']) ?></strong>
                </div>
                <div>
                    <span>Fecha</span>
                    <strong><?= datetime_pe($registro['assigned_at']) ?></strong>
                </div>
            </div>

            <div class="notes-box">
                <span>Observaciones</span>
                <p><?= nl2br(e($registro['notes'] ?: 'Sin observaciones.')) ?></p>
            </div>
        </section>

        <aside class="panel action-panel">
            <h3>Acciones</h3>
            <a class="action-link" href="<?= url('devoluciones/crear?assignment_id='.$registro['id']) ?>">
                <b>↩ Registrar devolución</b>
                <small>Devolver uno o varios equipos</small>
            </a>
            <a class="action-link" target="_blank" href="<?= url('asignaciones/'.$registro['id'].'/imprimir') ?>">
                <b>▤ Vista imprimible</b>
                <small>Firmar manualmente el documento</small>
            </a>
        </aside>
    </div>

    <section class="panel table-panel">
        <div class="panel-head">
            <div>
                <h3>Equipos asignados</h3>
                <p><?= count($registro['items']) ?> elementos en el acta</p>
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
                    <?php foreach ($registro['items'] as $activo): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$activo['asset_id']) ?>">
                                    <?= e($activo['asset_code']) ?>
                                </a>
                            </td>
                            <td><?= e($activo['type_name']) ?></td>
                            <td><?= e(trim(($activo['brand_name'] ?? '').' '.($activo['model_name'] ?? '')) ?: '—') ?></td>
                            <td><?= e($activo['serial_number'] ?: '—') ?></td>
                            <td><?= e($activo['condition_out']) ?></td>
                            <td>
                                <?php if ($activo['returned_at']): ?>
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
