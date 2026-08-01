<div class="page-actions">
        <div>
            <div class="eyebrow">Acta de asignación</div>
            <h2><?= e($registro['numero_asignacion']) ?></h2>
            <p><?= e($registro['nombre_trabajador']) ?> · <?= e($registro['nombre_area'] ?: 'Sin área') ?></p>
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
                    <strong><?= e($registro['nombre_trabajador']) ?></strong>
                </div>
                <div>
                    <span>Código</span>
                    <strong><?= e($registro['codigo_trabajador']) ?></strong>
                </div>
                <div>
                    <span>Cargo</span>
                    <strong><?= e($registro['cargo'] ?: '—') ?></strong>
                </div>
                <div>
                    <span>Área</span>
                    <strong><?= e($registro['nombre_area'] ?: '—') ?></strong>
                </div>
                <div>
                    <span>Estado</span>
                    <strong><?= badge($registro['estado']) ?></strong>
                </div>
                <div>
                    <span>Fecha</span>
                    <strong><?= datetime_pe($registro['asignado_en']) ?></strong>
                </div>
            </div>

            <div class="notes-box">
                <span>Observaciones</span>
                <p><?= nl2br(e($registro['observaciones'] ?: 'Sin observaciones.')) ?></p>
            </div>
        </section>

        <aside class="panel action-panel">
            <h3>Acciones</h3>
            <a class="action-link" href="<?= url('devoluciones/crear?asignacion_id='.$registro['id']) ?>">
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
                <p><?= count($registro['elementos']) ?> elementos en el acta</p>
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
                    <?php foreach ($registro['elementos'] as $activo): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$activo['activo_id']) ?>">
                                    <?= e($activo['codigo_activo']) ?>
                                </a>
                            </td>
                            <td><?= e($activo['nombre_tipo']) ?></td>
                            <td><?= e(trim(($activo['nombre_marca'] ?? '').' '.($activo['nombre_modelo'] ?? '')) ?: '—') ?></td>
                            <td><?= e($activo['numero_serie'] ?: '—') ?></td>
                            <td><?= e($activo['condicion_salida']) ?></td>
                            <td>
                                <?php if ($activo['devuelto_en']): ?>
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
