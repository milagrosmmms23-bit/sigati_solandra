<?php
$preview = $preview ?? null;
$mostrar = static fn ($valor): string => htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
$columnas = [
    'tipo' => 'Tipo', 'codigo_anterior' => 'Codigo', 'marca' => 'Marca', 'modelo' => 'Modelo',
    'serie' => 'Serie', 'area' => 'Area', 'ubicacion' => 'Ubicacion', 'nombre_equipo' => 'Nombre equipo',
    'imei1' => 'IMEI', 'telefono' => 'Telefono', 'observaciones' => 'Observaciones',
];
?>
<div class="page-actions">
    <div>
        <h2>Importar inventario</h2>
        <p>Primero analiza tu CSV, corrige observaciones y luego confirma la carga.</p>
    </div>
    <a class="btn btn-light" href="<?= url('activos') ?>">Volver</a>
</div>

<div class="two-columns">
    <form class="form-card compact-card" method="post" enctype="multipart/form-data" action="<?= url('activos/importar') ?>">
        <?= csrf_field() ?>
        <h3>Seleccionar archivo</h3>
        <p>Sube un CSV separado por comas o punto y coma.</p>
        <label class="file-drop">
            <input type="file" name="csv" accept=".csv,text/csv" required>
            <span>Arrastra o selecciona tu archivo CSV</span>
            <small>Maximo recomendado: 2,000 filas por carga</small>
        </label>
        <button class="btn btn-primary btn-block" type="submit">Analizar archivo</button>
    </form>

    <section class="panel">
        <h3>Columnas aceptadas</h3>
        <div class="code-block">
            tipo, codigo_anterior, marca, modelo, serie, area, ubicacion, nombre_equipo, ip, mac,
            imei1, imei2, telefono, fecha_compra, factura, proveedor, costo, fin_garantia, observaciones
        </div>
        <div class="notice">
            Tambien reconoce nombres parecidos: codigo, item, serial, chip_de_linea, imei, fecha_entrega y numero_factura.
        </div>
    </section>
</div>

<?php if ($preview): ?>
    <section class="panel" style="margin-top:18px;">
        <div class="page-actions">
            <div>
                <h3>Revision de <?= $mostrar($preview['archivo']) ?></h3>
                <p>
                    <?= (int) $preview['resumen']['total'] ?> filas leidas,
                    <?= (int) $preview['resumen']['validas'] ?> listas,
                    <?= (int) $preview['resumen']['errores'] ?> errores,
                    <?= (int) $preview['resumen']['advertencias'] ?> advertencias.
                </p>
            </div>
            <form method="post" action="<?= url('activos/importar') ?>" style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                <?= csrf_field() ?>
                <button class="btn btn-light" type="submit" name="cancelar" value="1">Cancelar</button>
                <?php if (!$preview['bloqueado']): ?>
                    <button class="btn btn-primary" type="submit" name="confirmar" value="1">Confirmar importacion</button>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($preview['bloqueado']): ?>
            <div class="notice danger">Hay errores que corregir en el CSV. No se guardo nada todavia.</div>
        <?php else: ?>
            <div class="notice success">El archivo esta listo para importar.</div>
        <?php endif; ?>

        <div class="table-responsive" style="margin-top:14px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fila</th>
                        <th>Estado</th>
                        <?php foreach ($columnas as $etiqueta): ?>
                            <th><?= $mostrar($etiqueta) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($preview['filas'], 0, 80) as $fila): ?>
                        <?php
                        $erroresFila = $fila['errores'] ?? [];
                        $advertenciasFila = $fila['advertencias'] ?? [];
                        $estado = $erroresFila ? implode(' | ', $erroresFila) : ($advertenciasFila ? implode(' | ', $advertenciasFila) : 'Listo');
                        ?>
                        <tr>
                            <td><?= (int) $fila['numero'] ?></td>
                            <td><?= $mostrar($estado) ?></td>
                            <?php foreach (array_keys($columnas) as $campo): ?>
                                <td><?= $mostrar($fila['datos'][$campo] ?? '') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>