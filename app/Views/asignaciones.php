<?php
$mode = $mode ?? 'index';
$rows = $rows ?? [];
$trabajadores = $trabajadores ?? [];
$activos = $activos ?? [];
$item = $item ?? null;
?>

<?php if ($mode === 'index'): ?>
    <div class="page-actions">
        <div>
            <h2>Asignaciones</h2>
            <p>Actas de entrega de equipos a trabajadores.</p>
        </div>

        <a class="btn btn-primary" href="<?= url('asignaciones/crear') ?>">+ Nueva asignación</a>
    </div>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Acta</th>
                        <th>Trabajador</th>
                        <th>Área</th>
                        <th>Equipos</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $assignment): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('asignaciones/'.$assignment['id']) ?>">
                                    <?= e($assignment['assignment_number']) ?>
                                </a>
                            </td>
                            <td><strong><?= e($assignment['employee_name']) ?></strong></td>
                            <td><?= e($assignment['area_name'] ?: '—') ?></td>
                            <td><?= (int) $assignment['item_count'] ?></td>
                            <td><?= badge($assignment['status']) ?></td>
                            <td><?= datetime_pe($assignment['assigned_at']) ?></td>
                            <td class="text-right">
                                <a
                                    class="icon-btn"
                                    href="<?= url('asignaciones/'.$assignment['id'].'/imprimir') ?>"
                                    target="_blank"
                                >
                                    Imprimir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty">No hay asignaciones.</div>
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
            <h2>Nueva asignación</h2>
            <p>Selecciona trabajador y equipos disponibles.</p>
        </div>

        <a class="btn btn-light" href="<?= url('asignaciones') ?>">Cancelar</a>
    </div>

    <form class="form-card" method="post" action="<?= url('asignaciones') ?>">
        <?= csrf_field() ?>

        <div class="form-section">
            <div class="section-title">
                <span>1</span>
                <div>
                    <h3>Responsable</h3>
                    <p>La asignación tomará el área actual del trabajador.</p>
                </div>
            </div>

            <div class="form-grid cols-2">
                <label>
                    Trabajador *
                    <select name="employee_id" id="employeeSelect" required>
                        <option value="">Seleccionar trabajador</option>
                        <?php foreach ($trabajadores as $employee): ?>
                            <option value="<?= $employee['id'] ?>" data-area="<?= e($employee['area_id']) ?>">
                                <?= e($employee['employee_code'].' · '.$employee['first_name'].' '.$employee['last_name'].' · '.($employee['area_name'] ?? 'Sin área')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <input type="hidden" name="area_id" id="assignmentArea">

                <label>
                    Observaciones
                    <textarea
                        name="notes"
                        rows="2"
                        placeholder="Motivo, condición general o indicaciones"
                    ></textarea>
                </label>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <span>2</span>
                <div>
                    <h3>Equipos disponibles</h3>
                    <p>Marca uno o varios activos para incluirlos en el acta.</p>
                </div>
            </div>

            <div class="asset-picker">
                <div class="picker-search">
                    <input type="search" placeholder="Filtrar por código, tipo, marca o serie" data-filter-activos>
                    <span><b data-selected-count>0</b> seleccionados</span>
                </div>

                <?php foreach ($activos as $asset): ?>
                    <label class="picker-item" data-asset-row>
                        <input type="checkbox" name="asset_ids[]" value="<?= $asset['id'] ?>" data-asset-check>
                        <div>
                            <strong><?= e($asset['asset_code']) ?></strong>
                            <span><?= e($asset['type_name'].' · '.trim(($asset['brand_name'] ?? '').' '.($asset['model_name'] ?? ''))) ?></span>
                            <small>Serie: <?= e($asset['serial_number'] ?: '—') ?></small>
                        </div>
                        <input
                            class="condition-input"
                            name="condition[<?= $asset['id'] ?>]"
                            value="Buen estado"
                            placeholder="Condición de entrega"
                        >
                    </label>
                <?php endforeach; ?>

                <?php if (!$activos): ?>
                    <div class="empty">No hay equipos disponibles.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-footer">
            <a class="btn btn-light" href="<?= url('asignaciones') ?>">Cancelar</a>
            <button class="btn btn-primary" type="submit">Confirmar y generar acta</button>
        </div>
    </form>

<?php elseif ($mode === 'show'): ?>
    <div class="page-actions">
        <div>
            <div class="eyebrow">Acta de asignación</div>
            <h2><?= e($item['assignment_number']) ?></h2>
            <p><?= e($item['employee_name']) ?> · <?= e($item['area_name'] ?: 'Sin área') ?></p>
        </div>

        <div class="actions">
            <a class="btn btn-light" target="_blank" href="<?= url('asignaciones/'.$item['id'].'/imprimir') ?>">
                Imprimir
            </a>
            <a class="btn btn-primary" href="<?= url('asignaciones/'.$item['id'].'/pdf') ?>">
                Descargar PDF
            </a>
        </div>
    </div>

    <div class="two-columns wide-left">
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
                    <span>Cargo</span>
                    <strong><?= e($item['position'] ?: '—') ?></strong>
                </div>
                <div>
                    <span>Área</span>
                    <strong><?= e($item['area_name'] ?: '—') ?></strong>
                </div>
                <div>
                    <span>Estado</span>
                    <strong><?= badge($item['status']) ?></strong>
                </div>
                <div>
                    <span>Fecha</span>
                    <strong><?= datetime_pe($item['assigned_at']) ?></strong>
                </div>
            </div>

            <div class="notes-box">
                <span>Observaciones</span>
                <p><?= nl2br(e($item['notes'] ?: 'Sin observaciones.')) ?></p>
            </div>
        </section>

        <aside class="panel action-panel">
            <h3>Acciones</h3>
            <a class="action-link" href="<?= url('devoluciones/crear?assignment_id='.$item['id']) ?>">
                <b>↩ Registrar devolución</b>
                <small>Devolver uno o varios equipos</small>
            </a>
            <a class="action-link" target="_blank" href="<?= url('asignaciones/'.$item['id'].'/imprimir') ?>">
                <b>▤ Vista imprimible</b>
                <small>Firmar manualmente el documento</small>
            </a>
        </aside>
    </div>

    <section class="panel table-panel">
        <div class="panel-head">
            <div>
                <h3>Equipos asignados</h3>
                <p><?= count($item['items']) ?> elementos en el acta</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Marca / modelo</th>
                        <th>Serie</th>
                        <th>Condición</th>
                        <th>Situación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($item['items'] as $asset): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$asset['asset_id']) ?>">
                                    <?= e($asset['asset_code']) ?>
                                </a>
                            </td>
                            <td><?= e($asset['type_name']) ?></td>
                            <td><?= e(trim(($asset['brand_name'] ?? '').' '.($asset['model_name'] ?? '')) ?: '—') ?></td>
                            <td><?= e($asset['serial_number'] ?: '—') ?></td>
                            <td><?= e($asset['condition_out']) ?></td>
                            <td>
                                <?php if ($asset['returned_at']): ?>
                                    <span class="badge badge-dark">Devuelto</span>
                                <?php else: ?>
                                    <span class="badge badge-primary">Asignado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
