<div class="page-actions">
        <div>
            <div class="eyebrow">Acta de devolución</div>
            <h2><?= e($registro['numero_devolucion']) ?></h2>
            <p><?= e($registro['nombre_trabajador']) ?> · Referencia <?= e($registro['numero_asignacion']) ?></p>
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
                <strong><?= e($registro['nombre_trabajador']) ?></strong>
            </div>
            <div>
                <span>Código</span>
                <strong><?= e($registro['codigo_trabajador']) ?></strong>
            </div>
            <div>
                <span>Área</span>
                <strong><?= e($registro['nombre_area'] ?: '—') ?></strong>
            </div>
            <div>
                <span>Fecha</span>
                <strong><?= datetime_pe($registro['devuelto_en']) ?></strong>
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
                    <?php foreach ($registro['elementos'] as $activo): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$activo['activo_id']) ?>">
                                    <?= e($activo['codigo_activo']) ?>
                                </a>
                            </td>
                            <td>
                                <?= e($activo['nombre_tipo'].' '.trim(($activo['nombre_marca'] ?? '').' '.($activo['nombre_modelo'] ?? ''))) ?>
                                <small>Serie: <?= e($activo['numero_serie'] ?: '—') ?></small>
                            </td>
                            <td><?= e($activo['condicion_entrada']) ?></td>
                            <td><?= e($activo['observaciones_danos'] ?: 'Sin daños') ?></td>
                            <td><?= badge($activo['nombre_siguiente_estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
