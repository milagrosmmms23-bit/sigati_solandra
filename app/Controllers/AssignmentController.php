<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Asset, AssetReturn, Assignment, Catalog, Dashboard, Employee, Maintenance};
use Throwable;

final class AssignmentController extends Controller {
    private Assignment $model;public function __construct(){Auth::requireLogin();$this->model=new Assignment();}
    public function index():void{$this->view('assignments',['mode'=>'index','title'=>'Asignaciones','rows'=>$this->model->all()]);}
    public function create():void{$this->view('assignments',['mode'=>'form','title'=>'Nueva asignación','employees'=>(new Employee())->all(),'assets'=>(new Asset())->available()]);}
    public function store():void{Csrf::verify();$employee=(int)($_POST['employee_id']??0);$ids=array_values(array_unique(array_map('intval',$_POST['asset_ids']??[])));if(!$employee||!$ids){Flash::error('Selecciona un trabajador y al menos un activo.');redirect('assignments/create');}$items=[];foreach($ids as $id)$items[]=['asset_id'=>$id,'condition'=>trim($_POST['condition'][$id]??'Buen estado')];try{$id=$this->model->create($employee,(int)($_POST['area_id']??0)?:null,trim($_POST['notes']??''),$items,Auth::id());Flash::success('Asignación confirmada.');redirect('assignments/'.$id);}catch(Throwable $e){Flash::error($e->getMessage());redirect('assignments/create');}}
    public function show(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('assignments',['mode'=>'show','title'=>$x['assignment_number'],'item'=>$x]);}
    public function print(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('print',['doc'=>'assignment','title'=>$x['assignment_number'],'item'=>$x],'print_layout');}
    public function pdf(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);if(!class_exists('Dompdf\\Dompdf'))redirect('assignments/'.$id.'/print');$html=View::capture('print',['doc'=>'assignment','item'=>$x,'pdf'=>true]);$d=new \Dompdf\Dompdf();$d->loadHtml($html,'UTF-8');$d->setPaper('A4');$d->render();$d->stream($x['assignment_number'].'.pdf',['Attachment'=>true]);}
}
