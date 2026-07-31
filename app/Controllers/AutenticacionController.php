<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller, Csrf, Flash};

final class AutenticacionController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('');
        }

        $this->view('ingreso', ['title' => 'Iniciar sesión'], 'autenticacion');
    }

    public function login(): void
    {
        Csrf::verify();

        $usuario = trim($_POST['username'] ?? '');
        $clave = (string) ($_POST['password'] ?? '');

        if (Auth::attempt($usuario, $clave)) {
            Flash::success('Bienvenido a SIGATI SOLANDRA.');
            redirect('');
        }

        Flash::error('Usuario o contraseña incorrectos.');
        redirect('ingreso');
    }

    public function logout(): void
    {
        Csrf::verify();
        Auth::logout();

        Flash::success('Sesión cerrada.');
        redirect('ingreso');
    }
}