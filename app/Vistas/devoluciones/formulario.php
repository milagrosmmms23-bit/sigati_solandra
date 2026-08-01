<div class="page-actions">
        <div>
            <h2>Nueva devolución</h2>
            <p>Selecciona una asignación vigente y evalúa cada equipo.</p>
        </div>

        <a class="btn btn-light" href="<?= url('devoluciones') ?>">Cancelar</a>
    </div>

    <form class="form-card" method="get" action="<?= url('devoluciones/crear') ?>">
        <div class="form-grid cols-2">
            <label>
                Asignación vigente
                <select name="assignment_id" onchange="this.form.submit()">
                    <option value="">Seleccionar</option>
                    <?php foreach ($asignaciones as $asignacionActiva): ?>
                        <option
                            value="<?= $asignacionActiva['id'] ?>"
                            <?= selected($asignacion['id'] ?? '', $asignacionActiva['id']) ?>
                        >
                            <?= e($asignacionActiva['assignment_number'].' · '.$asignacionActiva['employee_name'].' · '.$asignacionActiva['pending'].' pendientes') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="align-end">
                <noscript>
                    <button class="btn btn-dark">Cargar</button>
                </noscript>
            </div>
        </div>
    </form>

    <?php if ($asignacion): ?>
        <form class="form-card" method="post" action="<?= url('devoluciones') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="assignment_id" value="<?= $asignacion['id'] ?>">

            <div class="assignment-banner">
                <div>
                    <span>Asignación</span>
                    <strong><?= e($asignacion['assignment_number']) ?></strong>
                </div>
                <div>
                    <span>Trabajador</span>
                    <strong><?= e($asignacion['employee_name']) ?></strong>
                </div>
                <div>
                    <span>Área</span>
                    <strong><?= e($asignacion['area_name'] ?: '—') ?></strong>
                </div>
            </div>

            <div class="return-list">
                <?php foreach ($asignacion['items'] as $itemAsignacion): ?>
                    <?php if ($itemAsignacion['returned_at']) continue; ?>

                    <div class="return-item">
                        <label class="check-title">
                            <input type="checkbox" name="item_ids[]" value="<?= $itemAsignacion['id'] ?>">
                            <div>
                                <strong>
                                    <?= e($itemAsignacion['asset_code']) ?> · <?= e($itemAsignacion['type_name']) ?>
                                </strong>
                                <span>
                                    <?= e(trim(($itemAsignacion['brand_name'] ?? '').' '.($itemAsignacion['model_name'] ?? ''))) ?>
                                    · Serie <?= e($itemAsignacion['serial_number'] ?: '—') ?>
                                </span>
                            </div>
                        </label>

                        <div class="return-fields">
                            <label>
                                Condición al devolver
                                <input name="condition[<?= $itemAsignacion['id'] ?>]" value="Buen estado">
                            </label>

                            <label>
                                Daños o faltantes
                                <input name="damage[<?= $itemAsignacion['id'] ?>]" placeholder="Sin daños">
                            </label>

                            <label>
                                Estado posterior
                                <select name="status_id[<?= $itemAsignacion['id'] ?>]">
                                    <?php foreach ($estados as $estado): ?>
                                        <option
                                            value="<?= $estado['id'] ?>"
                                            <?= $estado['code'] === 'DISPONIBLE' ? 'selected' : '' ?>
                                        >
                                            <?= e($estado['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <label class="full-label">
                Observaciones generales
                <textarea name="notes" rows="3"></textarea>
            </label>

            <div class="form-footer">
                <a class="btn btn-light" href="<?= url('devoluciones') ?>">Cancelar</a>
                <button class="btn btn-primary">Registrar devolución y generar acta</button>
            </div>
        </form>
    <?php else: ?>
        <section class="panel">
            <div class="empty">Selecciona una asignación para cargar sus equipos pendientes.</div>
        </section>
    <?php endif; ?>
