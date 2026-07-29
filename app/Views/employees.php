<?php
$mode = $mode ?? 'index';
$errors = $_SESSION['_errors'] ?? [];
$rows = $rows ?? [];
$q = $q ?? '';
$areas = $areas ?? [];
$item = $item ?? null;
?>

<?php if ($mode === 'index'): ?>
    <div class="page-actions">
        <div>
            <h2>Trabajadores</h2>
            <p>Personas que pueden recibir activos tecnológicos.</p>
        </div>

        <a class="btn btn-primary" href="<?= url('employees/create') ?>">+ Nuevo trabajador</a>
    </div>

    <form class="filter-panel" method="get">
        <div class="field grow">
            <label>Buscar</label>
            <input name="q" value="<?= e($q) ?>" placeholder="Código, nombre o cargo">
        </div>
        <button class="btn btn-dark">Buscar</button>
    </form>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Trabajador</th>
                        <th>Cargo</th>
                        <th>Área</th>
                        <th>Contacto</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $employee): ?>
                        <tr>
                            <td><strong><?= e($employee['employee_code']) ?></strong></td>
                            <td><strong><?= e($employee['first_name'].' '.$employee['last_name']) ?></strong></td>
                            <td><?= e($employee['position'] ?: '—') ?></td>
                            <td><?= e($employee['area_name'] ?: 'Sin área') ?></td>
                            <td>
                                <?= e($employee['email'] ?: '—') ?>
                                <small><?= e($employee['phone'] ?: '') ?></small>
                            </td>
                            <td class="text-right">
                                <a class="icon-btn" href="<?= url('employees/'.$employee['id'].'/edit') ?>">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty">No hay trabajadores registrados.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php else: ?>
    <?php $isEdit = !empty($item); ?>

    <div class="page-actions">
        <div>
            <h2><?= $isEdit ? 'Editar trabajador' : 'Nuevo trabajador' ?></h2>
            <p>Registra al responsable que recibirá los equipos.</p>
        </div>

        <a class="btn btn-light" href="<?= url('employees') ?>">Cancelar</a>
    </div>

    <form
        class="form-card compact-card"
        method="post"
        action="<?= $isEdit ? url('employees/'.$item['id']) : url('employees') ?>"
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
            <a class="btn btn-light" href="<?= url('employees') ?>">Cancelar</a>
            <button class="btn btn-primary">
                <?= $isEdit ? 'Guardar cambios' : 'Registrar trabajador' ?>
            </button>
        </div>
    </form>
<?php endif; ?>
