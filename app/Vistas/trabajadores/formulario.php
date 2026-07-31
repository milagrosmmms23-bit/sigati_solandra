<?php $isEdit = !empty($item); ?>

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
        action="<?= $isEdit ? url('trabajadores/'.$item['id']) : url('trabajadores') ?>"
    >
        <?= csrf_field() ?>

        <div class="form-grid cols-2">
            <label>
                Código de trabajador *
                <input
                    name="employee_code"
                    required
                    value="<?= e(old('employee_code', $item['employee_code'] ?? '')) ?>"
                >

                <?php if (isset($errors['employee_code'])): ?>
                    <small class="error"><?= e($errors['employee_code']) ?></small>
                <?php endif; ?>
            </label>

            <label>
                Área
                <select name="area_id">
                    <option value="">Sin área</option>
                    <?php foreach ($areas as $area): ?>
                        <option
                            value="<?= $area['id'] ?>"
                            <?= selected(old('area_id', $item['area_id'] ?? ''), $area['id']) ?>
                        >
                            <?= e($area['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Nombres *
                <input name="first_name" required value="<?= e(old('first_name', $item['first_name'] ?? '')) ?>">
            </label>

            <label>
                Apellidos *
                <input name="last_name" required value="<?= e(old('last_name', $item['last_name'] ?? '')) ?>">
            </label>

            <label>
                Cargo
                <input name="position" value="<?= e(old('position', $item['position'] ?? '')) ?>">
            </label>

            <label>
                Teléfono
                <input name="phone" value="<?= e(old('phone', $item['phone'] ?? '')) ?>">
            </label>

            <label class="span-2">
                Correo
                <input type="email" name="email" value="<?= e(old('email', $item['email'] ?? '')) ?>">
            </label>
        </div>

        <div class="form-footer">
            <a class="btn btn-light" href="<?= url('trabajadores') ?>">Cancelar</a>
            <button class="btn btn-primary">
                <?= $isEdit ? 'Guardar cambios' : 'Registrar trabajador' ?>
            </button>
        </div>
    </form>
