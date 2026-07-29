<?php $mode = $mode ?? 'index'; ?>

<?php if ($mode === 'index'): ?>
    <div class="page-actions">
        <div>
            <h2>Devoluciones</h2>
            <p>Recepción y evaluación de equipos entregados.</p>
        </div>

        <a class="btn btn-primary" href="<?= url('returns/create') ?>">+ Nueva devolución</a>
    </div>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Acta</th>
                        <th>Asignación</th>
                        <th>Trabajador</th>
                        <th>Equipos</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $return): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('returns/'.$return['id']) ?>">
                                    <?= e($return['return_number']) ?>
                                </a>
                            </td>
                            <td><?= e($return['assignment_number']) ?></td>
                            <td><?= e($return['employee_name']) ?></td>
                            <td><?= (int) $return['item_count'] ?></td>
                            <td><?= datetime_pe($return['returned_at']) ?></td>
                            <td class="text-right">
                                <a class="icon-btn" target="_blank" href="<?= url('returns/'.$return['id'].'/print') ?>">
                                    Imprimir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty">No hay devoluciones.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($mode === 'form'): ?>
    <div class="page-actions">
        <div>
            <h2>Nueva devolución</h2>
            <p>Selecciona una asignación vigente y evalúa cada equipo.</p>
        </div>

        <a class="btn btn-light" href="<?= url('returns') ?>">Cancelar</a>
    </div>

    <form class="form-card" method="get" action="<?= url('returns/create') ?>">
        <div class="form-grid cols-2">
            <label>
                Asignación vigente
                <select name="assignment_id" onchange="this.form.submit()">
                    <option value="">Seleccionar</option>
                    <?php foreach ($assignments as $activeAssignment): ?>
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
        <form class="form-card" method="post" action="<?= url('returns') ?>">
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
                <a class="btn btn-light" href="<?= url('returns') ?>">Cancelar</a>
                <button class="btn btn-primary">Registrar devolución y generar acta</button>
            </div>
        </form>
    <?php else: ?>
        <section class="panel">
            <div class="empty">Selecciona una asignación para cargar sus equipos pendientes.</div>
        </section>
    <?php endif; ?>

<?php elseif ($mode === 'show'): ?>
    <div class="page-actions">
        <div>
            <div class="eyebrow">Acta de devolución</div>
            <h2><?= e($item['return_number']) ?></h2>
            <p><?= e($item['employee_name']) ?> · Referencia <?= e($item['assignment_number']) ?></p>
        </div>

        <div class="actions">
            <a class="btn btn-light" target="_blank" href="<?= url('returns/'.$item['id'].'/print') ?>">
                Imprimir
            </a>
            <a class="btn btn-primary" href="<?= url('returns/'.$item['id'].'/pdf') ?>">
                Descargar PDF
            </a>
        </div>
    </div>

    <section class="panel">
        <div class="detail-grid">
            <div>
                <span>Trabajador</span>
                <strong><?= e($item['employee_name']) ?></strong>
            </div>
            <div>
                <span>Código</span>
                <strong><?= e($item['employee_code']) ?></strong>
            </div>
            <div>
                <span>Área</span>
                <strong><?= e($item['area_name'] ?: '—') ?></strong>
            </div>
            <div>
                <span>Fecha</span>
                <strong><?= datetime_pe($item['returned_at']) ?></strong>
            </div>
        </div>
    </section>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Equipo</th>
                        <th>Condición</th>
                        <th>Daños / faltantes</th>
                        <th>Estado posterior</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($item['items'] as $asset): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('assets/'.$asset['asset_id']) ?>">
                                    <?= e($asset['asset_code']) ?>
                                </a>
                            </td>
                            <td>
                                <?= e($asset['type_name'].' '.trim(($asset['brand_name'] ?? '').' '.($asset['model_name'] ?? ''))) ?>
                                <small>Serie: <?= e($asset['serial_number'] ?: '—') ?></small>
                            </td>
                            <td><?= e($asset['condition_in']) ?></td>
                            <td><?= e($asset['damage_notes'] ?: 'Sin daños') ?></td>
                            <td><?= badge($asset['next_status_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
