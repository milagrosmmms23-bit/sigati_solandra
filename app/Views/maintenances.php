<?php
$mode = $mode ?? 'index';
$rows = $rows ?? [];
$assets = $assets ?? [];
?>

<?php if ($mode === 'index'): ?>
    <div class="page-actions">
        <div>
            <h2>Mantenimientos</h2>
            <p>Control preventivo y correctivo de los equipos.</p>
        </div>

        <a class="btn btn-primary" href="<?= url('maintenances/create') ?>">+ Nuevo mantenimiento</a>
    </div>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Tipo</th>
                        <th>Falla / motivo</th>
                        <th>Estado</th>
                        <th>Inicio</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $maintenance): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('assets/'.$maintenance['asset_id']) ?>">
                                    <?= e($maintenance['asset_code']) ?>
                                </a>
                                <small>
                                    <?= e($maintenance['type_name'].' '.trim(($maintenance['brand_name'] ?? '').' '.($maintenance['model_name'] ?? ''))) ?>
                                </small>
                            </td>
                            <td><?= e($maintenance['type']) ?></td>
                            <td><?= e($maintenance['issue'] ?: 'Mantenimiento programado') ?></td>
                            <td><?= badge($maintenance['status']) ?></td>
                            <td><?= date_pe($maintenance['started_at']) ?></td>
                            <td><?= money($maintenance['cost']) ?></td>
                            <td>
                                <?php if ($maintenance['status'] === 'ABIERTO'): ?>
                                    <button
                                        class="icon-btn"
                                        type="button"
                                        data-open-modal="close-<?= $maintenance['id'] ?>"
                                    >
                                        Cerrar
                                    </button>
                                <?php else: ?>
                                    <span class="muted">Cerrado <?= date_pe($maintenance['finished_at']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if ($maintenance['status'] === 'ABIERTO'): ?>
                            <tr class="modal-row">
                                <td colspan="7">
                                    <dialog id="close-<?= $maintenance['id'] ?>" class="modal">
                                        <form method="post" action="<?= url('maintenances/'.$maintenance['id'].'/close') ?>">
                                            <?= csrf_field() ?>

                                            <div class="modal-head">
                                                <div>
                                                    <h3>Cerrar mantenimiento</h3>
                                                    <p><?= e($maintenance['asset_code']) ?></p>
                                                </div>
                                                <button type="button" data-close-modal>×</button>
                                            </div>

                                            <div class="form-grid cols-2">
                                                <label>
                                                    Diagnóstico
                                                    <textarea name="diagnosis" rows="3"><?= e($maintenance['diagnosis'] ?? '') ?></textarea>
                                                </label>

                                                <label>
                                                    Acciones realizadas
                                                    <textarea name="actions" rows="3"><?= e($maintenance['actions'] ?? '') ?></textarea>
                                                </label>

                                                <label>
                                                    Repuestos utilizados
                                                    <input name="parts">
                                                </label>

                                                <label>
                                                    Costo total (S/)
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="cost"
                                                        value="<?= e($maintenance['cost']) ?>"
                                                    >
                                                </label>

                                                <label>
                                                    Próximo mantenimiento
                                                    <input type="date" name="next_date">
                                                </label>
                                            </div>

                                            <div class="form-footer">
                                                <button type="button" class="btn btn-light" data-close-modal>
                                                    Cancelar
                                                </button>
                                                <button class="btn btn-primary">Cerrar mantenimiento</button>
                                            </div>
                                        </form>
                                    </dialog>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty">No hay mantenimientos registrados.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php else: ?>
    <div class="page-actions">
        <div>
            <h2>Nuevo mantenimiento</h2>
            <p>Abre una orden técnica y cambia el equipo a mantenimiento.</p>
        </div>

        <a class="btn btn-light" href="<?= url('maintenances') ?>">Cancelar</a>
    </div>

    <form class="form-card compact-card" method="post" action="<?= url('maintenances') ?>">
        <?= csrf_field() ?>

        <div class="form-grid cols-2">
            <label class="span-2">
                Activo *
                <select name="asset_id" required>
                    <option value="">Seleccionar equipo</option>
                    <?php foreach ($assets as $asset): ?>
                        <option value="<?= $asset['id'] ?>">
                            <?= e($asset['asset_code'].' · '.$asset['type_name'].' · '.trim(($asset['brand_name'] ?? '').' '.($asset['model_name'] ?? '')).' · Serie '.($asset['serial_number'] ?: '—')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Tipo
                <select name="type">
                    <option>PREVENTIVO</option>
                    <option>CORRECTIVO</option>
                </select>
            </label>

            <label>
                Costo inicial (S/)
                <input type="number" step="0.01" min="0" name="cost" value="0">
            </label>

            <label class="span-2">
                Falla o motivo
                <textarea name="issue" rows="3" required placeholder="Describe el motivo del mantenimiento"></textarea>
            </label>

            <label>
                Diagnóstico inicial
                <textarea name="diagnosis" rows="3"></textarea>
            </label>

            <label>
                Acciones iniciales
                <textarea name="actions" rows="3"></textarea>
            </label>
        </div>

        <div class="form-footer">
            <a class="btn btn-light" href="<?= url('maintenances') ?>">Cancelar</a>
            <button class="btn btn-primary">Abrir mantenimiento</button>
        </div>
    </form>
<?php endif; ?>
