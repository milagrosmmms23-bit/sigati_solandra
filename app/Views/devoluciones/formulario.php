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
                    <?php foreach ($asignaciones as $activeAssignment): ?>
                        <option
                            value="<?= $activeAssignment['id'] ?>"
                            <?= selected($assignment['id'] ?? '', $activeAssignment['id']) ?>
                        >
                            <?= e($activeAssignment['assignment_number'].' · '.$activeAssignment['employee_name'].' · '.$activeAssignment['pending'].' pendientes') ?>
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

    <?php if ($assignment): ?>
        <form class="form-card" method="post" action="<?= url('devoluciones') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">

            <div class="assignment-banner">
                <div>
                    <span>Asignación</span>
                    <strong><?= e($assignment['assignment_number']) ?></strong>
                </div>
                <div>
                    <span>Trabajador</span>
                    <strong><?= e($assignment['employee_name']) ?></strong>
                </div>
                <div>
                    <span>Área</span>
                    <strong><?= e($assignment['area_name'] ?: '—') ?></strong>
                </div>
            </div>

            <div class="return-list">
                <?php foreach ($assignment['items'] as $assignmentItem): ?>
                    <?php if ($assignmentItem['returned_at']) continue; ?>

                    <div class="return-item">
                        <label class="check-title">
                            <input type="checkbox" name="item_ids[]" value="<?= $assignmentItem['id'] ?>">
                            <div>
                                <strong>
                                    <?= e($assignmentItem['asset_code']) ?> · <?= e($assignmentItem['type_name']) ?>
                                </strong>
                                <span>
                                    <?= e(trim(($assignmentItem['brand_name'] ?? '').' '.($assignmentItem['model_name'] ?? ''))) ?>
                                    · Serie <?= e($assignmentItem['serial_number'] ?: '—') ?>
                                </span>
                            </div>
                        </label>

                        <div class="return-fields">
                            <label>
                                Condición al devolver
                                <input name="condition[<?= $assignmentItem['id'] ?>]" value="Buen estado">
                            </label>

                            <label>
                                Daños o faltantes
                                <input name="damage[<?= $assignmentItem['id'] ?>]" placeholder="Sin daños">
                            </label>

                            <label>
                                Estado posterior
                                <select name="status_id[<?= $assignmentItem['id'] ?>]">
                                    <?php foreach ($statuses as $status): ?>
                                        <option
                                            value="<?= $status['id'] ?>"
                                            <?= $status['code'] === 'DISPONIBLE' ? 'selected' : '' ?>
                                        >
                                            <?= e($status['name']) ?>
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
