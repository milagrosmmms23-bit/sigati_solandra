<div class="page-actions">
        <div>
            <h2>Inventario tecnológico</h2>
            <p><?= number_format((int) $result['total']) ?> activos encontrados.</p>
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
                value="<?= e($filters['q']) ?>"
                placeholder="Código, serie, hostname, IMEI o teléfono"
            >
        </div>

        <div class="field">
            <label>Tipo</label>
            <select name="type_id">
                <option value="">Todos</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type['id'] ?>" <?= selected($filters['type_id'], $type['id']) ?>>
                        <?= e($type['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Estado</label>
            <select name="status_id">
                <option value="">Todos</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status['id'] ?>" <?= selected($filters['status_id'], $status['id']) ?>>
                        <?= e($status['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Área</label>
            <select name="area_id">
                <option value="">Todas</option>
                <?php foreach ($areas as $area): ?>
                    <option value="<?= $area['id'] ?>" <?= selected($filters['area_id'], $area['id']) ?>>
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
                    <?php foreach ($result['rows'] as $asset): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$asset['id']) ?>">
                                    <?= e($asset['asset_code']) ?>
                                </a>
                                <small><?= e($asset['legacy_code'] ?: 'Sin código anterior') ?></small>
                            </td>
                            <td>
                                <strong><?= e($asset['type_name']) ?></strong>
                                <small>
                                    <?= e(trim(($asset['brand_name'] ?? '').' '.($asset['model_name'] ?? '')) ?: 'Sin marca/modelo') ?>
                                </small>
                            </td>
                            <td>
                                <?= e($asset['serial_number'] ?: '—') ?>
                                <small><?= e($asset['hostname'] ?: '') ?></small>
                            </td>
                            <td>
                                <?= e($asset['area_name'] ?: 'Sin área') ?>
                                <small><?= e($asset['employee_name'] ?: 'Sin responsable') ?></small>
                            </td>
                            <td><?= badge($asset['status_name']) ?></td>
                            <td><?= date_pe($asset['updated_at'] ?: $asset['created_at']) ?></td>
                            <td class="text-right">
                                <a class="icon-btn" href="<?= url('activos/'.$asset['id'].'/editar') ?>">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$result['rows']): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty">No se encontraron activos.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($result['pages'] > 1): ?>
            <div class="pagination">
                <?php for ($page = 1; $page <= $result['pages']; $page++): ?>
                    <?php $query = http_build_query(array_merge($filters, ['page' => $page])); ?>
                    <a class="<?= $page === $result['page'] ? 'active' : '' ?>" href="?<?= e($query) ?>">
                        <?= $page ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
