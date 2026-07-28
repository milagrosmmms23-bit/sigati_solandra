<?php $mode=$mode??'index'; $errors=$_SESSION['_errors']??[]; ?>
<?php if($mode==='index'): ?>
<div class="page-actions">
  <div><h2>Inventario tecnológico</h2><p><?= number_format((int)$result['total']) ?> activos encontrados.</p></div>
  <div class="actions"><a class="btn btn-light" href="<?= url('assets/import') ?>">Importar CSV</a><a class="btn btn-primary" href="<?= url('assets/create') ?>">+ Nuevo activo</a></div>
</div>
<form class="filter-panel" method="get">
  <div class="field grow"><label>Buscar</label><input name="q" value="<?= e($filters['q']) ?>" placeholder="Código, serie, hostname, IMEI o teléfono"></div>
  <div class="field"><label>Tipo</label><select name="type_id"><option value="">Todos</option><?php foreach($types as $x):?><option value="<?= $x['id'] ?>" <?= selected($filters['type_id'],$x['id']) ?>><?= e($x['name']) ?></option><?php endforeach;?></select></div>
  <div class="field"><label>Estado</label><select name="status_id"><option value="">Todos</option><?php foreach($statuses as $x):?><option value="<?= $x['id'] ?>" <?= selected($filters['status_id'],$x['id']) ?>><?= e($x['name']) ?></option><?php endforeach;?></select></div>
  <div class="field"><label>Área</label><select name="area_id"><option value="">Todas</option><?php foreach($areas as $x):?><option value="<?= $x['id'] ?>" <?= selected($filters['area_id'],$x['id']) ?>><?= e($x['name']) ?></option><?php endforeach;?></select></div>
  <button class="btn btn-dark" type="submit">Filtrar</button>
</form>
<section class="panel table-panel">
 <div class="table-responsive"><table class="data-table">
   <thead><tr><th>Código</th><th>Equipo</th><th>Serie</th><th>Área / responsable</th><th>Estado</th><th>Actualización</th><th></th></tr></thead>
   <tbody><?php foreach($result['rows'] as $r):?><tr>
     <td><a class="asset-code" href="<?= url('assets/'.$r['id']) ?>"><?= e($r['asset_code']) ?></a><small><?= e($r['legacy_code']?:'Sin código anterior') ?></small></td>
     <td><strong><?= e($r['type_name']) ?></strong><small><?= e(trim(($r['brand_name']??'').' '.($r['model_name']??''))?:'Sin marca/modelo') ?></small></td>
     <td><?= e($r['serial_number']?:'—') ?><small><?= e($r['hostname']?:'') ?></small></td>
     <td><?= e($r['area_name']?:'Sin área') ?><small><?= e($r['employee_name']?:'Sin responsable') ?></small></td>
     <td><?= badge($r['status_name']) ?></td><td><?= date_pe($r['updated_at']?:$r['created_at']) ?></td>
     <td class="text-right"><a class="icon-btn" href="<?= url('assets/'.$r['id'].'/edit') ?>">Editar</a></td>
   </tr><?php endforeach;?><?php if(!$result['rows']):?><tr><td colspan="7"><div class="empty">No se encontraron activos.</div></td></tr><?php endif;?></tbody>
 </table></div>
 <?php if($result['pages']>1):?><div class="pagination"><?php for($i=1;$i<=$result['pages'];$i++): $qs=http_build_query(array_merge($filters,['page'=>$i]));?><a class="<?= $i===$result['page']?'active':'' ?>" href="?<?= e($qs) ?>"><?= $i ?></a><?php endfor;?></div><?php endif;?>
</section>

<?php elseif($mode==='form'): $isEdit=!empty($item); $selectedStatus=old('status_id',$item['status_id']??''); ?>
<div class="page-actions"><div><h2><?= $isEdit?'Editar activo':'Registrar activo' ?></h2><p>Los campos con * son obligatorios.</p></div><a class="btn btn-light" href="<?= $isEdit?url('assets/'.$item['id']):url('assets') ?>">Cancelar</a></div>
<form class="form-card" method="post" action="<?= $isEdit?url('assets/'.$item['id']):url('assets') ?>">
 <?= csrf_field() ?>
 <div class="form-section"><div class="section-title"><span>1</span><div><h3>Identificación</h3><p>Datos principales y clasificación del equipo.</p></div></div>
 <div class="form-grid cols-3">
   <label>Tipo de activo *<select name="asset_type_id" required><option value="">Seleccionar</option><?php foreach($types as $x):?><option value="<?= $x['id'] ?>" <?= selected(old('asset_type_id',$item['asset_type_id']??''),$x['id']) ?>><?= e($x['name']) ?></option><?php endforeach;?></select><?= isset($errors['asset_type_id'])?'<small class="error">'.e($errors['asset_type_id']).'</small>':'' ?></label>
   <label>Estado *<select name="status_id" required><?php foreach($statuses as $x):?><option value="<?= $x['id'] ?>" <?= ((string)$selectedStatus===(string)$x['id'] || ($selectedStatus==='' && ($x['code']??'')==='DISPONIBLE'))?'selected':'' ?>><?= e($x['name']) ?></option><?php endforeach;?></select></label>
   <label>Código anterior<input name="legacy_code" value="<?= e(old('legacy_code',$item['legacy_code']??'')) ?>" placeholder="Ej. FT277701"></label>
   <label>Marca<select name="brand_id"><option value="">Sin definir</option><?php foreach($brands as $x):?><option value="<?= $x['id'] ?>" <?= selected(old('brand_id',$item['brand_id']??''),$x['id']) ?>><?= e($x['name']) ?></option><?php endforeach;?></select></label>
   <label>Modelo<select name="model_id"><option value="">Sin definir</option><?php foreach($models as $x):?><option value="<?= $x['id'] ?>" <?= selected(old('model_id',$item['model_id']??''),$x['id']) ?>><?= e($x['name']) ?></option><?php endforeach;?></select></label>
   <label>Número de serie<input name="serial_number" value="<?= e(old('serial_number',$item['serial_number']??'')) ?>" placeholder="Serie del fabricante"></label>
 </div></div>
 <div class="form-section"><div class="section-title"><span>2</span><div><h3>Ubicación y red</h3><p>Localización actual e identificación técnica.</p></div></div>
 <div class="form-grid cols-3">
   <label>Área<select name="current_area_id"><option value="">Sin área</option><?php foreach($areas as $x):?><option value="<?= $x['id'] ?>" <?= selected(old('current_area_id',$item['current_area_id']??''),$x['id']) ?>><?= e($x['name']) ?></option><?php endforeach;?></select></label>
   <label>Ubicación<select name="location_id"><option value="">Sin ubicación</option><?php foreach($locations as $x):?><option value="<?= $x['id'] ?>" <?= selected(old('location_id',$item['location_id']??''),$x['id']) ?>><?= e($x['name']) ?></option><?php endforeach;?></select></label>
   <label>Hostname<input name="hostname" value="<?= e(old('hostname',$item['hostname']??'')) ?>" placeholder="PC-AQP-001"></label>
   <label>Dirección IP<input name="ip_address" value="<?= e(old('ip_address',$item['ip_address']??'')) ?>" placeholder="192.168.x.x"></label>
   <label>Dirección MAC<input name="mac_address" value="<?= e(old('mac_address',$item['mac_address']??'')) ?>" placeholder="00:00:00:00:00:00"></label>
   <label>Teléfono<input name="phone_number" value="<?= e(old('phone_number',$item['phone_number']??'')) ?>" placeholder="Número corporativo"></label>
   <label>IMEI 1<input name="imei1" value="<?= e(old('imei1',$item['imei1']??'')) ?>"></label>
   <label>IMEI 2<input name="imei2" value="<?= e(old('imei2',$item['imei2']??'')) ?>"></label>
 </div></div>
 <div class="form-section"><div class="section-title"><span>3</span><div><h3>Compra y garantía</h3><p>Información administrativa del activo.</p></div></div>
 <div class="form-grid cols-3">
   <label>Fecha de compra<input type="date" name="purchase_date" value="<?= e(old('purchase_date',$item['purchase_date']??'')) ?>"></label>
   <label>Número de factura<input name="invoice_number" value="<?= e(old('invoice_number',$item['invoice_number']??'')) ?>"></label>
   <label>Proveedor<select name="supplier_id"><option value="">Sin definir</option><?php foreach($suppliers as $x):?><option value="<?= $x['id'] ?>" <?= selected(old('supplier_id',$item['supplier_id']??''),$x['id']) ?>><?= e($x['name']) ?></option><?php endforeach;?></select></label>
   <label>Costo (S/)<input type="number" step="0.01" min="0" name="cost" value="<?= e(old('cost',$item['cost']??'')) ?>"></label>
   <label>Fin de garantía<input type="date" name="warranty_end" value="<?= e(old('warranty_end',$item['warranty_end']??'')) ?>"></label>
 </div></div>
 <div class="form-section"><div class="section-title"><span>4</span><div><h3>Especificaciones</h3><p>Agrega características según el tipo de equipo.</p></div></div>
 <div id="specRows" class="spec-list">
 <?php $specRows=$item['specs']??[['spec_key'=>'','spec_value'=>'']]; if(!$specRows)$specRows=[['spec_key'=>'','spec_value'=>'']]; foreach($specRows as $sp):?>
   <div class="spec-row"><input name="spec_key[]" value="<?= e($sp['spec_key']) ?>" placeholder="Ej. RAM"><input name="spec_value[]" value="<?= e($sp['spec_value']) ?>" placeholder="Ej. 16 GB"><button type="button" class="icon-btn danger" data-remove-row>×</button></div>
 <?php endforeach;?>
 </div><button type="button" class="btn btn-light btn-sm" data-add-spec>+ Agregar característica</button>
 <label class="full-label">Observaciones<textarea name="notes" rows="4" placeholder="Estado físico, accesorios, información relevante..."><?= e(old('notes',$item['notes']??'')) ?></textarea></label>
 </div>
 <div class="form-footer"><a class="btn btn-light" href="<?= url('assets') ?>">Cancelar</a><button class="btn btn-primary" type="submit"><?= $isEdit?'Guardar cambios':'Registrar activo' ?></button></div>
</form>

<?php elseif($mode==='show'): ?>
<div class="page-actions"><div><div class="eyebrow"><?= e($item['type_name']) ?></div><h2><?= e($item['asset_code']) ?></h2><p><?= e(trim(($item['brand_name']??'').' '.($item['model_name']??''))?:'Sin marca o modelo') ?></p></div><div class="actions"><a class="btn btn-light" href="<?= url('assets/'.$item['id'].'/edit') ?>">Editar</a><a class="btn btn-primary" href="<?= url('assignments/create') ?>">Asignar equipo</a></div></div>
<div class="asset-detail-grid">
 <section class="panel asset-summary"><div class="asset-head"><div class="asset-visual"><?= e(substr($item['type_name'],0,2)) ?></div><div><h3><?= e($item['asset_code']) ?></h3><?= badge($item['status_name']) ?></div></div>
   <img class="qr-image" src="<?= url('assets/'.$item['id'].'/qr') ?>" alt="QR"><small>Escanea para abrir la ficha</small>
   <div class="summary-lines"><div><span>Código anterior</span><b><?= e($item['legacy_code']?:'—') ?></b></div><div><span>Serie</span><b><?= e($item['serial_number']?:'—') ?></b></div><div><span>Responsable</span><b><?= e($item['employee_name']?:'Sin asignar') ?></b></div><div><span>Área</span><b><?= e($item['area_name']?:'Sin área') ?></b></div></div>
 </section>
 <section class="panel detail-panel"><div class="tabs"><button class="active" data-tab="general">Información</button><button data-tab="technical">Técnica</button><button data-tab="history">Historial</button><button data-tab="maintenance">Mantenimiento</button></div>
   <div class="tab-pane active" data-pane="general"><div class="detail-grid">
     <?php $fields=['Tipo'=>$item['type_name'],'Marca'=>$item['brand_name'],'Modelo'=>$item['model_name'],'Ubicación'=>$item['location_name'],'Fecha de compra'=>date_pe($item['purchase_date']),'Factura'=>$item['invoice_number'],'Proveedor'=>$item['supplier_name'],'Costo'=>money($item['cost']),'Fin de garantía'=>date_pe($item['warranty_end']),'Registrado'=>datetime_pe($item['created_at'])]; foreach($fields as $k=>$v):?><div><span><?= e($k) ?></span><strong><?= e($v?:'—') ?></strong></div><?php endforeach;?></div><div class="notes-box"><span>Observaciones</span><p><?= nl2br(e($item['notes']?:'Sin observaciones.')) ?></p></div></div>
   <div class="tab-pane" data-pane="technical"><div class="detail-grid"><div><span>Hostname</span><strong><?= e($item['hostname']?:'—') ?></strong></div><div><span>IP</span><strong><?= e($item['ip_address']?:'—') ?></strong></div><div><span>MAC</span><strong><?= e($item['mac_address']?:'—') ?></strong></div><div><span>Teléfono</span><strong><?= e($item['phone_number']?:'—') ?></strong></div><div><span>IMEI 1</span><strong><?= e($item['imei1']?:'—') ?></strong></div><div><span>IMEI 2</span><strong><?= e($item['imei2']?:'—') ?></strong></div><?php foreach($item['specs'] as $sp):?><div><span><?= e($sp['spec_key']) ?></span><strong><?= e($sp['spec_value']) ?></strong></div><?php endforeach;?></div></div>
   <div class="tab-pane" data-pane="history"><div class="timeline"><?php foreach($item['movements'] as $m):?><div class="timeline-item"><i></i><div><strong><?= e($m['movement_type']) ?></strong><p><?= e($m['notes']?:'Movimiento registrado') ?></p><small><?= datetime_pe($m['created_at']) ?> · <?= e($m['user_name']??'Sistema') ?></small></div></div><?php endforeach;?><?php if(!$item['movements']):?><div class="empty">Sin movimientos.</div><?php endif;?></div></div>
   <div class="tab-pane" data-pane="maintenance"><div class="table-responsive"><table class="data-table"><thead><tr><th>Tipo</th><th>Estado</th><th>Falla</th><th>Inicio</th><th>Costo</th></tr></thead><tbody><?php foreach($item['maintenances'] as $m):?><tr><td><?= e($m['type']) ?></td><td><?= badge($m['status']) ?></td><td><?= e($m['issue']?:'—') ?></td><td><?= date_pe($m['started_at']) ?></td><td><?= money($m['cost']) ?></td></tr><?php endforeach;?><?php if(!$item['maintenances']):?><tr><td colspan="5"><div class="empty">Sin mantenimientos.</div></td></tr><?php endif;?></tbody></table></div></div>
 </section>
</div>

<?php elseif($mode==='import'): ?>
<div class="page-actions"><div><h2>Importar inventario</h2><p>Carga activos desde una plantilla CSV separada por comas.</p></div><a class="btn btn-light" href="<?= url('assets') ?>">Volver</a></div>
<div class="two-columns">
 <form class="form-card compact-card" method="post" enctype="multipart/form-data" action="<?= url('assets/import') ?>"><?= csrf_field() ?><h3>Seleccionar archivo</h3><p>Usa la plantilla incluida en la carpeta <code>database</code>.</p><label class="file-drop"><input type="file" name="csv" accept=".csv,text/csv" required><span>Arrastra o selecciona tu archivo CSV</span><small>Máximo recomendado: 2,000 filas por carga</small></label><button class="btn btn-primary btn-block" type="submit">Importar inventario</button></form>
 <section class="panel"><h3>Columnas reconocidas</h3><div class="code-block">tipo, codigo_anterior, marca, modelo, serie, area, ubicacion, hostname, ip, mac, imei1, imei2, telefono, fecha_compra, factura, proveedor, costo, fin_garantia, observaciones</div><div class="notice"><strong>Importante:</strong> los tipos deben coincidir con los catálogos del sistema. Las marcas, modelos, áreas, ubicaciones y proveedores que no existan serán creados.</div></section>
</div>
<?php endif; ?>
