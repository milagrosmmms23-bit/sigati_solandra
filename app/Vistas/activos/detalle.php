<?php
    $generalFields = [
        'Tipo' => $registro['type_name'],
        'Marca' => $registro['brand_name'],
        'Modelo' => $registro['model_name'],
        'Ubicación' => $registro['location_name'],
        'Fecha de compra' => date_pe($registro['purchase_date']),
        'Factura' => $registro['invoice_number'],
        'Proveedor' => $registro['supplier_name'],
        'Costo' => money($registro['cost']),
        'Fin de garantía' => date_pe($registro['warranty_end']),
        'Registrado' => datetime_pe($registro['created_at']),
    ];
    ?>

    <div class="page-actions">
        <div>
            <div class="eyebrow"><?= e($registro['type_name']) ?></div>
            <h2><?= e($registro['asset_code']) ?></h2>
            <p><?= e(trim(($registro['brand_name'] ?? '').' '.($registro['model_name'] ?? '')) ?: 'Sin marca o modelo') ?></p>
        </div>

        <div class="actions">
            <a class="btn btn-light" href="<?= url('activos/'.$registro['id'].'/editar') ?>">Editar</a>
            <a class="btn btn-primary" href="<?= url('asignaciones/crear') ?>">Asignar equipo</a>
        </div>
    </div>

    <div class="asset-detail-grid">
        <section class="panel asset-summary">
            <div class="asset-head">
                <div class="asset-visual"><?= e(substr($registro['type_name'], 0, 2)) ?></div>
                <div>
                    <h3><?= e($registro['asset_code']) ?></h3>
                    <?= badge($registro['status_name']) ?>
                </div>
            </div>

            <img class="qr-image" src="<?= url('activos/'.$registro['id'].'/qr') ?>" alt="QR">
            <small>Escanea para abrir la ficha</small>

            <div class="summary-lines">
                <div>
                    <span>Código anterior</span>
                    <b><?= e($registro['legacy_code'] ?: '—') ?></b>
                </div>
                <div>
                    <span>Serie</span>
                    <b><?= e($registro['serial_number'] ?: '—') ?></b>
                </div>
                <div>
                    <span>Responsable</span>
                    <b><?= e($registro['employee_name'] ?: 'Sin asignar') ?></b>
                </div>
                <div>
                    <span>Área</span>
                    <b><?= e($registro['area_name'] ?: 'Sin área') ?></b>
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
                    <p><?= nl2br(e($registro['notes'] ?: 'Sin observaciones.')) ?></p>
                </div>
            </div>

            <div class="tab-pane" data-pane="technical">
                <div class="detail-grid">
                    <div>
                        <span>Hostname</span>
                        <strong><?= e($registro['hostname'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>IP</span>
                        <strong><?= e($registro['ip_address'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>MAC</span>
                        <strong><?= e($registro['mac_address'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>Teléfono</span>
                        <strong><?= e($registro['phone_number'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>IMEI 1</span>
                        <strong><?= e($registro['imei1'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>IMEI 2</span>
                        <strong><?= e($registro['imei2'] ?: '—') ?></strong>
                    </div>

                    <?php foreach ($registro['specs'] as $especificacion): ?>
                        <div>
                            <span><?= e($especificacion['spec_key']) ?></span>
                            <strong><?= e($especificacion['spec_value']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="tab-pane" data-pane="history">
                <div class="timeline">
                    <?php foreach ($registro['movements'] as $movimiento): ?>
                        <div class="timeline-item">
                            <i></i>
                            <div>
                                <strong><?= e($movimiento['movement_type']) ?></strong>
                                <p><?= e($movimiento['notes'] ?: 'Movimiento registrado') ?></p>
                                <small>
                                    <?= datetime_pe($movimiento['created_at']) ?> · <?= e($movimiento['user_name'] ?? 'Sistema') ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!$registro['movements']): ?>
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
                                    <td><?= e($mantenimiento['type']) ?></td>
                                    <td><?= badge($mantenimiento['status']) ?></td>
                                    <td><?= e($mantenimiento['issue'] ?: '—') ?></td>
                                    <td><?= date_pe($mantenimiento['started_at']) ?></td>
                                    <td><?= money($mantenimiento['cost']) ?></td>
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
