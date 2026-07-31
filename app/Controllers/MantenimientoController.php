<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Activo, DevolucionActivo, Asignacion, Catalogo, Panel, Trabajador, Mantenimiento};
use Throwable;

final class MantenimientoController extends Controller {
    private Mantenimiento $model;public function __construct(){Auth::requireLogin();$this->model=new Mantenimiento();}
    public function index():void{$this->view('mantenimientos',['mode'=>'index','title'=>'Mantenimientos','rows'=>$this->model->all()]);}
    public function create():void{$activos=DB::pdo()->query("SELECT a.id,a.asset_code,a.serial_number,t.name type_name FROM activos a JOIN tipos_activo t ON t.id=a.asset_type_id WHERE a.active=1 ORDER BY a.asset_code")->fetchAll();$this->view('mantenimientos',['mode'=>'form','title'=>'Nuevo mantenimiento','activos'=>$activos]);}
    public function store():void{Csrf::verify();$d=['asset_id'=>(int)($_POST['asset_id']??0),'type'=>$_POST['type']??'PREVENTIVO','issue'=>trim($_POST['issue']??''),'diagnosis'=>trim($_POST['diagnosis']??''),'actions'=>trim($_POST['actions']??''),'cost'=>trim($_POST['cost']??'0')];try{$this->model->open($d,Auth::id());Flash::success('Mantenimiento abierto.');redirect('mantenimientos');}catch(Throwable $e){Flash::error($e->getMessage());redirect('mantenimientos/create');}}
    public function close(string $id):void{Csrf::verify();$d=['diagnosis'=>trim($_POST['diagnosis']??''),'actions'=>trim($_POST['actions']??''),'parts'=>trim($_POST['parts']??''),'cost'=>trim($_POST['cost']??'0'),'next_date'=>trim($_POST['next_date']??'')];try{$this->model->close((int)$id,$d,Auth::id());Flash::success('Mantenimiento cerrado.');}catch(Throwable $e){Flash::error($e->getMessage());}redirect('mantenimientos');}
}
