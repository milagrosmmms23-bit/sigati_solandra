<?php
$summary = $summary ?? [];
$byStatus = $byStatus ?? [];
$byType = $byType ?? [];
$byArea = $byArea ?? [];
$recent = $recent ?? [];

$total = max(1, (int) ($summary['total_assets'] ?? 0));

$cards = [
    ['Total de activos', $summary['total_assets'] ?? 0, 'Inventario registrado', 'navy'],
    ['Asignados', $summary['assigned_assets'] ?? 0, 'Bajo responsabilidad', 'blue'],
    ['Disponibles', $summary['available_assets'] ?? 0, 'Listos para entregar', 'green'],
    ['En mantenimiento', $summary['maintenance_assets'] ?? 0, 'Atención técnica', 'orange'],
    ['Trabajadores', $summary['total_employees'] ?? 0, 'Personal activo', 'purple'],
    ['Préstamos/asignaciones', $summary['active_assignments'] ?? 0, 'Actas vigentes', 'cyan'],
];
?>

<div class="page-actions">
    <div>
        <h2>Resumen operativo</h2>
        <p>Estado actual de los activos tecnológicos en planta.</p>
    </div>

    <div class="actions">
        <a class="btn btn-light" href="<?= url('reports/inventory') ?>">Ver reporte</a>
        <a class="btn btn-primary" href="<?= url('activos/create') ?>">+ Registrar activo</a>
    </div>
</div>

<div class="stat-grid">
    <?php foreach ($cards as [$label, $value, $description, $tone]): ?>
        <article class="stat-card tone-<?= $tone ?>">
            <div class="stat-icon">◆</div>
            <div>
                <span><?= e($label) ?></span>
                <strong><?= number_format((int) $value) ?></strong>
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
            <?php foreach ($byStatus as $status): ?>
                <?php $percent = round(((int) $status['total'] / $total) * 100, 1); ?>
                <div class="bar-row">
                    <div class="bar-label">
                        <span><?= e($status['name']) ?></span>
                        <b><?= (int) $status['total'] ?></b>
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
            <div class="donut" style="--p:<?= min(100, round(((int) ($byType[0]['total'] ?? 0) / $total) * 100)) ?>">
                <div>
                    <strong><?= number_format((int) ($byType[0]['total'] ?? 0)) ?></strong>
                    <span><?= e($byType[0]['name'] ?? 'Activos') ?></span>
                </div>
            </div>

            <div class="legend-list">
                <?php foreach (array_slice($byType, 0, 6) as $type): ?>
                    <div>
                        <i></i>
                        <span><?= e($type['name']) ?></span>
                        <b><?= (int) $type['total'] ?></b>
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
            <a href="<?= url('reports/inventory') ?>">Detalle</a>
        </div>

        <div class="area-list">
            <?php foreach ($byArea as $area): ?>
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
            <?php foreach ($recent as $movement): ?>
                <div class="timeline-item">
                    <i></i>
                    <div>
                        <strong><?= e($movement['asset_code']) ?> · <?= e($movement['movement_type']) ?></strong>
                        <p><?= e($movement['notes'] ?: 'Movimiento registrado') ?></p>
                        <small>
                            <?= datetime_pe($movement['created_at']) ?> · <?= e($movement['user_name'] ?? 'Sistema') ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!$recent): ?>
                <div class="empty">Aún no hay movimientos.</div>
            <?php endif; ?>
        </div>
    </section>
</div>
