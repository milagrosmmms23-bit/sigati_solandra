<div class="page-actions">
        <div>
            <h2>Inventario tecnologico</h2>
            <p><?= number_format((int) $resultado['total']) ?> activos encontrados.</p>
        </div>

        <div class="actions">
            <a class="btn btn-light" href="<?= url('reportes/inventario/excel') ?>">Descargar Excel</a>
            <a class="btn btn-light" href="<?= url('activos/importar') ?>">Importar</a>
            <a class="btn btn-primary" href="<?= url('activos/crear') ?>">+ Nuevo activo</a>
        </div>
    </div>

    <?php
    $indicadores = [
        ['Total filtrado', $resumen['total'] ?? 0, 'Activos en esta vista', 'blue'],
        ['Asignados', $resumen['asignados'] ?? 0, 'Bajo responsabilidad', 'green'],
        ['Sin responsable', $resumen['sin_responsable'] ?? 0, 'Revisar control', 'orange'],
        ['Sin factura', $resumen['sin_factura'] ?? 0, 'Pendiente documentario', 'orange'],
        ['Garantia vencida', $resumen['garantia_vencida'] ?? 0, 'Revisar reposicion', 'purple'],
        ['Por vencer', $resumen['garantia_por_vencer'] ?? 0, 'Proximos 30 dias', 'cyan'],
        ['Sin serie/IMEI', $resumen['sin_identificador'] ?? 0, 'Completar ficha', 'orange'],
    ];
    ?>

    <div class="inventory-summary">
        <?php foreach ($indicadores as [$etiqueta, $valor, $detalle, $tono]): ?>
            <article class="summary-chip tone-<?= e($tono) ?>">
                <span><?= e($etiqueta) ?></span>
                <strong><?= number_format((int) $valor) ?></strong>
                <small><?= e($detalle) ?></small>
            </article>
        <?php endforeach; ?>
    </div>

    <form class="filter-panel" method="get">
        <div class="field grow">
            <label>Buscar</label>
            <input
                name="q"
                value="<?= e($filtros['q']) ?>"
                placeholder="Codigo, serie, trabajador, area, factura, IMEI o telefono"
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

        <div class="field">
            <label>Responsable</label>
            <select name="responsable">
                <option value="">Todos</option>
                <option value="con_responsable" <?= selected($filtros['responsable'] ?? '', 'con_responsable') ?>>
                    Con responsable
                </option>
                <option value="sin_responsable" <?= selected($filtros['responsable'] ?? '', 'sin_responsable') ?>>
                    Sin responsable
                </option>
            </select>
        </div>

        <div class="field">
            <label>Facturacion</label>
            <select name="facturacion">
                <option value="">Todos</option>
                <option value="facturado" <?= selected($filtros['facturacion'] ?? '', 'facturado') ?>>
                    Facturado
                </option>
                <option value="pendiente" <?= selected($filtros['facturacion'] ?? '', 'pendiente') ?>>
                    Pendiente
                </option>
                <option value="con_factura" <?= selected($filtros['facturacion'] ?? '', 'con_factura') ?>>
                    Con nro. factura
                </option>
                <option value="sin_factura" <?= selected($filtros['facturacion'] ?? '', 'sin_factura') ?>>
                    Sin nro. factura
                </option>
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
                        <th>Facturacion</th>
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
                            <td>
                                <?= e($activo['estado_facturacion'] ?: ($activo['numero_factura'] ? 'Con factura' : 'Pendiente')) ?>
                                <small><?= e($activo['numero_factura'] ?: 'Sin nro. factura') ?></small>
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
                            <td colspan="8">
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
