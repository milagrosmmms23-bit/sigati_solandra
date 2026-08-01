<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador, Csrf, Flash};

final class AutenticacionControlador extends Controlador
{
    public function formularioIngreso(): void
    {
        if (Auth::autenticado()) {
            redirect('');
        }

        $this->vista('ingreso', ['titulo' => 'Iniciar sesión'], 'autenticacion');
    }

    public function ingresar(): void
    {
        Csrf::verificar();

        $usuario = trim($_POST['usuario'] ?? '');
        $clave = (string) ($_POST['clave'] ?? '');

        if (Auth::intentar($usuario, $clave)) {
            Flash::exito('Bienvenido a SIGATI SOLANDRA.');
            redirect('');
        }

        Flash::error('Usuario o contraseña incorrectos.');
        redirect('ingreso');
    }

    public function salir(): void
    {
        Csrf::verificar();
        Auth::cerrarSesion();

        Flash::exito('Sesión cerrada.');
        redirect('ingreso');
    }
}