<?php
    $isEdit = !empty($registro);
    $selectedStatus = old('status_id', $registro['status_id'] ?? '');
    $specRows = $registro['specs'] ?? [['spec_key' => '', 'spec_value' => '']];

    if (!$specRows) {
        $specRows = [['spec_key' => '', 'spec_value' => '']];
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
                    <select name="asset_type_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option
                                value="<?= $tipo['id'] ?>"
                                <?= selected(old('asset_type_id', $registro['asset_type_id'] ?? ''), $tipo['id']) ?>
                            >
                                <?= e($tipo['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if (isset($errores['asset_type_id'])): ?>
                        <small class="error"><?= e($errores['asset_type_id']) ?></small>
                    <?php endif; ?>
                </label>

                <label>
                    Estado *
                    <select name="status_id" required>
                        <?php foreach ($estados as $estado): ?>
                            <?php
                            $isDefaultStatus = $selectedStatus === '' && ($estado['code'] ?? '') === 'DISPONIBLE';
                            $isSelectedStatus = (string) $selectedStatus === (string) $estado['id'];
                            ?>
                            <option
                                value="<?= $estado['id'] ?>"
                                <?= ($isSelectedStatus || $isDefaultStatus) ? 'selected' : '' ?>
                            >
                                <?= e($estado['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Código anterior
                    <input
                        name="legacy_code"
                        value="<?= e(old('legacy_code', $registro['legacy_code'] ?? '')) ?>"
                        placeholder="Ej. FT277701"
                    >
                </label>

                <label>
                    Marca
                    <select name="brand_id">
                        <option value="">Sin definir</option>
                        <?php foreach ($marcas as $marca): ?>
                            <option
                                value="<?= $marca['id'] ?>"
                                <?= selected(old('brand_id', $registro['brand_id'] ?? ''), $marca['id']) ?>
                            >
                                <?= e($marca['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Modelo
                    <select name="model_id">
                        <option value="">Sin definir</option>
                        <?php foreach ($modelos as $modelo): ?>
                            <option
                                value="<?= $modelo['id'] ?>"
                                <?= selected(old('model_id', $registro['model_id'] ?? ''), $modelo['id']) ?>
                            >
                                <?= e($modelo['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Número de serie
                    <input
                        name="serial_number"
                        value="<?= e(old('serial_number', $registro['serial_number'] ?? '')) ?>"
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
                    <select name="current_area_id">
                        <option value="">Sin área</option>
                        <?php foreach ($areas as $area): ?>
                            <option
                                value="<?= $area['id'] ?>"
                                <?= selected(old('current_area_id', $registro['current_area_id'] ?? ''), $area['id']) ?>
                            >
                                <?= e($area['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Ubicación
                    <select name="location_id">
                        <option value="">Sin ubicación</option>
                        <?php foreach ($ubicaciones as $ubicacion): ?>
                            <option
                                value="<?= $ubicacion['id'] ?>"
                                <?= selected(old('location_id', $registro['location_id'] ?? ''), $ubicacion['id']) ?>
                            >
                                <?= e($ubicacion['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Hostname
                    <input
                        name="hostname"
                        value="<?= e(old('hostname', $registro['hostname'] ?? '')) ?>"
                        placeholder="PC-AQP-001"
                    >
                </label>

                <label>
                    Dirección IP
                    <input
                        name="ip_address"
                        value="<?= e(old('ip_address', $registro['ip_address'] ?? '')) ?>"
                        placeholder="192.168.x.x"
                    >
                </label>

                <label>
                    Dirección MAC
                    <input
                        name="mac_address"
                        value="<?= e(old('mac_address', $registro['mac_address'] ?? '')) ?>"
                        placeholder="00:00:00:00:00:00"
                    >
                </label>

                <label>
                    Teléfono
                    <input
                        name="phone_number"
                        value="<?= e(old('phone_number', $registro['phone_number'] ?? '')) ?>"
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
                        name="purchase_date"
                        value="<?= e(old('purchase_date', $registro['purchase_date'] ?? '')) ?>"
                    >
                </label>

                <label>
                    Número de factura
                    <input
                        name="invoice_number"
                        value="<?= e(old('invoice_number', $registro['invoice_number'] ?? '')) ?>"
                    >
                </label>

                <label>
                    Proveedor
                    <select name="supplier_id">
                        <option value="">Sin definir</option>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <option
                                value="<?= $proveedor['id'] ?>"
                                <?= selected(old('supplier_id', $registro['supplier_id'] ?? ''), $proveedor['id']) ?>
                            >
                                <?= e($proveedor['name']) ?>
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
                        name="cost"
                        value="<?= e(old('cost', $registro['cost'] ?? '')) ?>"
                    >
                </label>

                <label>
                    Fin de garantía
                    <input
                        type="date"
                        name="warranty_end"
                        value="<?= e(old('warranty_end', $registro['warranty_end'] ?? '')) ?>"
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

            <div id="specRows" class="spec-list">
                <?php foreach ($specRows as $especificacion): ?>
                    <div class="spec-row">
                        <input
                            name="spec_key[]"
                            value="<?= e($especificacion['spec_key']) ?>"
                            placeholder="Ej. RAM"
                        >
                        <input
                            name="spec_value[]"
                            value="<?= e($especificacion['spec_value']) ?>"
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
                    name="notes"
                    rows="4"
                    placeholder="Estado físico, accesorios, información relevante..."
                ><?= e(old('notes', $registro['notes'] ?? '')) ?></textarea>
            </label>
        </div>

        <div class="form-footer">
            <a class="btn btn-light" href="<?= url('activos') ?>">Cancelar</a>
            <button class="btn btn-primary" type="submit">
                <?= $isEdit ? 'Guardar cambios' : 'Registrar activo' ?>
            </button>
        </div>
    </form>
