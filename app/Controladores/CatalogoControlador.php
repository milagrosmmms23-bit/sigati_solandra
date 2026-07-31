<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador, Csrf, Flash};
use App\Modelos\Catalogo;
use Throwable;

final class CatalogoControlador extends Controlador
{
    private Catalogo $model;

    public function __construct()
    {
        Auth::requireRole(['ADMIN']);
        $this->model = new Catalogo();
    }

    public function listado(): void
    {
        $rows = [];

        foreach ($this->model->allowed as $table => $label) {
            $rows[$table] = $this->model->listar($table);
        }

        $this->view('catalogos', [
            'title' => 'Catálogos',
            'rows' => $rows,
            'labels' => $this->model->allowed,
        ]);
    }

    public function guardar(string $table): void
    {
        Csrf::verify();

        try {
            $this->model->crear($table, $_POST);
            Flash::success('Registro agregado.');
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
        }

        redirect('catalogos#'.$table);
    }
}