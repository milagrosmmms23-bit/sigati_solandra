<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Asset, AssetReturn, Assignment, Catalog, Dashboard, Employee, Maintenance};
use Throwable;

final class ReportController extends Controller {
    public function __construct(){Auth::requireLogin();}
    public function inventory():void{$this->view('report',['title'=>'Reporte de inventario','rows'=>(new Asset())->export()]);}
    public function csv():never{$rows=(new Asset())->export();header('Content-Type: text/csv; charset=UTF-8');header('Content-Disposition: attachment; filename="inventario_solandra_'.date('Ymd_His').'.csv"');$o=fopen('php://output','w');fwrite($o,"\xEF\xBB\xBF");if($rows){fputcsv($o,array_keys($rows[0]),';');foreach($rows as $r)fputcsv($o,$r,';');}fclose($o);exit;}
}
