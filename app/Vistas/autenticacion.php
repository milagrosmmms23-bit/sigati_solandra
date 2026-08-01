<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($titulo ?? 'Acceso') ?> | SIGATI SOLANDRA</title>
    <link rel="icon" href="<?= url('favicon.svg') ?>">
    <link rel="stylesheet" href="<?= recurso('css/app.css') ?>">
</head>
<body class="auth-body">
<?= $contenido ?>
</body>
</html>
