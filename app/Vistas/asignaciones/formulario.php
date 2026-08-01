<div class="page-actions">
        <div>
            <h2>Nueva asignacion</h2>
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
                    <p>La asignacion tomara el area actual del trabajador.</p>
                </div>
            </div>

            <div class="form-grid cols-2">
                <label>
                    Trabajador *
                    <select name="trabajador_id" id="employeeSelect" required>
                        <option value="">Seleccionar trabajador</option>
                        <?php foreach ($trabajadores as $trabajador): ?>
                            <option value="<?= $trabajador['id'] ?>" data-area="<?= e($trabajador['area_id']) ?>">
                                <?= e($trabajador['codigo_trabajador'].' - '.$trabajador['nombres'].' '.$trabajador['apellidos'].' - '.($trabajador['nombre_area'] ?? 'Sin area')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <input type="hidden" name="area_id" id="assignmentArea">

                <label>
                    Observaciones
                    <textarea
                        name="observaciones"
                        rows="2"
                        placeholder="Motivo, condicion general o indicaciones"
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
                    <input type="search" placeholder="Filtrar por codigo, tipo, marca o serie" data-filter-activos>
                    <span><b data-selected-count>0</b> seleccionados</span>
                </div>

                <?php foreach ($activos as $activo): ?>
                    <label class="picker-item" data-activo-fila>
                        <input type="checkbox" name="activo_ids[]" value="<?= $activo['id'] ?>" data-activo-check>
                        <div>
                            <strong><?= e($activo['codigo_activo']) ?></strong>
                            <span><?= e($activo['nombre_tipo'].' - '.trim(($activo['nombre_marca'] ?? '').' '.($activo['nombre_modelo'] ?? ''))) ?></span>
                            <small>Serie: <?= e($activo['numero_serie'] ?: '-') ?></small>
                        </div>
                        <input
                            class="condition-input"
                            name="condicion[<?= $activo['id'] ?>]"
                            value="Buen estado"
                            placeholder="Condicion de entrega"
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
