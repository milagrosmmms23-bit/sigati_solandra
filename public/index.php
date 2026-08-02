<?php
declare(strict_types=1);

use App\Nucleo\Router;
use App\Controladores\{
    ActivoControlador,
    AsignacionControlador,
    AutenticacionControlador,
    CatalogoControlador,
    CodigoQrControlador,
    DevolucionControlador,
    MantenimientoControlador,
    PanelControlador,
    ReporteControlador,
    TrabajadorControlador
};

$root = dirname(__DIR__);

require $root.'/app/Nucleo/nucleo.php';
require $root.'/app/Nucleo/ayudantes.php';

if (is_file($root.'/vendor/autoload.php')) {
    require $root.'/vendor/autoload.php';
}

$archivosModelos = [
    'ModeloBase',
    'Catalogo',
    'Trabajador',
    'Activo',
    'Asignacion',
    'DevolucionActivo',
    'Mantenimiento',
    'Panel',
];

foreach ($archivosModelos as $file) {
    require $root.'/app/Modelos/'.$file.'.php';
}

$archivosControladores = [
    'AutenticacionControlador',
    'PanelControlador',
    'ActivoControlador',
    'TrabajadorControlador',
    'AsignacionControlador',
    'DevolucionControlador',
    'MantenimientoControlador',
    'CatalogoControlador',
    'ReporteControlador',
    'CodigoQrControlador',
];

foreach ($archivosControladores as $file) {
    require $root.'/app/Controladores/'.$file.'.php';
}

date_default_timezone_set((string) config('aplicacion.zona_horaria', 'America/Lima'));

session_name('SIGATI_SOLANDRA');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

set_exception_handler(function (Throwable $exception): void {
    http_response_code(500);

    $debug = (bool) config('aplicacion.depuracion', false);
    $message = $debug ? $exception->getMessage() : 'Ocurrio un error interno.';

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

// Autenticacion
$router->get('/ingreso', [AutenticacionControlador::class, 'formularioIngreso']);
$router->post('/ingreso', [AutenticacionControlador::class, 'ingresar']);
$router->post('/salir', [AutenticacionControlador::class, 'salir']);

// Panel
$router->get('/', [PanelControlador::class, 'inicio']);

// Activos
$router->get('/activos', [ActivoControlador::class, 'listado']);
$router->get('/activos/crear', [ActivoControlador::class, 'crear']);
$router->post('/activos', [ActivoControlador::class, 'guardar']);
$router->get('/activos/importar', [ActivoControlador::class, 'formularioImportacion']);
$router->post('/activos/importar', [ActivoControlador::class, 'importarCsv']);
$router->get('/activos/{id}/editar', [ActivoControlador::class, 'editar']);
$router->post('/activos/{id}', [ActivoControlador::class, 'actualizar']);
$router->get('/activos/{id}/qr', [CodigoQrControlador::class, 'ver']);
$router->get('/activos/{id}', [ActivoControlador::class, 'ver']);

// Trabajadores
$router->get('/trabajadores', [TrabajadorControlador::class, 'listado']);
$router->get('/trabajadores/crear', [TrabajadorControlador::class, 'crear']);
$router->get('/trabajadores/importar', [TrabajadorControlador::class, 'formularioImportacion']);
$router->post('/trabajadores/importar', [TrabajadorControlador::class, 'importarArchivo']);
$router->post('/trabajadores', [TrabajadorControlador::class, 'guardar']);
$router->get('/trabajadores/{id}/editar', [TrabajadorControlador::class, 'editar']);
$router->post('/trabajadores/{id}', [TrabajadorControlador::class, 'actualizar']);

// Asignaciones
$router->get('/asignaciones', [AsignacionControlador::class, 'listado']);
$router->get('/asignaciones/crear', [AsignacionControlador::class, 'crear']);
$router->get('/asignaciones/importar', [AsignacionControlador::class, 'formularioImportacion']);
$router->post('/asignaciones/importar', [AsignacionControlador::class, 'importarArchivo']);
$router->post('/asignaciones', [AsignacionControlador::class, 'guardar']);
$router->get('/asignaciones/{id}/imprimir', [AsignacionControlador::class, 'imprimir']);
$router->get('/asignaciones/{id}/pdf', [AsignacionControlador::class, 'pdf']);
$router->get('/asignaciones/{id}', [AsignacionControlador::class, 'ver']);

// Devoluciones
$router->get('/devoluciones', [DevolucionControlador::class, 'listado']);
$router->get('/devoluciones/crear', [DevolucionControlador::class, 'crear']);
$router->post('/devoluciones', [DevolucionControlador::class, 'guardar']);
$router->get('/devoluciones/{id}/imprimir', [DevolucionControlador::class, 'imprimir']);
$router->get('/devoluciones/{id}/pdf', [DevolucionControlador::class, 'pdf']);
$router->get('/devoluciones/{id}', [DevolucionControlador::class, 'ver']);

// Mantenimientos
$router->get('/mantenimientos', [MantenimientoControlador::class, 'listado']);
$router->get('/mantenimientos/crear', [MantenimientoControlador::class, 'crear']);
$router->post('/mantenimientos', [MantenimientoControlador::class, 'guardar']);
$router->post('/mantenimientos/{id}/cerrar', [MantenimientoControlador::class, 'cerrar']);

// Catalogos y reportes
$router->get('/catalogos', [CatalogoControlador::class, 'listado']);
$router->post('/catalogos/{table}', [CatalogoControlador::class, 'guardar']);
$router->get('/reportes/inventario', [ReporteControlador::class, 'inventario']);
$router->get('/reportes/inventario/csv', [ReporteControlador::class, 'exportarCsv']);

$router->dispatch();

unset($_SESSION['_old'], $_SESSION['_errors']);