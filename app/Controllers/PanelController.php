<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Activo, DevolucionActivo, Asignacion, Catalogo, Panel, Trabajador, Mantenimiento};
use Throwable;

final class PanelController extends Controller {
    public function index():void{Auth::requireLogin();$this->view('panel',array_merge(['title'=>'Panel'],(new Panel())->data()));}
}
