<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, Flash};
use App\Models\{Catalogo, Trabajador};
use Throwable;

final class TrabajadorController extends Controller
{
    private Trabajador $model;
    private Catalogo $catalogo;

    public function __construct()
    {
        Auth::requireLogin();
        $this->model = new Trabajador();
        $this->catalogo = new Catalogo();
    }

    public function listado(): void
    {
        $q = trim($_GET['q'] ?? '');

        $this->view('trabajadores', [
            'mode' => 'listado',
            'title' => 'Trabajadores',
            'rows' => $this->model->listar($q),
            'q' => $q,
        ]);
    }

    public function crear(): void
    {
        $this->view('trabajadores', [
            'mode' => 'formulario',
            'title' => 'Nuevo trabajador',
            'item' => null,
            'areas' => $this->catalogo->listar('areas'),
        ]);
    }

    public function guardar(): void
    {
        Csrf::verify();

        $data = $this->payload();
        $errors = $this->validate($data, [
            'employee_code' => 'required|max:50',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'email',
        ]);

        if ($errors) {
            $this->errors($errors, $_POST, 'trabajadores/crear');
        }

        try {
            $id = $this->model->guardar($data);
            Audit::log('Trabajadores', 'CREAR', 'trabajador', $id, null, $data);
            Flash::success('Trabajador registrado.');
            redirect('trabajadores');
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('trabajadores/crear');
        }
    }

    public function editar(string $id): void
    {
        $trabajador = $this->model->buscar((int) $id);

        if (!$trabajador) {
            abort(404);
        }

        $this->view('trabajadores', [
            'mode' => 'formulario',
            'title' => 'Editar trabajador',
            'item' => $trabajador,
            'areas' => $this->catalogo->listar('areas'),
        ]);
    }

    public function actualizar(string $id): void
    {
        Csrf::verify();

        $anterior = $this->model->buscar((int) $id);

        if (!$anterior) {
            abort(404);
        }

        $data = $this->payload();
        $errors = $this->validate($data, [
            'employee_code' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'email',
        ]);

        if ($errors) {
            $this->errors($errors, $_POST, 'trabajadores/'.$id.'/editar');
        }

        $this->model->guardar($data, (int) $id);
        Audit::log('Trabajadores', 'ACTUALIZAR', 'trabajador', (int) $id, $anterior, $data);
        Flash::success('Trabajador actualizado.');
        redirect('trabajadores');
    }

    private function payload(): array
    {
        return [
            'employee_code' => trim($_POST['employee_code'] ?? ''),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'area_id' => (int) ($_POST['area_id'] ?? 0),
        ];
    }
}