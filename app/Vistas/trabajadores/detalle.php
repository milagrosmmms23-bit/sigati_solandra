<?php
$activos = $activos ?? [];
$asignaciones = $asignaciones ?? [];
$nombreCompleto = trim(($registro['nombres'] ?? '').' '.($registro['apellidos'] ?? ''));
?>

<div class="page-actions">
    <div>
        <div class="eyebrow">Ficha de trabajador</div>
        <h2><?= e($nombreCompleto) ?></h2>
        <p><?= e($registro['cargo'] ?: 'Sin cargo') ?> - <?= e($registro['nombre_area'] ?: 'Sin area') ?></p>
    </div>

    <div class="actions">
        <a class="btn btn-light" href="<?= url('trabajadores/'.$registro['id'].'/editar') ?>">Editar</a>
        <a class="btn btn-primary" href="<?= url('asignaciones/crear') ?>">Nueva asignacion</a>
    </div>
</div>

<section class="panel report-summary">
    <div>
        <span>Activos actuales</span>
        <strong><?= count($activos) ?></strong>
    </div>
    <div>
        <span>Actas registradas</span>
        <strong><?= count($asignaciones) ?></strong>
    </div>
    <div>
        <span>Correo</span>
        <strong><?= e($registro['correo'] ?: '-') ?></strong>
    </div>
    <div>
        <span>Telefono</span>
        <strong><?= e($registro['telefono'] ?: '-') ?></strong>
    </div>
</section>

<div class="dashboard-grid">
    <section class="panel table-panel">
        <div class="panel-head">
            <div>
                <h3>Activos asignados actualmente</h3>
                <p>Equipos bajo responsabilidad del trabajador.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Activo</th>
                        <th>Serie / IMEI</th>
                        <th>Area</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activos as $activo): ?>
                        <tr>
                            <td>
                                <a class="asset-code" href="<?= url('activos/'.$activo['id']) ?>">
                                    <?= e($activo['codigo_activo']) ?>
                                </a>
                                <small><?= e($activo['codigo_anterior'] ?: '-') ?></small>
                            </td>
                            <td>
                                <strong><?= e($activo['nombre_tipo']) ?></strong>
                                <small><?= e(trim(($activo['nombre_marca'] ?? '').' '.($activo['nombre_modelo'] ?? '')) ?: '-') ?></small>
                            </td>
                            <td>
                                <?= e($activo['numero_serie'] ?: ($activo['imei1'] ?: '-')) ?>
                                <small><?= e($activo['numero_telefono'] ?: '') ?></small>
                            </td>
                            <td><?= e($activo['nombre_area'] ?: '-') ?></td>
                            <td><?= badge($activo['nombre_estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$activos): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty">Este trabajador no tiene activos asignados actualmente.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h3>Actas del trabajador</h3>
                <p>Ultimas asignaciones registradas.</p>
            </div>
        </div>

        <div class="timeline compact">
            <?php foreach ($asignaciones as $asignacion): ?>
                <div class="timeline-item">
                    <i></i>
                    <div>
                        <strong>
                            <a href="<?= url('asignaciones/'.$asignacion['id']) ?>">
                                <?= e($asignacion['numero_asignacion']) ?>
                            </a>
                            - <?= e($asignacion['estado']) ?>
                        </strong>
                        <p>
                            <?= (int) $asignacion['total_activos'] ?> activo(s)
                            <?= $asignacion['nombre_area'] ? ' - '.e($asignacion['nombre_area']) : '' ?>
                        </p>
                        <small><?= datetime_pe($asignacion['asignado_en'] ?: $asignacion['creado_en']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!$asignaciones): ?>
                <div class="empty">Sin actas registradas.</div>
            <?php endif; ?>
        </div>
    </section>
</div>