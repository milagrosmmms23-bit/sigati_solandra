<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador, Csrf, BD, Flash};
use App\Modelos\Mantenimiento;
use Throwable;

final class MantenimientoControlador extends Controlador
{
    private Mantenimiento $modelo;

    public function __construct()
    {
        Auth::requerirIngreso();
        $this->modelo = new Mantenimiento();
    }

    public function listado(): void
    {
        $this->vista('mantenimientos', [
            'modo' => 'listado',
            'titulo' => 'Mantenimientos',
            'filas' => $this->modelo->listar(),
        ]);
    }

    public function crear(): void
    {
        $activos = BD::pdo()
            ->query(
                "SELECT a.id, a.asset_code, a.serial_number, t.name type_name
                 FROM activos a
                 JOIN tipos_activo t ON t.id = a.asset_type_id
                 WHERE a.active = 1
                 ORDER BY a.asset_code"
            )
            ->fetchAll();

        $this->vista('mantenimientos', [
            'modo' => 'formulario',
            'titulo' => 'Nuevo mantenimiento',
            'activos' => $activos,
        ]);
    }

    public function guardar(): void
    {
        Csrf::verificar();

        $datos = [
            'asset_id' => (int) ($_POST['asset_id'] ?? 0),
            'tipo' => $_POST['type'] ?? 'PREVENTIVO',
            'problema' => trim($_POST['issue'] ?? ''),
            'diagnostico' => trim($_POST['diagnosis'] ?? ''),
            'acciones' => trim($_POST['actions'] ?? ''),
            'costo' => trim($_POST['cost'] ?? '0'),
        ];

        try {
            $this->modelo->abrir($datos, Auth::id());
            Flash::exito('Mantenimiento abierto.');
            redirect('mantenimientos');
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('mantenimientos/crear');
        }
    }

    public function cerrar(string $id): void
    {
        Csrf::verificar();

        $datos = [
            'diagnostico' => trim($_POST['diagnosis'] ?? ''),
            'acciones' => trim($_POST['actions'] ?? ''),
            'repuestos' => trim($_POST['parts'] ?? ''),
            'costo' => trim($_POST['cost'] ?? '0'),
            'proxima_fecha' => trim($_POST['next_date'] ?? ''),
        ];

        try {
            $this->modelo->cerrar((int) $id, $datos, Auth::id());
            Flash::exito('Mantenimiento cerrado.');
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
        }

        redirect('mantenimientos');
    }
}