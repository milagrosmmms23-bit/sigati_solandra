<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Asset, AssetReturn, Assignment, Catalog, Dashboard, Employee, Maintenance};
use Throwable;

final class ReturnController extends Controller {
    private AssetReturn $model;public function __construct(){Auth::requireLogin();$this->model=new AssetReturn();}
    public function index():void{$this->view('returns',['mode'=>'index','title'=>'Devoluciones','rows'=>$this->model->all()]);}
    public function create():void{$id=(int)($_GET['assignment_id']??0);$assignment=$id?(new Assignment())->find($id):null;$this->view('returns',['mode'=>'form','title'=>'Nueva devolución','assignments'=>(new Assignment())->active(),'assignment'=>$assignment,'statuses'=>(new Catalog())->all('asset_statuses')]);}
    public function store():void{Csrf::verify();$assignment=(int)($_POST['assignment_id']??0);$ids=array_values(array_unique(array_map('intval',$_POST['item_ids']??[])));if(!$assignment||!$ids){Flash::error('Selecciona al menos un equipo.');redirect('returns/create?assignment_id='.$assignment);}$items=[];foreach($ids as $id)$items[]=['item_id'=>$id,'condition'=>trim($_POST['condition'][$id]??'Buen estado'),'damage'=>trim($_POST['damage'][$id]??''),'status_id'=>(int)($_POST['status_id'][$id]??0)];try{$id=$this->model->create($assignment,trim($_POST['notes']??''),$items,Auth::id());Flash::success('Devolución registrada.');redirect('returns/'.$id);}catch(Throwable $e){Flash::error($e->getMessage());redirect('returns/create?assignment_id='.$assignment);}}
    public function show(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('returns',['mode'=>'show','title'=>$x['return_number'],'item'=>$x]);}
    public function print(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('print',['doc'=>'return','title'=>$x['return_number'],'item'=>$x],'print_layout');}
    public function pdf(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);if(!class_exists('Dompdf\\Dompdf'))redirect('returns/'.$id.'/print');$html=View::capture('print',['doc'=>'return','item'=>$x,'pdf'=>true]);$d=new \Dompdf\Dompdf();$d->loadHtml($html,'UTF-8');$d->setPaper('A4');$d->render();$d->stream($x['return_number'].'.pdf',['Attachment'=>true]);}
}
