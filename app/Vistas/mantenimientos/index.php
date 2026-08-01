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
                    <?php foreach ($filas as $mantenimiento): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$mantenimiento['asset_id']) ?>">
                                    <?= e($mantenimiento['asset_code']) ?>
                                </a>
                                <small>
                                    <?= e($mantenimiento['type_name'].' '.trim(($mantenimiento['brand_name'] ?? '').' '.($mantenimiento['model_name'] ?? ''))) ?>
                                </small>
                            </td>
                            <td><?= e($mantenimiento['type']) ?></td>
                            <td><?= e($mantenimiento['issue'] ?: 'Mantenimiento programado') ?></td>
                            <td><?= badge($mantenimiento['status']) ?></td>
                            <td><?= date_pe($mantenimiento['started_at']) ?></td>
                            <td><?= money($mantenimiento['cost']) ?></td>
                            <td>
                                <?php if ($mantenimiento['status'] === 'ABIERTO'): ?>
                                    <button
                                        class="icon-btn"
                                        type="button"
                                        data-open-modal="close-<?= $mantenimiento['id'] ?>"
                                    >
                                        Cerrar
                                    </button>
                                <?php else: ?>
                                    <span class="muted">Cerrado <?= date_pe($mantenimiento['finished_at']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if ($mantenimiento['status'] === 'ABIERTO'): ?>
                            <tr class="modal-row">
                                <td colspan="7">
                                    <dialog id="close-<?= $mantenimiento['id'] ?>" class="modal">
                                        <form method="post" action="<?= url('mantenimientos/'.$mantenimiento['id'].'/cerrar') ?>">
                                            <?= csrf_field() ?>

                                            <div class="modal-head">
                                                <div>
                                                    <h3>Cerrar mantenimiento</h3>
                                                    <p><?= e($mantenimiento['asset_code']) ?></p>
                                                </div>
                                                <button type="button" data-close-modal>×</button>
                                            </div>

                                            <div class="form-grid cols-2">
                                                <label>
                                                    Diagnóstico
                                                    <textarea name="diagnosis" rows="3"><?= e($mantenimiento['diagnosis'] ?? '') ?></textarea>
                                                </label>

                                                <label>
                                                    Acciones realizadas
                                                    <textarea name="actions" rows="3"><?= e($mantenimiento['actions'] ?? '') ?></textarea>
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
                                                        value="<?= e($mantenimiento['cost']) ?>"
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

                    <?php if (!$filas): ?>
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
