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
$router->get('/ingreso', [AutenticacionController::class, 'formularioIngreso']);
$router->post('/ingreso', [AutenticacionController::class, 'ingresar']);
$router->post('/salir', [AutenticacionController::class, 'salir']);

// Panel
$router->get('/', [PanelController::class, 'inicio']);

// Activos
$router->get('/activos', [ActivoController::class, 'listado']);
$router->get('/activos/crear', [ActivoController::class, 'crear']);
$router->post('/activos', [ActivoController::class, 'guardar']);
$router->get('/activos/importar', [ActivoController::class, 'formularioImportacion']);
$router->post('/activos/importar', [ActivoController::class, 'importarCsv']);
$router->get('/activos/{id}/editar', [ActivoController::class, 'editar']);
$router->post('/activos/{id}', [ActivoController::class, 'actualizar']);
$router->get('/activos/{id}/qr', [CodigoQrController::class, 'ver']);
$router->get('/activos/{id}', [ActivoController::class, 'ver']);

// Trabajadores
$router->get('/trabajadores', [TrabajadorController::class, 'listado']);
$router->get('/trabajadores/crear', [TrabajadorController::class, 'crear']);
$router->post('/trabajadores', [TrabajadorController::class, 'guardar']);
$router->get('/trabajadores/{id}/editar', [TrabajadorController::class, 'editar']);
$router->post('/trabajadores/{id}', [TrabajadorController::class, 'actualizar']);

// Asignaciones
$router->get('/asignaciones', [AsignacionController::class, 'listado']);
$router->get('/asignaciones/crear', [AsignacionController::class, 'crear']);
$router->post('/asignaciones', [AsignacionController::class, 'guardar']);
$router->get('/asignaciones/{id}/imprimir', [AsignacionController::class, 'imprimir']);
$router->get('/asignaciones/{id}/pdf', [AsignacionController::class, 'pdf']);
$router->get('/asignaciones/{id}', [AsignacionController::class, 'ver']);

// Devoluciones
$router->get('/devoluciones', [DevolucionController::class, 'listado']);
$router->get('/devoluciones/crear', [DevolucionController::class, 'crear']);
$router->post('/devoluciones', [DevolucionController::class, 'guardar']);
$router->get('/devoluciones/{id}/imprimir', [DevolucionController::class, 'imprimir']);
$router->get('/devoluciones/{id}/pdf', [DevolucionController::class, 'pdf']);
$router->get('/devoluciones/{id}', [DevolucionController::class, 'ver']);

// Mantenimientos
$router->get('/mantenimientos', [MantenimientoController::class, 'listado']);
$router->get('/mantenimientos/crear', [MantenimientoController::class, 'crear']);
$router->post('/mantenimientos', [MantenimientoController::class, 'guardar']);
$router->post('/mantenimientos/{id}/cerrar', [MantenimientoController::class, 'cerrar']);

// Catálogos y reportes
$router->get('/catalogos', [CatalogoController::class, 'listado']);
$router->post('/catalogos/{table}', [CatalogoController::class, 'guardar']);
$router->get('/reportes/inventario', [ReporteController::class, 'inventario']);
$router->get('/reportes/inventario/csv', [ReporteController::class, 'exportarCsv']);

$router->dispatch();

unset($_SESSION['_old'], $_SESSION['_errors']);