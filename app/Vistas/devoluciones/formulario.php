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
                <select name="asignacion_id" onchange="this.form.submit()">
                    <option value="">Seleccionar</option>
                    <?php foreach ($asignaciones as $asignacionActiva): ?>
                        <option
                            value="<?= $asignacionActiva['id'] ?>"
                            <?= selected($asignacion['id'] ?? '', $asignacionActiva['id']) ?>
                        >
                            <?= e($asignacionActiva['numero_asignacion'].' · '.$asignacionActiva['nombre_trabajador'].' · '.$asignacionActiva['pendientes'].' pendientes') ?>
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
            <input type="hidden" name="asignacion_id" value="<?= $asignacion['id'] ?>">

            <div class="assignment-banner">
                <div>
                    <span>Asignación</span>
                    <strong><?= e($asignacion['numero_asignacion']) ?></strong>
                </div>
                <div>
                    <span>Trabajador</span>
                    <strong><?= e($asignacion['nombre_trabajador']) ?></strong>
                </div>
                <div>
                    <span>Área</span>
                    <strong><?= e($asignacion['nombre_area'] ?: '—') ?></strong>
                </div>
            </div>

            <div class="return-list">
                <?php foreach ($asignacion['elementos'] as $itemAsignacion): ?>
                    <?php if ($itemAsignacion['devuelto_en']) continue; ?>

                    <div class="return-item">
                        <label class="check-title">
                            <input type="checkbox" name="item_asignacion_ids[]" value="<?= $itemAsignacion['id'] ?>">
                            <div>
                                <strong>
                                    <?= e($itemAsignacion['codigo_activo']) ?> · <?= e($itemAsignacion['nombre_tipo']) ?>
                                </strong>
                                <span>
                                    <?= e(trim(($itemAsignacion['nombre_marca'] ?? '').' '.($itemAsignacion['nombre_modelo'] ?? ''))) ?>
                                    · Serie <?= e($itemAsignacion['numero_serie'] ?: '—') ?>
                                </span>
                            </div>
                        </label>

                        <div class="return-fields">
                            <label>
                                Condición al devolver
                                <input name="condicion[<?= $itemAsignacion['id'] ?>]" value="Buen estado">
                            </label>

                            <label>
                                Daños o faltantes
                                <input name="danos[<?= $itemAsignacion['id'] ?>]" placeholder="Sin daños">
                            </label>

                            <label>
                                Estado posterior
                                <select name="estado_id[<?= $itemAsignacion['id'] ?>]">
                                    <?php foreach ($estados as $estado): ?>
                                        <option
                                            value="<?= $estado['id'] ?>"
                                            <?= $estado['codigo'] === 'DISPONIBLE' ? 'selected' : '' ?>
                                        >
                                            <?= e($estado['nombre']) ?>
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
                <textarea name="observaciones" rows="3"></textarea>
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
