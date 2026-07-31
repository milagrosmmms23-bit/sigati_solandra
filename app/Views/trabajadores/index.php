<div class="page-actions">
        <div>
            <h2>Trabajadores</h2>
            <p>Personas que pueden recibir activos tecnológicos.</p>
        </div>

        <a class="btn btn-primary" href="<?= url('trabajadores/crear') ?>">+ Nuevo trabajador</a>
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
                                <a class="icon-btn" href="<?= url('trabajadores/'.$employee['id'].'/editar') ?>">
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
