<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador, Csrf, DB, Flash};
use App\Modelos\Mantenimiento;
use Throwable;

final class MantenimientoControlador extends Controlador
{
    private Mantenimiento $model;

    public function __construct()
    {
        Auth::requireLogin();
        $this->model = new Mantenimiento();
    }

    public function listado(): void
    {
        $this->view('mantenimientos', [
            'mode' => 'listado',
            'title' => 'Mantenimientos',
            'rows' => $this->model->listar(),
        ]);
    }

    public function crear(): void
    {
        $activos = DB::pdo()
            ->query(
                "SELECT a.id, a.asset_code, a.serial_number, t.name type_name
                 FROM activos a
                 JOIN tipos_activo t ON t.id = a.asset_type_id
                 WHERE a.active = 1
                 ORDER BY a.asset_code"
            )
            ->fetchAll();

        $this->view('mantenimientos', [
            'mode' => 'formulario',
            'title' => 'Nuevo mantenimiento',
            'activos' => $activos,
        ]);
    }

    public function guardar(): void
    {
        Csrf::verify();

        $data = [
            'asset_id' => (int) ($_POST['asset_id'] ?? 0),
            'type' => $_POST['type'] ?? 'PREVENTIVO',
            'issue' => trim($_POST['issue'] ?? ''),
            'diagnosis' => trim($_POST['diagnosis'] ?? ''),
            'actions' => trim($_POST['actions'] ?? ''),
            'cost' => trim($_POST['cost'] ?? '0'),
        ];

        try {
            $this->model->abrir($data, Auth::id());
            Flash::success('Mantenimiento abierto.');
            redirect('mantenimientos');
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('mantenimientos/crear');
        }
    }

    public function cerrar(string $id): void
    {
        Csrf::verify();

        $data = [
            'diagnosis' => trim($_POST['diagnosis'] ?? ''),
            'actions' => trim($_POST['actions'] ?? ''),
            'parts' => trim($_POST['parts'] ?? ''),
            'cost' => trim($_POST['cost'] ?? '0'),
            'next_date' => trim($_POST['next_date'] ?? ''),
        ];

        try {
            $this->model->cerrar((int) $id, $data, Auth::id());
            Flash::success('Mantenimiento cerrado.');
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
        }

        redirect('mantenimientos');
    }
}