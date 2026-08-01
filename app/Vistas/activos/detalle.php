<?php
    $generalFields = [
        'Tipo' => $registro['nombre_tipo'],
        'Marca' => $registro['nombre_marca'],
        'Modelo' => $registro['nombre_modelo'],
        'Ubicación' => $registro['nombre_ubicacion'],
        'Fecha de compra' => date_pe($registro['fecha_compra']),
        'Factura' => $registro['numero_factura'],
        'Proveedor' => $registro['nombre_proveedor'],
        'Costo' => money($registro['costo']),
        'Fin de garantía' => date_pe($registro['fin_garantia']),
        'Registrado' => datetime_pe($registro['creado_en']),
    ];
    ?>

    <div class="page-actions">
        <div>
            <div class="eyebrow"><?= e($registro['nombre_tipo']) ?></div>
            <h2><?= e($registro['codigo_activo']) ?></h2>
            <p><?= e(trim(($registro['nombre_marca'] ?? '').' '.($registro['nombre_modelo'] ?? '')) ?: 'Sin marca o modelo') ?></p>
        </div>

        <div class="actions">
            <a class="btn btn-light" href="<?= url('activos/'.$registro['id'].'/editar') ?>">Editar</a>
            <a class="btn btn-primary" href="<?= url('asignaciones/crear') ?>">Asignar equipo</a>
        </div>
    </div>

    <div class="asset-detail-grid">
        <section class="panel asset-summary">
            <div class="asset-head">
                <div class="asset-visual"><?= e(substr($registro['nombre_tipo'], 0, 2)) ?></div>
                <div>
                    <h3><?= e($registro['codigo_activo']) ?></h3>
                    <?= badge($registro['nombre_estado']) ?>
                </div>
            </div>

            <img class="qr-image" src="<?= url('activos/'.$registro['id'].'/qr') ?>" alt="QR">
            <small>Escanea para abrir la ficha</small>

            <div class="summary-lines">
                <div>
                    <span>Código anterior</span>
                    <b><?= e($registro['codigo_anterior'] ?: '—') ?></b>
                </div>
                <div>
                    <span>Serie</span>
                    <b><?= e($registro['numero_serie'] ?: '—') ?></b>
                </div>
                <div>
                    <span>Responsable</span>
                    <b><?= e($registro['nombre_trabajador'] ?: 'Sin asignar') ?></b>
                </div>
                <div>
                    <span>Área</span>
                    <b><?= e($registro['nombre_area'] ?: 'Sin área') ?></b>
                </div>
            </div>
        </section>

        <section class="panel detail-panel">
            <div class="tabs">
                <button class="active" data-tab="general">Información</button>
                <button data-tab="technical">Técnica</button>
                <button data-tab="history">Historial</button>
                <button data-tab="maintenance">Mantenimiento</button>
            </div>

            <div class="tab-pane active" data-pane="general">
                <div class="detail-grid">
                    <?php foreach ($generalFields as $label => $valor): ?>
                        <div>
                            <span><?= e($label) ?></span>
                            <strong><?= e($valor ?: '—') ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="notes-box">
                    <span>Observaciones</span>
                    <p><?= nl2br(e($registro['observaciones'] ?: 'Sin observaciones.')) ?></p>
                </div>
            </div>

            <div class="tab-pane" data-pane="technical">
                <div class="detail-grid">
                    <div>
                        <span>Hostname</span>
                        <strong><?= e($registro['nombre_equipo'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>IP</span>
                        <strong><?= e($registro['direccion_ip'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>MAC</span>
                        <strong><?= e($registro['direccion_mac'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>Teléfono</span>
                        <strong><?= e($registro['numero_telefono'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>IMEI 1</span>
                        <strong><?= e($registro['imei1'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>IMEI 2</span>
                        <strong><?= e($registro['imei2'] ?: '—') ?></strong>
                    </div>

                    <?php foreach ($registro['especificaciones'] as $especificacion): ?>
                        <div>
                            <span><?= e($especificacion['clave_especificacion']) ?></span>
                            <strong><?= e($especificacion['valor_especificacion']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="tab-pane" data-pane="history">
                <div class="timeline">
                    <?php foreach ($registro['movimientos'] as $movimiento): ?>
                        <div class="timeline-item">
                            <i></i>
                            <div>
                                <strong><?= e($movimiento['tipo_movimiento']) ?></strong>
                                <p><?= e($movimiento['observaciones'] ?: 'Movimiento registrado') ?></p>
                                <small>
                                    <?= datetime_pe($movimiento['creado_en']) ?> · <?= e($movimiento['nombre_usuario'] ?? 'Sistema') ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!$registro['movimientos']): ?>
                        <div class="empty">Sin movimientos.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane" data-pane="maintenance">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Falla</th>
                                <th>Inicio</th>
                                <th>Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registro['mantenimientos'] as $mantenimiento): ?>
                                <tr>
                                    <td><?= e($mantenimiento['tipo']) ?></td>
                                    <td><?= badge($mantenimiento['estado']) ?></td>
                                    <td><?= e($mantenimiento['problema'] ?: '—') ?></td>
                                    <td><?= date_pe($mantenimiento['iniciado_en']) ?></td>
                                    <td><?= money($mantenimiento['costo']) ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (!$registro['mantenimientos']): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty">Sin mantenimientos.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
