<?php
namespace App\Core;

use PDO;
use Throwable;

final class Config {
    private static array $data=[];
    public static function get(string $key,mixed $default=null):mixed{
        [$file,$item]=array_pad(explode('.',$key,2),2,null);
        if(!isset(self::$data[$file])) self::$data[$file]=require dirname(__DIR__,2).'/config/'.$file.'.php';
        return $item===null?self::$data[$file]:(self::$data[$file][$item]??$default);
    }
}

final class DB {
    private static ?PDO $pdo=null;
    public static function pdo():PDO{
        if(self::$pdo)return self::$pdo;
        $c=Config::get('database');
        $dsn="mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset={$c['charset']}";
        self::$pdo=new PDO($dsn,$c['username'],$c['password'],[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
        ]);
        return self::$pdo;
    }
}

final class Flash {
    public static function add(string $type,string $message):void{$_SESSION['_flash'][]=compact('type','message');}
    public static function success(string $m):void{self::add('success',$m);} public static function error(string $m):void{self::add('danger',$m);} public static function warning(string $m):void{self::add('warning',$m);}
    public static function take():array{$x=$_SESSION['_flash']??[];unset($_SESSION['_flash']);return $x;}
}

final class Csrf {
    public static function token():string{return $_SESSION['_token']??=bin2hex(random_bytes(32));}
    public static function verify():void{if(!isset($_POST['_token'])||!hash_equals(self::token(),(string)$_POST['_token'])) abort(419,'La sesión del formulario expiró.');}
}

final class Auth {
    public static function attempt(string $username,string $password):bool{
        $s=DB::pdo()->prepare('SELECT u.*,r.name role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.username=? AND u.active=1 LIMIT 1');$s->execute([$username]);$u=$s->fetch();
        if(!$u||!password_verify($password,$u['password_hash']))return false;
        unset($u['password_hash']);session_regenerate_id(true);$_SESSION['user']=$u;DB::pdo()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([$u['id']]);return true;
    }
    public static function check():bool{return isset($_SESSION['user']);}
    public static function user():?array{return $_SESSION['user']??null;}
    public static function id():?int{return isset($_SESSION['user']['id'])?(int)$_SESSION['user']['id']:null;}
    public static function role():?string{return $_SESSION['user']['role_name']??null;}
    public static function requireLogin():void{if(!self::check()){Flash::warning('Inicia sesión para continuar.');redirect('login');}}
    public static function requireRole(array $roles):void{self::requireLogin();if(!in_array(self::role(),$roles,true))abort(403,'No tienes permiso.');}
    public static function logout():void{unset($_SESSION['user']);session_regenerate_id(true);}
}

final class Audit {
    public static function log(string $module,string $action,string $entity,?int $id,mixed $old=null,mixed $new=null):void{
        try{$s=DB::pdo()->prepare('INSERT INTO audit_logs(user_id,module,action,entity_type,entity_id,old_values,new_values,ip_address,user_agent,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())');$s->execute([Auth::id(),$module,$action,$entity,$id,$old?json_encode($old,JSON_UNESCAPED_UNICODE):null,$new?json_encode($new,JSON_UNESCAPED_UNICODE):null,$_SERVER['REMOTE_ADDR']??null,substr($_SERVER['HTTP_USER_AGENT']??'',0,500)]);}catch(Throwable){}
    }
}

final class View {
    public static function render(string $view,array $data=[],string $layout='layout'):void{
        extract($data,EXTR_SKIP);$file=dirname(__DIR__).'/Views/'.$view.'.php';if(!is_file($file))abort(500,'Vista no encontrada: '.$view);
        ob_start();require $file;$content=ob_get_clean();
        if($layout===''){echo $content;return;}require dirname(__DIR__).'/Views/'.$layout.'.php';
    }
    public static function capture(string $view,array $data=[]):string{extract($data,EXTR_SKIP);ob_start();require dirname(__DIR__).'/Views/'.$view.'.php';return (string)ob_get_clean();}
}

abstract class Controller {
    protected function view(string $v,array $d=[],string $l='layout'):void{View::render($v,$d,$l);}
    protected function validate(array $d,array $rules):array{$e=[];foreach($rules as $f=>$rs){$v=trim((string)($d[$f]??''));foreach(explode('|',$rs) as $r){if($r==='required'&&$v==='')$e[$f]='Campo obligatorio.';if($r==='email'&&$v!==''&&!filter_var($v,FILTER_VALIDATE_EMAIL))$e[$f]='Correo inválido.';if(str_starts_with($r,'max:')&&mb_strlen($v)>(int)substr($r,4))$e[$f]='Longitud máxima excedida.';}}return $e;}
    protected function errors(array $e,array $old,string $path):never{$_SESSION['_errors']=$e;$_SESSION['_old']=$old;redirect($path);}
}

final class Router {
    private array $routes=[];
    public function get(string $p,array $h):void{$this->routes[]=['GET',$p,$h];} public function post(string $p,array $h):void{$this->routes[]=['POST',$p,$h];}
    public function dispatch():void{
        $method=$_SERVER['REQUEST_METHOD'];$uri=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)?:'/';$base=rtrim((string)Config::get('app.base_url'),'/');if($base!==''&&str_starts_with($uri,$base))$uri=substr($uri,strlen($base));$uri='/'.trim($uri,'/');if($uri==='//')$uri='/';
        foreach($this->routes as [$m,$p,$h]){if($m!==$method)continue;$pattern='#^'.preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#','([^/]+)',rtrim($p,'/')).'/?$#';if(preg_match($pattern,$uri,$match)){array_shift($match);[$class,$action]=$h;(new $class())->$action(...$match);return;}}
        abort(404,'Ruta no encontrada.');
    }
}

function config(string $key,mixed $default=null):mixed{return Config::get($key,$default);} 
function url(string $path=''):string{$base=rtrim((string)config('app.base_url'),'/');return $base.($path!==''?'/'.ltrim($path,'/'):'');}
function asset(string $path):string{return url('assets/'.ltrim($path,'/'));}
function e(mixed $v):string{return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function old(string $k,mixed $d=''):mixed{return $_SESSION['_old'][$k]??$d;}
function selected(mixed $a,mixed $b):string{return (string)$a===(string)$b?'selected':'';}
function csrf_field():string{return '<input type="hidden" name="_token" value="'.e(Csrf::token()).'">';}
function redirect(string $path):never{header('Location: '.url($path));exit;}
function abort(int $code,string $message=''):never{http_response_code($code);echo '<h1>Error '.$code.'</h1><p>'.e($message?:'No se pudo completar la solicitud.').'</p>';exit;}
function date_pe(?string $d):string{return $d?date('d/m/Y',strtotime($d)):'—';}
function datetime_pe(?string $d):string{return $d?date('d/m/Y H:i',strtotime($d)):'—';}
function money(mixed $v):string{return $v===''||$v===null?'—':'S/ '.number_format((float)$v,2);}
function badge(string $s):string{$map=['DISPONIBLE'=>'success','ASIGNADO'=>'primary','MANTENIMIENTO'=>'warning','REPARACION'=>'danger','CONFIRMADA'=>'success','PARCIAL'=>'warning','CERRADA'=>'dark','ABIERTO'=>'warning','CERRADO'=>'success'];$c=$map[strtoupper($s)]??'secondary';return '<span class="badge badge-'.$c.'">'.e($s).'</span>';}
