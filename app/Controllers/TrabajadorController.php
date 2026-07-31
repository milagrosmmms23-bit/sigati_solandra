<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Activo, DevolucionActivo, Asignacion, Catalogo, Panel, Trabajador, Mantenimiento};
use Throwable;

final class TrabajadorController extends Controller {
    private Trabajador $model;private Catalogo $cat;public function __construct(){Auth::requireLogin();$this->model=new Trabajador();$this->cat=new Catalogo();}
    public function index():void{$q=trim($_GET['q']??'');$this->view('trabajadores',['mode'=>'index','title'=>'Trabajadores','rows'=>$this->model->all($q),'q'=>$q]);}
    public function create():void{$this->view('trabajadores',['mode'=>'form','title'=>'Nuevo trabajador','item'=>null,'areas'=>$this->cat->all('areas')]);}
    public function store():void{Csrf::verify();$d=$this->payload();$e=$this->validate($d,['employee_code'=>'required|max:50','first_name'=>'required','last_name'=>'required','email'=>'email']);if($e)$this->errors($e,$_POST,'trabajadores/create');try{$id=$this->model->save($d);Audit::log('Trabajadores','CREAR','employee',$id,null,$d);Flash::success('Trabajador registrado.');redirect('trabajadores');}catch(Throwable $x){Flash::error($x->getMessage());redirect('trabajadores/create');}}
    public function edit(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('trabajadores',['mode'=>'form','title'=>'Editar trabajador','item'=>$x,'areas'=>$this->cat->all('areas')]);}
    public function update(string $id):void{Csrf::verify();$old=$this->model->find((int)$id);if(!$old)abort(404);$d=$this->payload();$e=$this->validate($d,['employee_code'=>'required','first_name'=>'required','last_name'=>'required','email'=>'email']);if($e)$this->errors($e,$_POST,'trabajadores/'.$id.'/edit');$this->model->save($d,(int)$id);Audit::log('Trabajadores','ACTUALIZAR','employee',(int)$id,$old,$d);Flash::success('Trabajador actualizado.');redirect('trabajadores');}
    private function payload():array{return ['employee_code'=>trim($_POST['employee_code']??''),'first_name'=>trim($_POST['first_name']??''),'last_name'=>trim($_POST['last_name']??''),'email'=>trim($_POST['email']??''),'phone'=>trim($_POST['phone']??''),'position'=>trim($_POST['position']??''),'area_id'=>(int)($_POST['area_id']??0)];}
}
