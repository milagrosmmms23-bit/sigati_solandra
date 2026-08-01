<?php $isEdit = !empty($registro); ?>

    <div class="page-actions">
        <div>
            <h2><?= $isEdit ? 'Editar trabajador' : 'Nuevo trabajador' ?></h2>
            <p>Registra al responsable que recibirá los equipos.</p>
        </div>

        <a class="btn btn-light" href="<?= url('trabajadores') ?>">Cancelar</a>
    </div>

    <form
        class="form-card compact-card"
        method="post"
        action="<?= $isEdit ? url('trabajadores/'.$registro['id']) : url('trabajadores') ?>"
    >
        <?= csrf_field() ?>

        <div class="form-grid cols-2">
            <label>
                Código de trabajador *
                <input
                    name="codigo_trabajador"
                    required
                    value="<?= e(old('codigo_trabajador', $registro['codigo_trabajador'] ?? '')) ?>"
                >

                <?php if (isset($errores['codigo_trabajador'])): ?>
                    <small class="error"><?= e($errores['codigo_trabajador']) ?></small>
                <?php endif; ?>
            </label>

            <label>
                Área
                <select name="area_id">
                    <option value="">Sin área</option>
                    <?php foreach ($areas as $area): ?>
                        <option
                            value="<?= $area['id'] ?>"
                            <?= selected(old('area_id', $registro['area_id'] ?? ''), $area['id']) ?>
                        >
                            <?= e($area['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Nombres *
                <input name="nombres" required value="<?= e(old('nombres', $registro['nombres'] ?? '')) ?>">
            </label>

            <label>
                Apellidos *
                <input name="apellidos" required value="<?= e(old('apellidos', $registro['apellidos'] ?? '')) ?>">
            </label>

            <label>
                Cargo
                <input name="cargo" value="<?= e(old('cargo', $registro['cargo'] ?? '')) ?>">
            </label>

            <label>
                Teléfono
                <input name="telefono" value="<?= e(old('telefono', $registro['telefono'] ?? '')) ?>">
            </label>

            <label class="span-2">
                Correo
                <input type="email" name="correo" value="<?= e(old('correo', $registro['correo'] ?? '')) ?>">
            </label>
        </div>

        <div class="form-footer">
            <a class="btn btn-light" href="<?= url('trabajadores') ?>">Cancelar</a>
            <button class="btn btn-primary">
                <?= $isEdit ? 'Guardar cambios' : 'Registrar trabajador' ?>
            </button>
        </div>
    </form>
