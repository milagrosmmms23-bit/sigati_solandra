<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller, Csrf, Flash};
use App\Models\Catalogo;
use Throwable;

final class CatalogoController extends Controller
{
    private Catalogo $model;

    public function __construct()
    {
        Auth::requireRole(['ADMIN']);
        $this->model = new Catalogo();
    }

    public function index(): void
    {
        $rows = [];

        foreach ($this->model->allowed as $table => $label) {
            $rows[$table] = $this->model->all($table);
        }

        $this->view('catalogos', [
            'title' => 'Catálogos',
            'rows' => $rows,
            'labels' => $this->model->allowed,
        ]);
    }

    public function store(string $table): void
    {
        Csrf::verify();

        try {
            $this->model->create($table, $_POST);
            Flash::success('Registro agregado.');
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
        }

        redirect('catalogos#'.$table);
    }
}