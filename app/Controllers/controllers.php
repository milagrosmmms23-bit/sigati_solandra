<?php
namespace App\Controllers;

use App\Core\{Auth,Audit,Controller,Csrf,DB,Flash,View};
use App\Models\{Asset,AssetReturn,Assignment,Catalog,Dashboard,Employee,Maintenance};
use Throwable;

final class AuthController extends Controller {
    public function loginForm():void{if(Auth::check())redirect('');$this->view('login',['title'=>'Iniciar sesión'],'auth');}
    public function login():void{Csrf::verify();if(Auth::attempt(trim($_POST['username']??''),(string)($_POST['password']??''))){Flash::success('Bienvenido a SIGATI SOLANDRA.');redirect('');}Flash::error('Usuario o contraseña incorrectos.');redirect('login');}
    public function logout():void{Csrf::verify();Auth::logout();Flash::success('Sesión cerrada.');redirect('login');}
}

final class DashboardController extends Controller {
    public function index():void{Auth::requireLogin();$this->view('dashboard',array_merge(['title'=>'Dashboard'],(new Dashboard())->data()));}
}

final class AssetController extends Controller {
    private Asset $model;private Catalog $cat;
    public function __construct(){Auth::requireLogin();$this->model=new Asset();$this->cat=new Catalog();}
    public function index():void{$f=['q'=>trim($_GET['q']??''),'type_id'=>$_GET['type_id']??'','status_id'=>$_GET['status_id']??'','area_id'=>$_GET['area_id']??''];$p=max(1,(int)($_GET['page']??1));$this->view('assets',['mode'=>'index','title'=>'Inventario','result'=>$this->model->list($f,$p,(int)config('app.items_per_page',15)),'filters'=>$f]+$this->catalogs());}
    public function create():void{$this->view('assets',['mode'=>'form','title'=>'Nuevo activo','item'=>null]+$this->catalogs());}
    public function store():void{Csrf::verify();$d=$this->payload();$er=$this->validate($d,['asset_type_id'=>'required','status_id'=>'required','serial_number'=>'max:150']);if($er)$this->errors($er,$_POST,'assets/create');try{$id=$this->model->save($d,$this->specs(),Auth::id());Audit::log('Inventario','CREAR','asset',$id,null,$d);Flash::success('Activo registrado correctamente.');redirect('assets/'.$id);}catch(Throwable $e){Flash::error('No se pudo registrar: '.$e->getMessage());$_SESSION['_old']=$_POST;redirect('assets/create');}}
    public function show(string $id):void{$a=$this->model->find((int)$id);if(!$a)abort(404,'Activo no encontrado.');$this->view('assets',['mode'=>'show','title'=>$a['asset_code'],'item'=>$a]);}
    public function edit(string $id):void{$a=$this->model->find((int)$id);if(!$a)abort(404);$this->view('assets',['mode'=>'form','title'=>'Editar '.$a['asset_code'],'item'=>$a]+$this->catalogs());}
    public function update(string $id):void{Csrf::verify();$old=$this->model->find((int)$id);if(!$old)abort(404);$d=$this->payload();$er=$this->validate($d,['asset_type_id'=>'required','status_id'=>'required']);if($er)$this->errors($er,$_POST,'assets/'.$id.'/edit');try{$this->model->save($d,$this->specs(),Auth::id(),(int)$id);Audit::log('Inventario','ACTUALIZAR','asset',(int)$id,$old,$d);Flash::success('Activo actualizado.');redirect('assets/'.$id);}catch(Throwable $e){Flash::error($e->getMessage());redirect('assets/'.$id.'/edit');}}
    public function importForm():void{$this->view('assets',['mode'=>'import','title'=>'Importar inventario']);}
    public function importCsv():void{Csrf::verify();if(empty($_FILES['csv']['tmp_name'])){Flash::error('Selecciona un CSV.');redirect('assets/import');}$h=fopen($_FILES['csv']['tmp_name'],'r');$headers=fgetcsv($h,0,',');if(!$headers){Flash::error('CSV vacío.');redirect('assets/import');}$headers=array_map(fn($x)=>trim(strtolower($x)),$headers);$ok=0;$errors=[];while(($row=fgetcsv($h,0,','))!==false){if(count($row)!==count($headers))continue;$r=array_combine($headers,$row);try{$type=$this->findByName('asset_types',$r['tipo']??'');if(!$type)throw new \RuntimeException('Tipo inexistente: '.($r['tipo']??''));$status=$this->findByCode('asset_statuses','DISPONIBLE');$brand=$this->findOrCreate('brands',$r['marca']??'');$model=$this->findModel($brand,$r['modelo']??'');$area=$this->findOrCreate('areas',$r['area']??'');$location=$this->findLocation($area,$r['ubicacion']??'');$supplier=$this->findOrCreate('suppliers',$r['proveedor']??'');$d=['legacy_code'=>$r['codigo_anterior']??'','asset_type_id'=>$type,'brand_id'=>$brand,'model_id'=>$model,'status_id'=>$status,'current_area_id'=>$area,'location_id'=>$location,'serial_number'=>$r['serie']??'','hostname'=>$r['hostname']??'','ip_address'=>$r['ip']??'','mac_address'=>$r['mac']??'','imei1'=>$r['imei1']??'','imei2'=>$r['imei2']??'','phone_number'=>$r['telefono']??'','purchase_date'=>$r['fecha_compra']??'','invoice_number'=>$r['factura']??'','supplier_id'=>$supplier,'cost'=>$r['costo']??'','warranty_end'=>$r['fin_garantia']??'','notes'=>$r['observaciones']??''];$this->model->save($d,[],Auth::id());$ok++;}catch(Throwable $e){$errors[]=$e->getMessage();}}fclose($h);Flash::success("Se importaron $ok activos.");if($errors)Flash::warning(implode(' | ',array_slice($errors,0,4)));redirect('assets');}
    private function catalogs():array{return ['types'=>$this->cat->all('asset_types'),'statuses'=>$this->cat->all('asset_statuses'),'brands'=>$this->cat->all('brands'),'models'=>$this->cat->all('models'),'areas'=>$this->cat->all('areas'),'locations'=>$this->cat->all('locations'),'suppliers'=>$this->cat->all('suppliers')];}
    private function payload():array{return ['legacy_code'=>trim($_POST['legacy_code']??''),'asset_type_id'=>(int)($_POST['asset_type_id']??0),'brand_id'=>(int)($_POST['brand_id']??0),'model_id'=>(int)($_POST['model_id']??0),'status_id'=>(int)($_POST['status_id']??0),'current_area_id'=>(int)($_POST['current_area_id']??0),'location_id'=>(int)($_POST['location_id']??0),'serial_number'=>trim($_POST['serial_number']??''),'hostname'=>trim($_POST['hostname']??''),'ip_address'=>trim($_POST['ip_address']??''),'mac_address'=>trim($_POST['mac_address']??''),'imei1'=>trim($_POST['imei1']??''),'imei2'=>trim($_POST['imei2']??''),'phone_number'=>trim($_POST['phone_number']??''),'purchase_date'=>trim($_POST['purchase_date']??''),'invoice_number'=>trim($_POST['invoice_number']??''),'supplier_id'=>(int)($_POST['supplier_id']??0),'cost'=>trim($_POST['cost']??''),'warranty_end'=>trim($_POST['warranty_end']??''),'notes'=>trim($_POST['notes']??'')];}
    private function specs():array{$keys=$_POST['spec_key']??[];$vals=$_POST['spec_value']??[];$o=[];foreach($keys as $i=>$k)if(trim($k)!=='')$o[trim($k)]=trim($vals[$i]??'');return $o;}
    private function findByName(string $t,string $n):?int{if(trim($n)==='')return null;$s=DB::pdo()->prepare("SELECT id FROM $t WHERE LOWER(name)=LOWER(?) LIMIT 1");$s->execute([trim($n)]);$v=$s->fetchColumn();return $v?(int)$v:null;}
    private function findByCode(string $t,string $c):int{$s=DB::pdo()->prepare("SELECT id FROM $t WHERE code=?");$s->execute([$c]);return (int)$s->fetchColumn();}
    private function findOrCreate(string $t,string $n):?int{if(trim($n)==='')return null;$id=$this->findByName($t,$n);if($id)return $id;$s=DB::pdo()->prepare("INSERT INTO $t(name,active) VALUES(?,1)");$s->execute([trim($n)]);return (int)DB::pdo()->lastInsertId();}
    private function findModel(?int $brand,string $n):?int{if(trim($n)==='')return null;$s=DB::pdo()->prepare('SELECT id FROM models WHERE brand_id<=>? AND LOWER(name)=LOWER(?)');$s->execute([$brand,trim($n)]);$v=$s->fetchColumn();if($v)return(int)$v;$s=DB::pdo()->prepare('INSERT INTO models(brand_id,name,active) VALUES(?,?,1)');$s->execute([$brand,trim($n)]);return(int)DB::pdo()->lastInsertId();}
    private function findLocation(?int $area,string $n):?int{if(trim($n)==='')return null;$s=DB::pdo()->prepare('SELECT id FROM locations WHERE area_id<=>? AND LOWER(name)=LOWER(?)');$s->execute([$area,trim($n)]);$v=$s->fetchColumn();if($v)return(int)$v;$s=DB::pdo()->prepare('INSERT INTO locations(area_id,name,active) VALUES(?,?,1)');$s->execute([$area,trim($n)]);return(int)DB::pdo()->lastInsertId();}
}

final class EmployeeController extends Controller {
    private Employee $model;private Catalog $cat;public function __construct(){Auth::requireLogin();$this->model=new Employee();$this->cat=new Catalog();}
    public function index():void{$q=trim($_GET['q']??'');$this->view('employees',['mode'=>'index','title'=>'Trabajadores','rows'=>$this->model->all($q),'q'=>$q]);}
    public function create():void{$this->view('employees',['mode'=>'form','title'=>'Nuevo trabajador','item'=>null,'areas'=>$this->cat->all('areas')]);}
    public function store():void{Csrf::verify();$d=$this->payload();$e=$this->validate($d,['employee_code'=>'required|max:50','first_name'=>'required','last_name'=>'required','email'=>'email']);if($e)$this->errors($e,$_POST,'employees/create');try{$id=$this->model->save($d);Audit::log('Trabajadores','CREAR','employee',$id,null,$d);Flash::success('Trabajador registrado.');redirect('employees');}catch(Throwable $x){Flash::error($x->getMessage());redirect('employees/create');}}
    public function edit(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('employees',['mode'=>'form','title'=>'Editar trabajador','item'=>$x,'areas'=>$this->cat->all('areas')]);}
    public function update(string $id):void{Csrf::verify();$old=$this->model->find((int)$id);if(!$old)abort(404);$d=$this->payload();$e=$this->validate($d,['employee_code'=>'required','first_name'=>'required','last_name'=>'required','email'=>'email']);if($e)$this->errors($e,$_POST,'employees/'.$id.'/edit');$this->model->save($d,(int)$id);Audit::log('Trabajadores','ACTUALIZAR','employee',(int)$id,$old,$d);Flash::success('Trabajador actualizado.');redirect('employees');}
    private function payload():array{return ['employee_code'=>trim($_POST['employee_code']??''),'first_name'=>trim($_POST['first_name']??''),'last_name'=>trim($_POST['last_name']??''),'email'=>trim($_POST['email']??''),'phone'=>trim($_POST['phone']??''),'position'=>trim($_POST['position']??''),'area_id'=>(int)($_POST['area_id']??0)];}
}

final class AssignmentController extends Controller {
    private Assignment $model;public function __construct(){Auth::requireLogin();$this->model=new Assignment();}
    public function index():void{$this->view('assignments',['mode'=>'index','title'=>'Asignaciones','rows'=>$this->model->all()]);}
    public function create():void{$this->view('assignments',['mode'=>'form','title'=>'Nueva asignación','employees'=>(new Employee())->all(),'assets'=>(new Asset())->available()]);}
    public function store():void{Csrf::verify();$employee=(int)($_POST['employee_id']??0);$ids=array_values(array_unique(array_map('intval',$_POST['asset_ids']??[])));if(!$employee||!$ids){Flash::error('Selecciona un trabajador y al menos un activo.');redirect('assignments/create');}$items=[];foreach($ids as $id)$items[]=['asset_id'=>$id,'condition'=>trim($_POST['condition'][$id]??'Buen estado')];try{$id=$this->model->create($employee,(int)($_POST['area_id']??0)?:null,trim($_POST['notes']??''),$items,Auth::id());Flash::success('Asignación confirmada.');redirect('assignments/'.$id);}catch(Throwable $e){Flash::error($e->getMessage());redirect('assignments/create');}}
    public function show(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('assignments',['mode'=>'show','title'=>$x['assignment_number'],'item'=>$x]);}
    public function print(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('print',['doc'=>'assignment','title'=>$x['assignment_number'],'item'=>$x],'print_layout');}
    public function pdf(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);if(!class_exists('Dompdf\\Dompdf'))redirect('assignments/'.$id.'/print');$html=View::capture('print',['doc'=>'assignment','item'=>$x,'pdf'=>true]);$d=new \Dompdf\Dompdf();$d->loadHtml($html,'UTF-8');$d->setPaper('A4');$d->render();$d->stream($x['assignment_number'].'.pdf',['Attachment'=>true]);}
}

final class ReturnController extends Controller {
    private AssetReturn $model;public function __construct(){Auth::requireLogin();$this->model=new AssetReturn();}
    public function index():void{$this->view('returns',['mode'=>'index','title'=>'Devoluciones','rows'=>$this->model->all()]);}
    public function create():void{$id=(int)($_GET['assignment_id']??0);$assignment=$id?(new Assignment())->find($id):null;$this->view('returns',['mode'=>'form','title'=>'Nueva devolución','assignments'=>(new Assignment())->active(),'assignment'=>$assignment,'statuses'=>(new Catalog())->all('asset_statuses')]);}
    public function store():void{Csrf::verify();$assignment=(int)($_POST['assignment_id']??0);$ids=array_values(array_unique(array_map('intval',$_POST['item_ids']??[])));if(!$assignment||!$ids){Flash::error('Selecciona al menos un equipo.');redirect('returns/create?assignment_id='.$assignment);}$items=[];foreach($ids as $id)$items[]=['item_id'=>$id,'condition'=>trim($_POST['condition'][$id]??'Buen estado'),'damage'=>trim($_POST['damage'][$id]??''),'status_id'=>(int)($_POST['status_id'][$id]??0)];try{$id=$this->model->create($assignment,trim($_POST['notes']??''),$items,Auth::id());Flash::success('Devolución registrada.');redirect('returns/'.$id);}catch(Throwable $e){Flash::error($e->getMessage());redirect('returns/create?assignment_id='.$assignment);}}
    public function show(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('returns',['mode'=>'show','title'=>$x['return_number'],'item'=>$x]);}
    public function print(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);$this->view('print',['doc'=>'return','title'=>$x['return_number'],'item'=>$x],'print_layout');}
    public function pdf(string $id):void{$x=$this->model->find((int)$id);if(!$x)abort(404);if(!class_exists('Dompdf\\Dompdf'))redirect('returns/'.$id.'/print');$html=View::capture('print',['doc'=>'return','item'=>$x,'pdf'=>true]);$d=new \Dompdf\Dompdf();$d->loadHtml($html,'UTF-8');$d->setPaper('A4');$d->render();$d->stream($x['return_number'].'.pdf',['Attachment'=>true]);}
}

final class MaintenanceController extends Controller {
    private Maintenance $model;public function __construct(){Auth::requireLogin();$this->model=new Maintenance();}
    public function index():void{$this->view('maintenances',['mode'=>'index','title'=>'Mantenimientos','rows'=>$this->model->all()]);}
    public function create():void{$assets=DB::pdo()->query("SELECT a.id,a.asset_code,a.serial_number,t.name type_name FROM assets a JOIN asset_types t ON t.id=a.asset_type_id WHERE a.active=1 ORDER BY a.asset_code")->fetchAll();$this->view('maintenances',['mode'=>'form','title'=>'Nuevo mantenimiento','assets'=>$assets]);}
    public function store():void{Csrf::verify();$d=['asset_id'=>(int)($_POST['asset_id']??0),'type'=>$_POST['type']??'PREVENTIVO','issue'=>trim($_POST['issue']??''),'diagnosis'=>trim($_POST['diagnosis']??''),'actions'=>trim($_POST['actions']??''),'cost'=>trim($_POST['cost']??'0')];try{$this->model->open($d,Auth::id());Flash::success('Mantenimiento abierto.');redirect('maintenances');}catch(Throwable $e){Flash::error($e->getMessage());redirect('maintenances/create');}}
    public function close(string $id):void{Csrf::verify();$d=['diagnosis'=>trim($_POST['diagnosis']??''),'actions'=>trim($_POST['actions']??''),'parts'=>trim($_POST['parts']??''),'cost'=>trim($_POST['cost']??'0'),'next_date'=>trim($_POST['next_date']??'')];try{$this->model->close((int)$id,$d,Auth::id());Flash::success('Mantenimiento cerrado.');}catch(Throwable $e){Flash::error($e->getMessage());}redirect('maintenances');}
}

final class CatalogController extends Controller {
    private Catalog $model;public function __construct(){Auth::requireRole(['ADMIN']);$this->model=new Catalog();}
    public function index():void{$rows=[];foreach($this->model->allowed as $k=>$v)$rows[$k]=$this->model->all($k);$this->view('catalogs',['title'=>'Catálogos','rows'=>$rows,'labels'=>$this->model->allowed]);}
    public function store(string $table):void{Csrf::verify();try{$this->model->create($table,$_POST);Flash::success('Registro agregado.');}catch(Throwable $e){Flash::error($e->getMessage());}redirect('catalogs#'.$table);}
}

final class ReportController extends Controller {
    public function __construct(){Auth::requireLogin();}
    public function inventory():void{$this->view('report',['title'=>'Reporte de inventario','rows'=>(new Asset())->export()]);}
    public function csv():never{$rows=(new Asset())->export();header('Content-Type: text/csv; charset=UTF-8');header('Content-Disposition: attachment; filename="inventario_solandra_'.date('Ymd_His').'.csv"');$o=fopen('php://output','w');fwrite($o,"\xEF\xBB\xBF");if($rows){fputcsv($o,array_keys($rows[0]),';');foreach($rows as $r)fputcsv($o,$r,';');}fclose($o);exit;}
}

final class QrController extends Controller {
    public function show(string $id):void{Auth::requireLogin();$a=(new Asset())->find((int)$id);if(!$a)abort(404);if(!class_exists('chillerlan\\QRCode\\QRCode')){header('Content-Type: image/svg+xml');echo '<svg xmlns="http://www.w3.org/2000/svg" width="220" height="220"><rect width="100%" height="100%" fill="white"/><rect x="10" y="10" width="200" height="200" fill="none" stroke="#0f172a" stroke-width="4"/><text x="110" y="105" text-anchor="middle" font-family="Arial" font-size="15">QR pendiente</text><text x="110" y="130" text-anchor="middle" font-family="Arial" font-size="13">'.e($a['asset_code']).'</text></svg>';return;}$out=(new \chillerlan\QRCode\QRCode())->render(url('assets/'.$a['id']));if(str_starts_with($out,'data:')){[$meta,$data]=explode(',',$out,2);$mime=str_contains($meta,'svg')?'image/svg+xml':'image/png';header('Content-Type: '.$mime);echo str_contains($meta,'base64')?base64_decode($data):urldecode($data);return;}header('Content-Type: image/svg+xml');echo $out;}
}
