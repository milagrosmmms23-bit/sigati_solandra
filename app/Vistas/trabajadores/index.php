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
                    <?php foreach ($filas as $trabajador): ?>
                        <tr>
                            <td><strong><?= e($trabajador['codigo_trabajador']) ?></strong></td>
                            <td><strong><?= e($trabajador['nombres'].' '.$trabajador['apellidos']) ?></strong></td>
                            <td><?= e($trabajador['cargo'] ?: '—') ?></td>
                            <td><?= e($trabajador['nombre_area'] ?: 'Sin área') ?></td>
                            <td>
                                <?= e($trabajador['correo'] ?: '—') ?>
                                <small><?= e($trabajador['telefono'] ?: '') ?></small>
                            </td>
                            <td class="text-right">
                                <a class="icon-btn" href="<?= url('trabajadores/'.$trabajador['id'].'/editar') ?>">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$filas): ?>
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
