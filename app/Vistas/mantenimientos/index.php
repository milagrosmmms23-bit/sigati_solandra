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
                                <a class="asset-code" href="<?= url('activos/'.$mantenimiento['activo_id']) ?>">
                                    <?= e($mantenimiento['codigo_activo']) ?>
                                </a>
                                <small>
                                    <?= e($mantenimiento['nombre_tipo'].' '.trim(($mantenimiento['nombre_marca'] ?? '').' '.($mantenimiento['nombre_modelo'] ?? ''))) ?>
                                </small>
                            </td>
                            <td><?= e($mantenimiento['tipo']) ?></td>
                            <td><?= e($mantenimiento['problema'] ?: 'Mantenimiento programado') ?></td>
                            <td><?= badge($mantenimiento['estado']) ?></td>
                            <td><?= date_pe($mantenimiento['iniciado_en']) ?></td>
                            <td><?= money($mantenimiento['costo']) ?></td>
                            <td>
                                <?php if ($mantenimiento['estado'] === 'ABIERTO'): ?>
                                    <button
                                        class="icon-btn"
                                        type="button"
                                        data-open-modal="close-<?= $mantenimiento['id'] ?>"
                                    >
                                        Cerrar
                                    </button>
                                <?php else: ?>
                                    <span class="muted">Cerrado <?= date_pe($mantenimiento['finalizado_en']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php if ($mantenimiento['estado'] === 'ABIERTO'): ?>
                            <tr class="modal-row">
                                <td colspan="7">
                                    <dialog id="close-<?= $mantenimiento['id'] ?>" class="modal">
                                        <form method="post" action="<?= url('mantenimientos/'.$mantenimiento['id'].'/cerrar') ?>">
                                            <?= csrf_field() ?>

                                            <div class="modal-head">
                                                <div>
                                                    <h3>Cerrar mantenimiento</h3>
                                                    <p><?= e($mantenimiento['codigo_activo']) ?></p>
                                                </div>
                                                <button type="button" data-close-modal>&times;</button>
                                            </div>

                                            <div class="form-grid cols-2">
                                                <label>
                                                    Diagnostico
                                                    <textarea name="diagnostico" rows="3"><?= e($mantenimiento['diagnostico'] ?? '') ?></textarea>
                                                </label>

                                                <label>
                                                    Acciones realizadas
                                                    <textarea name="acciones" rows="3"><?= e($mantenimiento['acciones'] ?? '') ?></textarea>
                                                </label>

                                                <label>
                                                    Repuestos utilizados
                                                    <input name="repuestos">
                                                </label>

                                                <label>
                                                    Costo total (S/)
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="costo"
                                                        value="<?= e($mantenimiento['costo']) ?>"
                                                    >
                                                </label>

                                                <label>
                                                    Proximo mantenimiento
                                                    <input type="date" name="proxima_fecha">
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
