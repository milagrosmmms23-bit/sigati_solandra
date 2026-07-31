<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Activo, DevolucionActivo, Asignacion, Catalogo, Panel, Trabajador, Mantenimiento};
use Throwable;

final class ReporteController extends Controller {
    public function __construct(){Auth::requireLogin();}
    public function inventory():void{$this->view('reporte',['title'=>'Reporte de inventario','rows'=>(new Activo())->export()]);}
    public function csv():never{$rows=(new Activo())->export();header('Content-Type: text/csv; charset=UTF-8');header('Content-Disposition: attachment; filename="inventario_solandra_'.date('Ymd_His').'.csv"');$o=fopen('php://output','w');fwrite($o,"\xEF\xBB\xBF");if($rows){fputcsv($o,array_keys($rows[0]),';');foreach($rows as $r)fputcsv($o,$r,';');}fclose($o);exit;}
}
