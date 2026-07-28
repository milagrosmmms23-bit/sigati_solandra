<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Asset, AssetReturn, Assignment, Catalog, Dashboard, Employee, Maintenance};
use Throwable;

final class MaintenanceController extends Controller {
    private Maintenance $model;public function __construct(){Auth::requireLogin();$this->model=new Maintenance();}
    public function index():void{$this->view('maintenances',['mode'=>'index','title'=>'Mantenimientos','rows'=>$this->model->all()]);}
    public function create():void{$assets=DB::pdo()->query("SELECT a.id,a.asset_code,a.serial_number,t.name type_name FROM assets a JOIN asset_types t ON t.id=a.asset_type_id WHERE a.active=1 ORDER BY a.asset_code")->fetchAll();$this->view('maintenances',['mode'=>'form','title'=>'Nuevo mantenimiento','assets'=>$assets]);}
    public function store():void{Csrf::verify();$d=['asset_id'=>(int)($_POST['asset_id']??0),'type'=>$_POST['type']??'PREVENTIVO','issue'=>trim($_POST['issue']??''),'diagnosis'=>trim($_POST['diagnosis']??''),'actions'=>trim($_POST['actions']??''),'cost'=>trim($_POST['cost']??'0')];try{$this->model->open($d,Auth::id());Flash::success('Mantenimiento abierto.');redirect('maintenances');}catch(Throwable $e){Flash::error($e->getMessage());redirect('maintenances/create');}}
    public function close(string $id):void{Csrf::verify();$d=['diagnosis'=>trim($_POST['diagnosis']??''),'actions'=>trim($_POST['actions']??''),'parts'=>trim($_POST['parts']??''),'cost'=>trim($_POST['cost']??'0'),'next_date'=>trim($_POST['next_date']??'')];try{$this->model->close((int)$id,$d,Auth::id());Flash::success('Mantenimiento cerrado.');}catch(Throwable $e){Flash::error($e->getMessage());}redirect('maintenances');}
}
