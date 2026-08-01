<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador, Csrf, Flash};
use App\Modelos\Catalogo;
use Throwable;

final class CatalogoControlador extends Controlador
{
    private Catalogo $modelo;

    public function __construct()
    {
        Auth::requerirRol(['ADMIN']);
        $this->modelo = new Catalogo();
    }

    public function listado(): void
    {
        $filas = [];

        foreach ($this->modelo->allowed as $tabla => $label) {
            $filas[$tabla] = $this->modelo->listar($tabla);
        }

        $this->vista('catalogos', [
            'titulo' => 'Catálogos',
            'filas' => $filas,
            'labels' => $this->modelo->allowed,
        ]);
    }

    public function guardar(string $tabla): void
    {
        Csrf::verificar();

        try {
            $this->modelo->crear($tabla, $_POST);
            Flash::exito('Registro agregado.');
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
        }

        redirect('catalogos#'.$tabla);
    }
}