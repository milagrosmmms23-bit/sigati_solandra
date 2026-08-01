<?php
use App\Nucleo\Auth;
use App\Nucleo\Flash;

$usuario = Auth::usuario();
$flashes = Flash::tomar();
$rutaActual = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');

function nav_activo(string $segmento, string $rutaActual): string
{
    return str_contains($rutaActual, $segmento) ? 'active' : '';
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($titulo ?? 'SIGATI') ?> | SIGATI SOLANDRA</title>
    <link rel="icon" href="<?= url('favicon.svg') ?>">
    <link rel="stylesheet" href="<?= recurso('css/app.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-mark">S</div>
            <div>
                <strong>SIGATI</strong>
                <small>SOLANDRA - Arequipa</small>
            </div>
        </div>

        <nav>
            <a class="<?= $rutaActual === trim(config('aplicacion.url_base'), '/') ? 'active' : '' ?>" href="<?= url() ?>">
                <span>P</span> Panel
            </a>

            <div class="nav-label">GESTIÓN</div>

            <a class="<?= nav_activo('activos', $rutaActual) ?>" href="<?= url('activos') ?>">
                <span>I</span> Inventario
            </a>
            <a class="<?= nav_activo('trabajadores', $rutaActual) ?>" href="<?= url('trabajadores') ?>">
                <span>T</span> Trabajadores
            </a>
            <a class="<?= nav_activo('asignaciones', $rutaActual) ?>" href="<?= url('asignaciones') ?>">
                <span>A</span> Asignaciones
            </a>
            <a class="<?= nav_activo('devoluciones', $rutaActual) ?>" href="<?= url('devoluciones') ?>">
                <span>D</span> Devoluciones
            </a>
            <a class="<?= nav_activo('mantenimientos', $rutaActual) ?>" href="<?= url('mantenimientos') ?>">
                <span>M</span> Mantenimientos
            </a>

            <div class="nav-label">CONTROL</div>

            <a class="<?= nav_activo('reportes', $rutaActual) ?>" href="<?= url('reportes/inventario') ?>">
                <span>R</span> Reportes
            </a>

            <?php if (Auth::rol() === 'ADMIN'): ?>
                <a class="<?= nav_activo('catalogos', $rutaActual) ?>" href="<?= url('catalogos') ?>">
                    <span>C</span> Catálogos
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-foot">
            <small>Sede local</small>
            <strong>Planta Arequipa</strong>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <button class="menu-button" type="button" data-toggle-sidebar>Menu</button>

            <div>
                <h1><?= e($titulo ?? 'SIGATI') ?></h1>
                <p><?= e(config('aplicacion.empresa')) ?> - <?= e(config('aplicacion.sede')) ?></p>
            </div>

            <div class="user-menu">
                <div class="avatar"><?= e(strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1))) ?></div>
                <div>
                    <strong><?= e($usuario['nombre'] ?? '') ?></strong>
                    <small><?= e($usuario['nombre_rol'] ?? '') ?></small>
                </div>

                <form action="<?= url('salir') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="link-button" title="Cerrar sesión">Salir</button>
                </form>
            </div>
        </header>

        <section class="content">
            <?php foreach ($flashes as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
                    <?= e($flash['message']) ?>
                </div>
            <?php endforeach; ?>

            <?= $contenido ?>
        </section>
    </main>
</div>

<script src="<?= recurso('js/app.js') ?>"></script>
</body>
</html>