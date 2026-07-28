<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Asset, AssetReturn, Assignment, Catalog, Dashboard, Employee, Maintenance};
use Throwable;

final class AuthController extends Controller {
    public function loginForm():void{if(Auth::check())redirect('');$this->view('login',['title'=>'Iniciar sesión'],'auth');}
    public function login():void{Csrf::verify();if(Auth::attempt(trim($_POST['username']??''),(string)($_POST['password']??''))){Flash::success('Bienvenido a SIGATI SOLANDRA.');redirect('');}Flash::error('Usuario o contraseña incorrectos.');redirect('login');}
    public function logout():void{Csrf::verify();Auth::logout();Flash::success('Sesión cerrada.');redirect('login');}
}
