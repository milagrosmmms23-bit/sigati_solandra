<div class="page-actions"><div><h2>Catálogos del sistema</h2><p>Administra los valores usados en formularios y reportes.</p></div></div>
<div class="catalog-grid">
<?php foreach($labels as $table=>$label): ?>
<section class="panel catalog-card" id="<?= e($table) ?>"><div class="panel-head"><div><h3><?= e($label) ?></h3><p><?= count($rows[$table]) ?> registros activos</p></div></div>
<form class="inline-form" method="post" action="<?= url('catalogs/'.$table) ?>"><?= csrf_field() ?>
<?php if($table==='models'):?><select name="brand_id"><option value="">Sin marca</option><?php foreach($rows['brands'] as $x):?><option value="<?= $x['id'] ?>"><?= e($x['name']) ?></option><?php endforeach;?></select><?php endif;?>
<?php if($table==='locations'):?><select name="area_id"><option value="">Sin área</option><?php foreach($rows['areas'] as $x):?><option value="<?= $x['id'] ?>"><?= e($x['name']) ?></option><?php endforeach;?></select><?php endif;?>
<input name="name" required placeholder="Nuevo registro">
<?php if($table==='asset_types'):?><input name="prefix" maxlength="5" required placeholder="Prefijo"><?php endif;?><?php if($table==='asset_statuses'):?><input name="code" maxlength="40" placeholder="Código (opcional)"><select name="color"><option value="secondary">Gris</option><option value="success">Verde</option><option value="primary">Azul</option><option value="warning">Naranja</option><option value="danger">Rojo</option></select><?php endif;?>
<button class="btn btn-primary btn-sm">Agregar</button></form>
<div class="tag-list"><?php foreach($rows[$table] as $r):?><span><?= e($r['name']) ?><?php if(isset($r['prefix'])):?><small><?= e($r['prefix']) ?></small><?php endif;?></span><?php endforeach;?></div>
</section>
<?php endforeach; ?>
</div>
