<?php $flashes = App\Core\Flash::take(); ?>
<div class="login-shell">
    <section class="login-visual">
        <div class="login-overlay">
            <div class="brand large"><div class="brand-mark">S</div><div><strong>SIGATI</strong><small>SOLANDRA · SEDE AREQUIPA</small></div></div>
            <h1>Control integral de activos tecnológicos</h1>
            <p>Inventario, asignaciones, devoluciones y mantenimiento en una sola plataforma local.</p>
            <div class="login-features"><span>✓ Historial trazable</span><span>✓ Actas descargables</span><span>✓ Dashboard operativo</span></div>
        </div>
    </section>
    <section class="login-panel">
        <form class="login-card" action="<?= url('login') ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>
            <div class="mobile-brand">SIGATI <small>SOLANDRA</small></div>
            <h2>Bienvenido</h2>
            <p>Ingresa con tu cuenta del sistema.</p>
            <?php foreach ($flashes as $flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endforeach; ?>
            <label>Usuario<input name="username" required autofocus placeholder="admin"></label>
            <label>Contraseña<div class="password-wrap"><input id="password" type="password" name="password" required placeholder="••••••••"><button type="button" data-password-toggle>Ver</button></div></label>
            <button class="btn btn-primary btn-block" type="submit">Ingresar al sistema</button>
            <div class="demo-note"><strong>Acceso inicial:</strong> admin / Admin123*</div>
            <small class="copyright">Uso interno de SOLANDRA · Planta Arequipa</small>
        </form>
    </section>
</div>
<script src="<?= asset('js/app.js') ?>"></script>
