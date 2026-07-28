<?php
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\{
    AssetController, AssignmentController, AuthController, CatalogController,
    DashboardController, EmployeeController, MaintenanceController,
    QrController, ReportController, ReturnController
};

$root = dirname(__DIR__);

require $root.'/app/Core/core.php';
require $root.'/app/Core/helpers.php';

if (is_file($root.'/vendor/autoload.php')) {
    require $root.'/vendor/autoload.php';
}

require $root.'/app/Models/models.php';
require $root.'/app/Controllers/controllers.php';

date_default_timezone_set((string) config('app.timezone', 'America/Lima'));

session_name('SIGATI_SOLANDRA');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

set_exception_handler(function (Throwable $e): void {
    http_response_code(500);
    $debug = (bool) config('app.debug', false);
    $message = $debug ? $e->getMessage() : 'Ocurrió un error interno.';
    @file_put_contents(
        dirname(__DIR__).'/storage/logs/app.log',
        '['.date('Y-m-d H:i:s').'] '.$e."\n",
        FILE_APPEND
    );
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Error</title>';
    echo '<style>body{font-family:Arial;margin:40px;background:#f8fafc;color:#172033}.box{max-width:820px;margin:auto;background:white;padding:28px;border-radius:14px;box-shadow:0 10px 30px #0001}code{white-space:pre-wrap}</style>';
    echo '<div class="box"><h1>Error del sistema</h1><p>'.e($message).'</p>';
    if ($debug) echo '<code>'.e($e->getTraceAsString()).'</code>';
    echo '</div></html>';
});

$router = new Router();

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/', [DashboardController::class, 'index']);

$router->get('/assets', [AssetController::class, 'index']);
$router->get('/assets/create', [AssetController::class, 'create']);
$router->post('/assets', [AssetController::class, 'store']);
$router->get('/assets/import', [AssetController::class, 'importForm']);
$router->post('/assets/import', [AssetController::class, 'importCsv']);
$router->get('/assets/{id}/edit', [AssetController::class, 'edit']);
$router->post('/assets/{id}', [AssetController::class, 'update']);
$router->get('/assets/{id}/qr', [QrController::class, 'show']);
$router->get('/assets/{id}', [AssetController::class, 'show']);

$router->get('/employees', [EmployeeController::class, 'index']);
$router->get('/employees/create', [EmployeeController::class, 'create']);
$router->post('/employees', [EmployeeController::class, 'store']);
$router->get('/employees/{id}/edit', [EmployeeController::class, 'edit']);
$router->post('/employees/{id}', [EmployeeController::class, 'update']);

$router->get('/assignments', [AssignmentController::class, 'index']);
$router->get('/assignments/create', [AssignmentController::class, 'create']);
$router->post('/assignments', [AssignmentController::class, 'store']);
$router->get('/assignments/{id}/print', [AssignmentController::class, 'print']);
$router->get('/assignments/{id}/pdf', [AssignmentController::class, 'pdf']);
$router->get('/assignments/{id}', [AssignmentController::class, 'show']);

$router->get('/returns', [ReturnController::class, 'index']);
$router->get('/returns/create', [ReturnController::class, 'create']);
$router->post('/returns', [ReturnController::class, 'store']);
$router->get('/returns/{id}/print', [ReturnController::class, 'print']);
$router->get('/returns/{id}/pdf', [ReturnController::class, 'pdf']);
$router->get('/returns/{id}', [ReturnController::class, 'show']);

$router->get('/maintenances', [MaintenanceController::class, 'index']);
$router->get('/maintenances/create', [MaintenanceController::class, 'create']);
$router->post('/maintenances', [MaintenanceController::class, 'store']);
$router->post('/maintenances/{id}/close', [MaintenanceController::class, 'close']);

$router->get('/catalogs', [CatalogController::class, 'index']);
$router->post('/catalogs/{table}', [CatalogController::class, 'store']);

$router->get('/reports/inventory', [ReportController::class, 'inventory']);
$router->get('/reports/inventory/csv', [ReportController::class, 'csv']);

$router->dispatch();

unset($_SESSION['_old'], $_SESSION['_errors']);
