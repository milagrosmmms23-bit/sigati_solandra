<div class="page-actions">
        <div>
            <h2>Mantenimientos</h2>
            <p>Control preventivo y correctivo de los equipos.</p>
        </div>

        <a class="btn btn-primary" href="<?= url('mantenimientos/crear') ?>">+ Nuevo mantenimiento</a>
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
                                <a class="asset-code" href="<?= url('activos/'.$maintenance['asset_id']) ?>">
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
                                        <form method="post" action="<?= url('mantenimientos/'.$maintenance['id'].'/cerrar') ?>">
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
