<?php
    $generalFields = [
        'Tipo' => $item['type_name'],
        'Marca' => $item['brand_name'],
        'Modelo' => $item['model_name'],
        'Ubicación' => $item['location_name'],
        'Fecha de compra' => date_pe($item['purchase_date']),
        'Factura' => $item['invoice_number'],
        'Proveedor' => $item['supplier_name'],
        'Costo' => money($item['cost']),
        'Fin de garantía' => date_pe($item['warranty_end']),
        'Registrado' => datetime_pe($item['created_at']),
    ];
    ?>

    <div class="page-actions">
        <div>
            <div class="eyebrow"><?= e($item['type_name']) ?></div>
            <h2><?= e($item['asset_code']) ?></h2>
            <p><?= e(trim(($item['brand_name'] ?? '').' '.($item['model_name'] ?? '')) ?: 'Sin marca o modelo') ?></p>
        </div>

        <div class="actions">
            <a class="btn btn-light" href="<?= url('activos/'.$item['id'].'/editar') ?>">Editar</a>
            <a class="btn btn-primary" href="<?= url('asignaciones/crear') ?>">Asignar equipo</a>
        </div>
    </div>

    <div class="asset-detail-grid">
        <section class="panel asset-summary">
            <div class="asset-head">
                <div class="asset-visual"><?= e(substr($item['type_name'], 0, 2)) ?></div>
                <div>
                    <h3><?= e($item['asset_code']) ?></h3>
                    <?= badge($item['status_name']) ?>
                </div>
            </div>

            <img class="qr-image" src="<?= url('activos/'.$item['id'].'/qr') ?>" alt="QR">
            <small>Escanea para abrir la ficha</small>

            <div class="summary-lines">
                <div>
                    <span>Código anterior</span>
                    <b><?= e($item['legacy_code'] ?: '—') ?></b>
                </div>
                <div>
                    <span>Serie</span>
                    <b><?= e($item['serial_number'] ?: '—') ?></b>
                </div>
                <div>
                    <span>Responsable</span>
                    <b><?= e($item['employee_name'] ?: 'Sin asignar') ?></b>
                </div>
                <div>
                    <span>Área</span>
                    <b><?= e($item['area_name'] ?: 'Sin área') ?></b>
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
                    <?php foreach ($generalFields as $label => $value): ?>
                        <div>
                            <span><?= e($label) ?></span>
                            <strong><?= e($value ?: '—') ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="notes-box">
                    <span>Observaciones</span>
                    <p><?= nl2br(e($item['notes'] ?: 'Sin observaciones.')) ?></p>
                </div>
            </div>

            <div class="tab-pane" data-pane="technical">
                <div class="detail-grid">
                    <div>
                        <span>Hostname</span>
                        <strong><?= e($item['hostname'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>IP</span>
                        <strong><?= e($item['ip_address'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>MAC</span>
                        <strong><?= e($item['mac_address'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>Teléfono</span>
                        <strong><?= e($item['phone_number'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>IMEI 1</span>
                        <strong><?= e($item['imei1'] ?: '—') ?></strong>
                    </div>
                    <div>
                        <span>IMEI 2</span>
                        <strong><?= e($item['imei2'] ?: '—') ?></strong>
                    </div>

                    <?php foreach ($item['specs'] as $spec): ?>
                        <div>
                            <span><?= e($spec['spec_key']) ?></span>
                            <strong><?= e($spec['spec_value']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="tab-pane" data-pane="history">
                <div class="timeline">
                    <?php foreach ($item['movements'] as $movement): ?>
                        <div class="timeline-item">
                            <i></i>
                            <div>
                                <strong><?= e($movement['movement_type']) ?></strong>
                                <p><?= e($movement['notes'] ?: 'Movimiento registrado') ?></p>
                                <small>
                                    <?= datetime_pe($movement['created_at']) ?> · <?= e($movement['user_name'] ?? 'Sistema') ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!$item['movements']): ?>
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
                            <?php foreach ($item['mantenimientos'] as $maintenance): ?>
                                <tr>
                                    <td><?= e($maintenance['type']) ?></td>
                                    <td><?= badge($maintenance['status']) ?></td>
                                    <td><?= e($maintenance['issue'] ?: '—') ?></td>
                                    <td><?= date_pe($maintenance['started_at']) ?></td>
                                    <td><?= money($maintenance['cost']) ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (!$item['mantenimientos']): ?>
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
