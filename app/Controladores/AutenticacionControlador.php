<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador, Csrf, Flash};

final class AutenticacionControlador extends Controlador
{
    public function formularioIngreso(): void
    {
        if (Auth::check()) {
            redirect('');
        }

        $this->view('ingreso', ['title' => 'Iniciar sesión'], 'autenticacion');
    }

    public function ingresar(): void
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

    public function salir(): void
    {
        Csrf::verify();
        Auth::logout();

        Flash::success('Sesión cerrada.');
        redirect('ingreso');
    }
}