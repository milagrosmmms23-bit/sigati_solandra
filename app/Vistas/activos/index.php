<div class="page-actions">
        <div>
            <h2>Inventario tecnologico</h2>
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
                placeholder="Codigo, serie, nombre_equipo, IMEI o telefono"
            >
        </div>

        <div class="field">
            <label>Tipo</label>
            <select name="tipo_activo_id">
                <option value="">Todos</option>
                <?php foreach ($tipos as $tipo): ?>
                    <option value="<?= $tipo['id'] ?>" <?= selected($filtros['tipo_activo_id'], $tipo['id']) ?>>
                        <?= e($tipo['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Estado</label>
            <select name="estado_id">
                <option value="">Todos</option>
                <?php foreach ($estados as $estado): ?>
                    <option value="<?= $estado['id'] ?>" <?= selected($filtros['estado_id'], $estado['id']) ?>>
                        <?= e($estado['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Area</label>
            <select name="area_id">
                <option value="">Todas</option>
                <?php foreach ($areas as $area): ?>
                    <option value="<?= $area['id'] ?>" <?= selected($filtros['area_id'], $area['id']) ?>>
                        <?= e($area['nombre']) ?>
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
                        <th>Codigo</th>
                        <th>Equipo</th>
                        <th>Serie</th>
                        <th>Area / responsable</th>
                        <th>Estado</th>
                        <th>Actualizacion</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultado['filas'] as $activo): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$activo['id']) ?>">
                                    <?= e($activo['codigo_activo']) ?>
                                </a>
                                <small><?= e($activo['codigo_anterior'] ?: 'Sin codigo anterior') ?></small>
                            </td>
                            <td>
                                <strong><?= e($activo['nombre_tipo']) ?></strong>
                                <small>
                                    <?= e(trim(($activo['nombre_marca'] ?? '').' '.($activo['nombre_modelo'] ?? '')) ?: 'Sin marca/modelo') ?>
                                </small>
                            </td>
                            <td>
                                <?= e($activo['numero_serie'] ?: '-') ?>
                                <small><?= e($activo['nombre_equipo'] ?: '') ?></small>
                            </td>
                            <td>
                                <?= e($activo['nombre_area'] ?: 'Sin area') ?>
                                <small><?= e($activo['nombre_trabajador'] ?: 'Sin responsable') ?></small>
                            </td>
                            <td><?= badge($activo['nombre_estado']) ?></td>
                            <td><?= date_pe($activo['actualizado_en'] ?: $activo['creado_en']) ?></td>
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
