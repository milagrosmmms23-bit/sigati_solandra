<?php
use App\Core\Auth;
use App\Core\Flash;

$user = Auth::user();
$flashes = Flash::take();
$current = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');

function nav_active(string $needle, string $current): string
{
    return str_contains($current, $needle) ? 'active' : '';
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title ?? 'SIGATI') ?> | SIGATI SOLANDRA</title>
    <link rel="icon" href="<?= url('favicon.svg') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-mark">S</div>
            <div>
                <strong>SIGATI</strong>
                <small>SOLANDRA · Arequipa</small>
            </div>
        </div>

        <nav>
            <a class="<?= $current === trim(config('app.base_url'), '/') ? 'active' : '' ?>" href="<?= url() ?>">
                <span>▦</span> Dashboard
            </a>

            <div class="nav-label">GESTIÓN</div>

            <a class="<?= nav_active('assets', $current) ?>" href="<?= url('assets') ?>">
                <span>▣</span> Inventario
            </a>
            <a class="<?= nav_active('employees', $current) ?>" href="<?= url('employees') ?>">
                <span>◎</span> Trabajadores
            </a>
            <a class="<?= nav_active('assignments', $current) ?>" href="<?= url('assignments') ?>">
                <span>⇢</span> Asignaciones
            </a>
            <a class="<?= nav_active('returns', $current) ?>" href="<?= url('returns') ?>">
                <span>↩</span> Devoluciones
            </a>
            <a class="<?= nav_active('maintenances', $current) ?>" href="<?= url('maintenances') ?>">
                <span>⚙</span> Mantenimientos
            </a>

            <div class="nav-label">CONTROL</div>

            <a class="<?= nav_active('reports', $current) ?>" href="<?= url('reports/inventory') ?>">
                <span>▤</span> Reportes
            </a>

            <?php if (Auth::role() === 'ADMIN'): ?>
                <a class="<?= nav_active('catalogs', $current) ?>" href="<?= url('catalogs') ?>">
                    <span>☷</span> Catálogos
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
            <button class="menu-button" type="button" data-toggle-sidebar>☰</button>

            <div>
                <h1><?= e($title ?? 'SIGATI') ?></h1>
                <p><?= e(config('app.company')) ?> · <?= e(config('app.site')) ?></p>
            </div>

            <div class="user-menu">
                <div class="avatar"><?= e(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?></div>
                <div>
                    <strong><?= e($user['name'] ?? '') ?></strong>
                    <small><?= e($user['role_name'] ?? '') ?></small>
                </div>

                <form action="<?= url('logout') ?>" method="post">
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

            <?= $content ?>
        </section>
    </main>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
