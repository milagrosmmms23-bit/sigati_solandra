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
                    <label class="picker-item" data-activo-fila>
                        <input type="checkbox" name="asset_ids[]" value="<?= $asset['id'] ?>" data-activo-check>
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
