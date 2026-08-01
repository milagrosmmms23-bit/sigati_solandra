<?php
    $isEdit = !empty($registro);
    $selectedStatus = old('estado_id', $registro['estado_id'] ?? '');
    $filasEspecificaciones = $registro['especificaciones'] ?? [['clave_especificacion' => '', 'valor_especificacion' => '']];

    if (!$filasEspecificaciones) {
        $filasEspecificaciones = [['clave_especificacion' => '', 'valor_especificacion' => '']];
    }
    ?>

    <div class="page-actions">
        <div>
            <h2><?= $isEdit ? 'Editar activo' : 'Registrar activo' ?></h2>
            <p>Los campos con * son obligatorios.</p>
        </div>

        <a class="btn btn-light" href="<?= $isEdit ? url('activos/'.$registro['id']) : url('activos') ?>">
            Cancelar
        </a>
    </div>

    <form
        class="form-card"
        method="post"
        action="<?= $isEdit ? url('activos/'.$registro['id']) : url('activos') ?>"
    >
        <?= csrf_field() ?>

        <div class="form-section">
            <div class="section-title">
                <span>1</span>
                <div>
                    <h3>Identificación</h3>
                    <p>Datos principales y clasificación del equipo.</p>
                </div>
            </div>

            <div class="form-grid cols-3">
                <label>
                    Tipo de activo *
                    <select name="tipo_activo_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option
                                value="<?= $tipo['id'] ?>"
                                <?= selected(old('tipo_activo_id', $registro['tipo_activo_id'] ?? ''), $tipo['id']) ?>
                            >
                                <?= e($tipo['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if (isset($errores['tipo_activo_id'])): ?>
                        <small class="error"><?= e($errores['tipo_activo_id']) ?></small>
                    <?php endif; ?>
                </label>

                <label>
                    Estado *
                    <select name="estado_id" required>
                        <?php foreach ($estados as $estado): ?>
                            <?php
                            $isDefaultStatus = $selectedStatus === '' && ($estado['codigo'] ?? '') === 'DISPONIBLE';
                            $isSelectedStatus = (string) $selectedStatus === (string) $estado['id'];
                            ?>
                            <option
                                value="<?= $estado['id'] ?>"
                                <?= ($isSelectedStatus || $isDefaultStatus) ? 'selected' : '' ?>
                            >
                                <?= e($estado['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Código anterior
                    <input
                        name="codigo_anterior"
                        value="<?= e(old('codigo_anterior', $registro['codigo_anterior'] ?? '')) ?>"
                        placeholder="Ej. FT277701"
                    >
                </label>

                <label>
                    Marca
                    <select name="marca_id">
                        <option value="">Sin definir</option>
                        <?php foreach ($marcas as $marca): ?>
                            <option
                                value="<?= $marca['id'] ?>"
                                <?= selected(old('marca_id', $registro['marca_id'] ?? ''), $marca['id']) ?>
                            >
                                <?= e($marca['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Modelo
                    <select name="modelo_id">
                        <option value="">Sin definir</option>
                        <?php foreach ($modelos as $modelo): ?>
                            <option
                                value="<?= $modelo['id'] ?>"
                                <?= selected(old('modelo_id', $registro['modelo_id'] ?? ''), $modelo['id']) ?>
                            >
                                <?= e($modelo['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Número de serie
                    <input
                        name="numero_serie"
                        value="<?= e(old('numero_serie', $registro['numero_serie'] ?? '')) ?>"
                        placeholder="Serie del fabricante"
                    >
                </label>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <span>2</span>
                <div>
                    <h3>Ubicación y red</h3>
                    <p>Localización actual e identificación técnica.</p>
                </div>
            </div>

            <div class="form-grid cols-3">
                <label>
                    Área
                    <select name="area_actual_id">
                        <option value="">Sin área</option>
                        <?php foreach ($areas as $area): ?>
                            <option
                                value="<?= $area['id'] ?>"
                                <?= selected(old('area_actual_id', $registro['area_actual_id'] ?? ''), $area['id']) ?>
                            >
                                <?= e($area['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Ubicación
                    <select name="ubicacion_id">
                        <option value="">Sin ubicación</option>
                        <?php foreach ($ubicaciones as $ubicacion): ?>
                            <option
                                value="<?= $ubicacion['id'] ?>"
                                <?= selected(old('ubicacion_id', $registro['ubicacion_id'] ?? ''), $ubicacion['id']) ?>
                            >
                                <?= e($ubicacion['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Hostname
                    <input
                        name="nombre_equipo"
                        value="<?= e(old('nombre_equipo', $registro['nombre_equipo'] ?? '')) ?>"
                        placeholder="PC-AQP-001"
                    >
                </label>

                <label>
                    Dirección IP
                    <input
                        name="direccion_ip"
                        value="<?= e(old('direccion_ip', $registro['direccion_ip'] ?? '')) ?>"
                        placeholder="192.168.x.x"
                    >
                </label>

                <label>
                    Dirección MAC
                    <input
                        name="direccion_mac"
                        value="<?= e(old('direccion_mac', $registro['direccion_mac'] ?? '')) ?>"
                        placeholder="00:00:00:00:00:00"
                    >
                </label>

                <label>
                    Teléfono
                    <input
                        name="numero_telefono"
                        value="<?= e(old('numero_telefono', $registro['numero_telefono'] ?? '')) ?>"
                        placeholder="Número corporativo"
                    >
                </label>

                <label>
                    IMEI 1
                    <input name="imei1" value="<?= e(old('imei1', $registro['imei1'] ?? '')) ?>">
                </label>

                <label>
                    IMEI 2
                    <input name="imei2" value="<?= e(old('imei2', $registro['imei2'] ?? '')) ?>">
                </label>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <span>3</span>
                <div>
                    <h3>Compra y garantía</h3>
                    <p>Información administrativa del activo.</p>
                </div>
            </div>

            <div class="form-grid cols-3">
                <label>
                    Fecha de compra
                    <input
                        type="date"
                        name="fecha_compra"
                        value="<?= e(old('fecha_compra', $registro['fecha_compra'] ?? '')) ?>"
                    >
                </label>

                <label>
                    Número de factura
                    <input
                        name="numero_factura"
                        value="<?= e(old('numero_factura', $registro['numero_factura'] ?? '')) ?>"
                    >
                </label>

                <label>
                    Proveedor
                    <select name="proveedor_id">
                        <option value="">Sin definir</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <option
                                value="<?= $proveedor['id'] ?>"
                                <?= selected(old('proveedor_id', $registro['proveedor_id'] ?? ''), $proveedor['id']) ?>
                            >
                                <?= e($proveedor['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Costo (S/)
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="costo"
                        value="<?= e(old('costo', $registro['costo'] ?? '')) ?>"
                    >
                </label>

                <label>
                    Fin de garantía
                    <input
                        type="date"
                        name="fin_garantia"
                        value="<?= e(old('fin_garantia', $registro['fin_garantia'] ?? '')) ?>"
                    >
                </label>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <span>4</span>
                <div>
                    <h3>Especificaciones</h3>
                    <p>Agrega características según el tipo de equipo.</p>
                </div>
            </div>

            <div id="filasEspecificaciones" class="spec-list">
                <?php foreach ($filasEspecificaciones as $especificacion): ?>
                    <div class="spec-row">
                        <input
                            name="clave_especificacion[]"
                            value="<?= e($especificacion['clave_especificacion']) ?>"
                            placeholder="Ej. RAM"
                        >
                        <input
                            name="valor_especificacion[]"
                            value="<?= e($especificacion['valor_especificacion']) ?>"
                            placeholder="Ej. 16 GB"
                        >
                        <button type="button" class="icon-btn danger" data-remove-row>×</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="btn btn-light btn-sm" data-add-spec>
                + Agregar característica
            </button>

            <label class="full-label">
                Observaciones
                <textarea
                    name="observaciones"
                    rows="4"
                    placeholder="Estado físico, accesorios, información relevante..."
                ><?= e(old('observaciones', $registro['observaciones'] ?? '')) ?></textarea>
            </label>
        </div>

        <div class="form-footer">
            <a class="btn btn-light" href="<?= url('activos') ?>">Cancelar</a>
            <button class="btn btn-primary" type="submit">
                <?= $isEdit ? 'Guardar cambios' : 'Registrar activo' ?>
            </button>
        </div>
    </form>
