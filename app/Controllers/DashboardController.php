<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Asset, AssetReturn, Assignment, Catalog, Dashboard, Employee, Maintenance};
use Throwable;

final class DashboardController extends Controller {
    public function index():void{Auth::requireLogin();$this->view('dashboard',array_merge(['title'=>'Dashboard'],(new Dashboard())->data()));}
}
