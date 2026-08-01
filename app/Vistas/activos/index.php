<div class="page-actions">
        <div>
            <h2>Inventario tecnológico</h2>
            <p><?= number_format((int) $resultado['total']) ?> activos encontrados.</p>
        </div>

        <div class="actions">
            <a class="btn btn-light" href="<?= url('activos/importar') ?>">Importar CSV</a>
            <a class="btn btn-primary" href="<?= url('activos/crear') ?>">+ Nuevo activo</a>
        </div>
    </div>

    <form class="filter-panel" method="get">
        <div class="field grow">
            <label>Buscar</label>
            <input
                name="q"
                value="<?= e($filtros['q']) ?>"
                placeholder="Código, serie, hostname, IMEI o teléfono"
            >
        </div>

        <div class="field">
            <label>Tipo</label>
            <select name="type_id">
                <option value="">Todos</option>
                <?php foreach ($tipos as $tipo): ?>
                    <option value="<?= $tipo['id'] ?>" <?= selected($filtros['type_id'], $tipo['id']) ?>>
                        <?= e($tipo['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Estado</label>
            <select name="status_id">
                <option value="">Todos</option>
                <?php foreach ($estados as $estado): ?>
                    <option value="<?= $estado['id'] ?>" <?= selected($filtros['status_id'], $estado['id']) ?>>
                        <?= e($estado['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Área</label>
            <select name="area_id">
                <option value="">Todas</option>
                <?php foreach ($areas as $area): ?>
                    <option value="<?= $area['id'] ?>" <?= selected($filtros['area_id'], $area['id']) ?>>
                        <?= e($area['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="btn btn-dark" type="submit">Filtrar</button>
    </form>

    <section class="panel table-panel">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Equipo</th>
                        <th>Serie</th>
                        <th>Área / responsable</th>
                        <th>Estado</th>
                        <th>Actualización</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultado['filas'] as $activo): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$activo['id']) ?>">
                                    <?= e($activo['asset_code']) ?>
                                </a>
                                <small><?= e($activo['legacy_code'] ?: 'Sin código anterior') ?></small>
                            </td>
                            <td>
                                <strong><?= e($activo['type_name']) ?></strong>
                                <small>
                                    <?= e(trim(($activo['brand_name'] ?? '').' '.($activo['model_name'] ?? '')) ?: 'Sin marca/modelo') ?>
                                </small>
                            </td>
                            <td>
                                <?= e($activo['serial_number'] ?: '—') ?>
                                <small><?= e($activo['hostname'] ?: '') ?></small>
                            </td>
                            <td>
                                <?= e($activo['area_name'] ?: 'Sin área') ?>
                                <small><?= e($activo['employee_name'] ?: 'Sin responsable') ?></small>
                            </td>
                            <td><?= badge($activo['status_name']) ?></td>
                            <td><?= date_pe($activo['updated_at'] ?: $activo['created_at']) ?></td>
                            <td class="text-right">
                                <a class="icon-btn" href="<?= url('activos/'.$activo['id'].'/editar') ?>">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$resultado['filas']): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty">No se encontraron activos.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($resultado['paginas'] > 1): ?>
            <div class="pagination">
                <?php for ($pagina = 1; $pagina <= $resultado['paginas']; $pagina++): ?>
                    <?php $query = http_build_query(array_merge($filtros, ['page' => $pagina])); ?>
                    <a class="<?= $pagina === $resultado['pagina'] ? 'active' : '' ?>" href="?<?= e($query) ?>">
                        <?= $pagina ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
