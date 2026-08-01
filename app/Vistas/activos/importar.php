<div class="page-actions">
        <div>
            <h2>Importar inventario</h2>
            <p>Carga activos desde una plantilla CSV separada por comas.</p>
        </div>

        <a class="btn btn-light" href="<?= url('activos') ?>">Volver</a>
    </div>

    <div class="two-columns">
        <form
            class="form-card compact-card"
            method="post"
            enctype="multipart/form-data"
            action="<?= url('activos/importar') ?>"
        >
            <?= csrf_field() ?>
            <h3>Seleccionar archivo</h3>
            <p>Usa la plantilla incluida en la carpeta <code>database</code>.</p>

            <label class="file-drop">
                <input type="file" name="csv" accept=".csv,text/csv" required>
                <span>Arrastra o selecciona tu archivo CSV</span>
                <small>Máximo recomendado: 2,000 filas por carga</small>
            </label>

            <button class="btn btn-primary btn-block" type="submit">
                Importar inventario
            </button>
        </form>

        <section class="panel">
            <h3>Columnas reconocidas</h3>
            <div class="code-block">
                tipo, codigo_anterior, marca, modelo, serie, area, ubicacion, nombre_equipo, ip, mac,
                imei1, imei2, telefono, fecha_compra, factura, proveedor, costo, fin_garantia,
                observaciones
            </div>
            <div class="notice">
                <strong>Importante:</strong>
                los tipos deben coincidir con los catálogos del sistema. Las marcas, modelos,
                áreas, ubicaciones y proveedores que no existan serán creados.
            </div>
        </section>
    </div>
