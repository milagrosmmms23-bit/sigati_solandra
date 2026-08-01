<div class="page-actions">
        <div>
            <h2>Nuevo mantenimiento</h2>
            <p>Abre una orden técnica y cambia el equipo a mantenimiento.</p>
        </div>

        <a class="btn btn-light" href="<?= url('mantenimientos') ?>">Cancelar</a>
    </div>

    <form class="form-card compact-card" method="post" action="<?= url('mantenimientos') ?>">
        <?= csrf_field() ?>

        <div class="form-grid cols-2">
            <label class="span-2">
                Activo *
                <select name="asset_id" required>
                    <option value="">Seleccionar equipo</option>
                    <?php foreach ($activos as $activo): ?>
                        <option value="<?= $activo['id'] ?>">
                            <?= e($activo['asset_code'].' · '.$activo['type_name'].' · '.trim(($activo['brand_name'] ?? '').' '.($activo['model_name'] ?? '')).' · Serie '.($activo['serial_number'] ?: '—')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Tipo
                <select name="type">
                    <option>PREVENTIVO</option>
                    <option>CORRECTIVO</option>
                </select>
            </label>

            <label>
                Costo inicial (S/)
                <input type="number" step="0.01" min="0" name="cost" value="0">
            </label>

            <label class="span-2">
                Falla o motivo
                <textarea name="issue" rows="3" required placeholder="Describe el motivo del mantenimiento"></textarea>
            </label>

            <label>
                Diagnóstico inicial
                <textarea name="diagnosis" rows="3"></textarea>
            </label>

            <label>
                Acciones iniciales
                <textarea name="actions" rows="3"></textarea>
            </label>
        </div>

        <div class="form-footer">
            <a class="btn btn-light" href="<?= url('mantenimientos') ?>">Cancelar</a>
            <button class="btn btn-primary">Abrir mantenimiento</button>
        </div>
    </form>
