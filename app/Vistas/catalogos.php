<?php
$labels = $labels ?? [];
$filas = $filas ?? [];
?>

<div class="page-actions">
    <div>
        <h2>Catálogos del sistema</h2>
        <p>Administra los valores usados en formularios y reportes.</p>
    </div>
</div>

<div class="catalog-grid">
    <?php foreach ($labels as $tabla => $label): ?>
        <section class="panel catalog-card" id="<?= e($tabla) ?>">
            <div class="panel-head">
                <div>
                    <h3><?= e($label) ?></h3>
                    <p><?= count($filas[$tabla]) ?> registros activos</p>
                </div>
            </div>

            <form class="inline-form" method="post" action="<?= url('catalogos/'.$tabla) ?>">
                <?= csrf_field() ?>

                <?php if ($tabla === 'modelos'): ?>
                    <select name="brand_id">
                        <option value="">Sin marca</option>
                        <?php foreach ($filas['marcas'] as $marca): ?>
                            <option value="<?= $marca['id'] ?>"><?= e($marca['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <?php if ($tabla === 'ubicaciones'): ?>
                    <select name="area_id">
                        <option value="">Sin área</option>
                        <?php foreach ($filas['areas'] as $area): ?>
                            <option value="<?= $area['id'] ?>"><?= e($area['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <input name="name" required placeholder="Nuevo registro">

                <?php if ($tabla === 'tipos_activo'): ?>
                    <input name="prefix" maxlength="5" required placeholder="Prefijo">
                <?php endif; ?>

                <?php if ($tabla === 'estados_activo'): ?>
                    <input name="code" maxlength="40" placeholder="Código (opcional)">
                    <select name="color">
                        <option value="secondary">Gris</option>
                        <option value="success">Verde</option>
                        <option value="primary">Azul</option>
                        <option value="warning">Naranja</option>
                        <option value="danger">Rojo</option>
                    </select>
                <?php endif; ?>

                <button class="btn btn-primary btn-sm">Agregar</button>
            </form>

            <div class="tag-list">
                <?php foreach ($filas[$tabla] as $registro): ?>
                    <span>
                        <?= e($registro['name']) ?>
                        <?php if (isset($registro['prefix'])): ?>
                            <small><?= e($registro['prefix']) ?></small>
                        <?php endif; ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
