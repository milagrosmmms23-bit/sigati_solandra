<?php
use App\Core\Config;
use App\Core\Csrf;

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
