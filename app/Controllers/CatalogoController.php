<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Activo, DevolucionActivo, Asignacion, Catalogo, Panel, Trabajador, Mantenimiento};
use Throwable;

final class CatalogoController extends Controller {
    private Catalogo $model;public function __construct(){Auth::requireRole(['ADMIN']);$this->model=new Catalogo();}
    public function index():void{$rows=[];foreach($this->model->allowed as $k=>$v)$rows[$k]=$this->model->all($k);$this->view('catalogos',['title'=>'Catálogos','rows'=>$rows,'labels'=>$this->model->allowed]);}
    public function store(string $table):void{Csrf::verify();try{$this->model->create($table,$_POST);Flash::success('Registro agregado.');}catch(Throwable $e){Flash::error($e->getMessage());}redirect('catalogs#'.$table);}
}
