<?php
    $isEdit = !empty($item);
    $selectedStatus = old('status_id', $item['status_id'] ?? '');
    $specRows = $item['specs'] ?? [['spec_key' => '', 'spec_value' => '']];

    if (!$specRows) {
        $specRows = [['spec_key' => '', 'spec_value' => '']];
    }
    ?>

    <div class="page-actions">
        <div>
            <h2><?= $isEdit ? 'Editar activo' : 'Registrar activo' ?></h2>
            <p>Los campos con * son obligatorios.</p>
        </div>

        <a class="btn btn-light" href="<?= $isEdit ? url('activos/'.$item['id']) : url('activos') ?>">
            Cancelar
        </a>
    </div>

    <form
        class="form-card"
        method="post"
        action="<?= $isEdit ? url('activos/'.$item['id']) : url('activos') ?>"
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
                        <?php foreach ($types as $type): ?>
                            <option
                                value="<?= $type['id'] ?>"
                                <?= selected(old('asset_type_id', $item['asset_type_id'] ?? ''), $type['id']) ?>
                            >
                                <?= e($type['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if (isset($errors['asset_type_id'])): ?>
                        <small class="error"><?= e($errors['asset_type_id']) ?></small>
                    <?php endif; ?>
                </label>

                <label>
                    Estado *
                    <select name="status_id" required>
                        <?php foreach ($statuses as $status): ?>
                            <?php
                            $isDefaultStatus = $selectedStatus === '' && ($status['code'] ?? '') === 'DISPONIBLE';
                            $isSelectedStatus = (string) $selectedStatus === (string) $status['id'];
                            ?>
                            <option
                                value="<?= $status['id'] ?>"
                                <?= ($isSelectedStatus || $isDefaultStatus) ? 'selected' : '' ?>
                            >
                                <?= e($status['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Código anterior
                    <input
                        name="legacy_code"
                        value="<?= e(old('legacy_code', $item['legacy_code'] ?? '')) ?>"
                        placeholder="Ej. FT277701"
                    >
                </label>

                <label>
                    Marca
                    <select name="brand_id">
                        <option value="">Sin definir</option>
                        <?php foreach ($marcas as $brand): ?>
                            <option
                                value="<?= $brand['id'] ?>"
                                <?= selected(old('brand_id', $item['brand_id'] ?? ''), $brand['id']) ?>
                            >
                                <?= e($brand['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Modelo
                    <select name="model_id">
                        <option value="">Sin definir</option>
                        <?php foreach ($modelos as $model): ?>
                            <option
                                value="<?= $model['id'] ?>"
                                <?= selected(old('model_id', $item['model_id'] ?? ''), $model['id']) ?>
                            >
                                <?= e($model['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Número de serie
                    <input
                        name="serial_number"
                        value="<?= e(old('serial_number', $item['serial_number'] ?? '')) ?>"
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
                                <?= selected(old('current_area_id', $item['current_area_id'] ?? ''), $area['id']) ?>
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
                        <?php foreach ($ubicaciones as $location): ?>
                            <option
                                value="<?= $location['id'] ?>"
                                <?= selected(old('location_id', $item['location_id'] ?? ''), $location['id']) ?>
                            >
                                <?= e($location['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Hostname
                    <input
                        name="hostname"
                        value="<?= e(old('hostname', $item['hostname'] ?? '')) ?>"
                        placeholder="PC-AQP-001"
                    >
                </label>

                <label>
                    Dirección IP
                    <input
                        name="ip_address"
                        value="<?= e(old('ip_address', $item['ip_address'] ?? '')) ?>"
                        placeholder="192.168.x.x"
                    >
                </label>

                <label>
                    Dirección MAC
                    <input
                        name="mac_address"
                        value="<?= e(old('mac_address', $item['mac_address'] ?? '')) ?>"
                        placeholder="00:00:00:00:00:00"
                    >
                </label>

                <label>
                    Teléfono
                    <input
                        name="phone_number"
                        value="<?= e(old('phone_number', $item['phone_number'] ?? '')) ?>"
                        placeholder="Número corporativo"
                    >
                </label>

                <label>
                    IMEI 1
                    <input name="imei1" value="<?= e(old('imei1', $item['imei1'] ?? '')) ?>">
                </label>

                <label>
                    IMEI 2
                    <input name="imei2" value="<?= e(old('imei2', $item['imei2'] ?? '')) ?>">
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
                        value="<?= e(old('purchase_date', $item['purchase_date'] ?? '')) ?>"
                    >
                </label>

                <label>
                    Número de factura
                    <input
                        name="invoice_number"
                        value="<?= e(old('invoice_number', $item['invoice_number'] ?? '')) ?>"
                    >
                </label>

                <label>
                    Proveedor
                    <select name="supplier_id">
                        <option value="">Sin definir</option>
                        <?php foreach ($proveedores as $supplier): ?>
                            <option
                                value="<?= $supplier['id'] ?>"
                                <?= selected(old('supplier_id', $item['supplier_id'] ?? ''), $supplier['id']) ?>
                            >
                                <?= e($supplier['name']) ?>
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
                        value="<?= e(old('cost', $item['cost'] ?? '')) ?>"
                    >
                </label>

                <label>
                    Fin de garantía
                    <input
                        type="date"
                        name="warranty_end"
                        value="<?= e(old('warranty_end', $item['warranty_end'] ?? '')) ?>"
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
                <?php foreach ($specRows as $spec): ?>
                    <div class="spec-row">
                        <input
                            name="spec_key[]"
                            value="<?= e($spec['spec_key']) ?>"
                            placeholder="Ej. RAM"
                        >
                        <input
                            name="spec_value[]"
                            value="<?= e($spec['spec_value']) ?>"
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
                ><?= e(old('notes', $item['notes'] ?? '')) ?></textarea>
            </label>
        </div>

        <div class="form-footer">
            <a class="btn btn-light" href="<?= url('activos') ?>">Cancelar</a>
            <button class="btn btn-primary" type="submit">
                <?= $isEdit ? 'Guardar cambios' : 'Registrar activo' ?>
            </button>
        </div>
    </form>
