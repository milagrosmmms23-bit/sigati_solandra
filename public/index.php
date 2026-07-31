<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\{
    ActivoController,
    AsignacionController,
    AutenticacionController,
    CatalogoController,
    CodigoQrController,
    DevolucionController,
    MantenimientoController,
    PanelController,
    ReporteController,
    TrabajadorController
};

$root = dirname(__DIR__);

require $root.'/app/Core/nucleo.php';
require $root.'/app/Core/ayudantes.php';

if (is_file($root.'/vendor/autoload.php')) {
    require $root.'/vendor/autoload.php';
}

$modelFiles = [
    'ModeloBase',
    'Catalogo',
    'Trabajador',
    'Activo',
    'Asignacion',
    'DevolucionActivo',
    'Mantenimiento',
    'Panel',
];

foreach ($modelFiles as $file) {
    require $root.'/app/Models/'.$file.'.php';
}

$controllerFiles = [
    'AutenticacionController',
    'PanelController',
    'ActivoController',
    'TrabajadorController',
    'AsignacionController',
    'DevolucionController',
    'MantenimientoController',
    'CatalogoController',
    'ReporteController',
    'CodigoQrController',
];

foreach ($controllerFiles as $file) {
    require $root.'/app/Controllers/'.$file.'.php';
}

date_default_timezone_set((string) config('app.timezone', 'America/Lima'));

session_name('SIGATI_SOLANDRA');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

set_exception_handler(function (Throwable $exception): void {
    http_response_code(500);

    $debug = (bool) config('app.debug', false);
    $message = $debug ? $exception->getMessage() : 'Ocurrió un error interno.';

    @file_put_contents(
        dirname(__DIR__).'/storage/logs/app.log',
        '['.date('Y-m-d H:i:s').'] '.$exception."\n",
        FILE_APPEND
    );

    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Error</title>';
    echo '<style>body{font-family:Arial;margin:40px;background:#f8fafc;color:#172033}.box{max-width:820px;margin:auto;background:white;padding:28px;border-radius:14px;box-shadow:0 10px 30px #0001}code{white-space:pre-wrap}</style>';
    echo '<div class="box"><h1>Error del sistema</h1><p>'.e($message).'</p>';

    if ($debug) {
        echo '<code>'.e($exception->getTraceAsString()).'</code>';
    }

    echo '</div></html>';
});

$router = new Router();

// Autenticación
$router->get('/ingreso', [AutenticacionController::class, 'loginForm']);
$router->get('/login', [AutenticacionController::class, 'loginForm']);
$router->post('/ingreso', [AutenticacionController::class, 'login']);
$router->post('/login', [AutenticacionController::class, 'login']);
$router->post('/salir', [AutenticacionController::class, 'logout']);
$router->post('/logout', [AutenticacionController::class, 'logout']);

// Panel
$router->get('/', [PanelController::class, 'index']);

// Activos
$router->get('/activos', [ActivoController::class, 'index']);
$router->get('/activos/crear', [ActivoController::class, 'create']);
$router->post('/activos', [ActivoController::class, 'store']);
$router->get('/activos/importar', [ActivoController::class, 'importForm']);
$router->post('/activos/importar', [ActivoController::class, 'importCsv']);
$router->get('/activos/{id}/editar', [ActivoController::class, 'edit']);
$router->post('/activos/{id}', [ActivoController::class, 'update']);
$router->get('/activos/{id}/qr', [CodigoQrController::class, 'show']);
$router->get('/activos/{id}', [ActivoController::class, 'show']);

// Trabajadores
$router->get('/trabajadores', [TrabajadorController::class, 'index']);
$router->get('/trabajadores/crear', [TrabajadorController::class, 'create']);
$router->post('/trabajadores', [TrabajadorController::class, 'store']);
$router->get('/trabajadores/{id}/editar', [TrabajadorController::class, 'edit']);
$router->post('/trabajadores/{id}', [TrabajadorController::class, 'update']);

// Asignaciones
$router->get('/asignaciones', [AsignacionController::class, 'index']);
$router->get('/asignaciones/crear', [AsignacionController::class, 'create']);
$router->post('/asignaciones', [AsignacionController::class, 'store']);
$router->get('/asignaciones/{id}/imprimir', [AsignacionController::class, 'print']);
$router->get('/asignaciones/{id}/pdf', [AsignacionController::class, 'pdf']);
$router->get('/asignaciones/{id}', [AsignacionController::class, 'show']);

// Devoluciones
$router->get('/devoluciones', [DevolucionController::class, 'index']);
$router->get('/devoluciones/crear', [DevolucionController::class, 'create']);
$router->post('/devoluciones', [DevolucionController::class, 'store']);
$router->get('/devoluciones/{id}/imprimir', [DevolucionController::class, 'print']);
$router->get('/devoluciones/{id}/pdf', [DevolucionController::class, 'pdf']);
$router->get('/devoluciones/{id}', [DevolucionController::class, 'show']);

// Mantenimientos
$router->get('/mantenimientos', [MantenimientoController::class, 'index']);
$router->get('/mantenimientos/crear', [MantenimientoController::class, 'create']);
$router->post('/mantenimientos', [MantenimientoController::class, 'store']);
$router->post('/mantenimientos/{id}/cerrar', [MantenimientoController::class, 'close']);

// Catálogos y reportes
$router->get('/catalogos', [CatalogoController::class, 'index']);
$router->post('/catalogos/{table}', [CatalogoController::class, 'store']);
$router->get('/reportes/inventario', [ReporteController::class, 'inventory']);
$router->get('/reportes/inventario/csv', [ReporteController::class, 'csv']);

// Alias antiguos para no romper enlaces guardados.
$router->get('/assets', [ActivoController::class, 'index']);
$router->get('/assets/create', [ActivoController::class, 'create']);
$router->post('/assets', [ActivoController::class, 'store']);
$router->get('/assets/import', [ActivoController::class, 'importForm']);
$router->post('/assets/import', [ActivoController::class, 'importCsv']);
$router->get('/assets/{id}/edit', [ActivoController::class, 'edit']);
$router->post('/assets/{id}', [ActivoController::class, 'update']);
$router->get('/assets/{id}/qr', [CodigoQrController::class, 'show']);
$router->get('/assets/{id}', [ActivoController::class, 'show']);

$router->get('/employees', [TrabajadorController::class, 'index']);
$router->get('/employees/create', [TrabajadorController::class, 'create']);
$router->post('/employees', [TrabajadorController::class, 'store']);
$router->get('/employees/{id}/edit', [TrabajadorController::class, 'edit']);
$router->post('/employees/{id}', [TrabajadorController::class, 'update']);

$router->get('/assignments', [AsignacionController::class, 'index']);
$router->get('/assignments/create', [AsignacionController::class, 'create']);
$router->post('/assignments', [AsignacionController::class, 'store']);
$router->get('/assignments/{id}/print', [AsignacionController::class, 'print']);
$router->get('/assignments/{id}/pdf', [AsignacionController::class, 'pdf']);
$router->get('/assignments/{id}', [AsignacionController::class, 'show']);

$router->get('/returns', [DevolucionController::class, 'index']);
$router->get('/returns/create', [DevolucionController::class, 'create']);
$router->post('/returns', [DevolucionController::class, 'store']);
$router->get('/returns/{id}/print', [DevolucionController::class, 'print']);
$router->get('/returns/{id}/pdf', [DevolucionController::class, 'pdf']);
$router->get('/returns/{id}', [DevolucionController::class, 'show']);

$router->get('/maintenances', [MantenimientoController::class, 'index']);
$router->get('/maintenances/create', [MantenimientoController::class, 'create']);
$router->post('/maintenances', [MantenimientoController::class, 'store']);
$router->post('/maintenances/{id}/close', [MantenimientoController::class, 'close']);

$router->get('/catalogs', [CatalogoController::class, 'index']);
$router->post('/catalogs/{table}', [CatalogoController::class, 'store']);
$router->get('/reports/inventory', [ReporteController::class, 'inventory']);
$router->get('/reports/inventory/csv', [ReporteController::class, 'csv']);

$router->dispatch();

unset($_SESSION['_old'], $_SESSION['_errors']);