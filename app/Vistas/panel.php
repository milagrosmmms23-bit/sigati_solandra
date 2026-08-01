<?php
$resumen = $resumen ?? [];
$porEstado = $porEstado ?? [];
$porTipo = $porTipo ?? [];
$porArea = $porArea ?? [];
$recientes = $recientes ?? [];

$total = max(1, (int) ($resumen['total_activos'] ?? 0));

$cards = [
    ['Total de activos', $resumen['total_activos'] ?? 0, 'Inventario registrado', 'navy'],
    ['Asignados', $resumen['activos_asignados'] ?? 0, 'Bajo responsabilidad', 'blue'],
    ['Disponibles', $resumen['activos_disponibles'] ?? 0, 'Listos para entregar', 'green'],
    ['En mantenimiento', $resumen['activos_mantenimiento'] ?? 0, 'Atención técnica', 'orange'],
    ['Trabajadores', $resumen['total_trabajadores'] ?? 0, 'Personal activo', 'purple'],
    ['Préstamos/asignaciones', $resumen['asignaciones_activas'] ?? 0, 'Actas vigentes', 'cyan'],
];
?>

<div class="page-actions">
    <div>
        <h2>Resumen operativo</h2>
        <p>Estado actual de los activos tecnológicos en planta.</p>
    </div>

    <div class="actions">
        <a class="btn btn-light" href="<?= url('reportes/inventario') ?>">Ver reporte</a>
        <a class="btn btn-primary" href="<?= url('activos/crear') ?>">+ Registrar activo</a>
    </div>
</div>

<div class="stat-grid">
    <?php foreach ($cards as [$label, $valor, $description, $tone]): ?>
        <article class="stat-card tone-<?= $tone ?>">
            <div class="stat-icon">◆</div>
            <div>
                <span><?= e($label) ?></span>
                <strong><?= number_format((int) $valor) ?></strong>
                <small><?= e($description) ?></small>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<div class="dashboard-grid">
    <section class="panel">
        <div class="panel-head">
            <div>
                <h3>Activos por estado</h3>
                <p>Distribución del inventario vigente</p>
            </div>
        </div>

        <div class="bar-list">
            <?php foreach ($porEstado as $estado): ?>
                <?php $percent = round(((int) $estado['total'] / $total) * 100, 1); ?>
                <div class="bar-row">
                    <div class="bar-label">
                        <span><?= e($estado['name']) ?></span>
                        <b><?= (int) $estado['total'] ?></b>
                    </div>
                    <div class="bar-track">
                        <i style="width:<?= $percent ?>%"></i>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h3>Activos por tipo</h3>
                <p>Principales categorías</p>
            </div>
        </div>

        <div class="donut-wrap">
            <div class="donut" style="--p:<?= min(100, round(((int) ($porTipo[0]['total'] ?? 0) / $total) * 100)) ?>">
                <div>
                    <strong><?= number_format((int) ($porTipo[0]['total'] ?? 0)) ?></strong>
                    <span><?= e($porTipo[0]['name'] ?? 'Activos') ?></span>
                </div>
            </div>

            <div class="legend-list">
                <?php foreach (array_slice($porTipo, 0, 6) as $tipo): ?>
                    <div>
                        <i></i>
                        <span><?= e($tipo['name']) ?></span>
                        <b><?= (int) $tipo['total'] ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h3>Distribución por área</h3>
                <p>Diez áreas con más activos</p>
            </div>
            <a href="<?= url('reportes/inventario') ?>">Detalle</a>
        </div>

        <div class="area-list">
            <?php foreach ($porArea as $area): ?>
                <div>
                    <span><?= e($area['name']) ?></span>
                    <b><?= (int) $area['total'] ?></b>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h3>Actividad reciente</h3>
                <p>Últimos movimientos registrados</p>
            </div>
        </div>

        <div class="timeline compact">
            <?php foreach ($recientes as $movimiento): ?>
                <div class="timeline-item">
                    <i></i>
                    <div>
                        <strong><?= e($movimiento['asset_code']) ?> · <?= e($movimiento['movement_type']) ?></strong>
                        <p><?= e($movimiento['notes'] ?: 'Movimiento registrado') ?></p>
                        <small>
                            <?= datetime_pe($movimiento['created_at']) ?> · <?= e($movimiento['user_name'] ?? 'Sistema') ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!$recientes): ?>
                <div class="empty">Aún no hay movimientos.</div>
            <?php endif; ?>
        </div>
    </section>
</div>
