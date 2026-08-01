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
                "SELECT a.id, a.codigo_activo, a.numero_serie, t.nombre nombre_tipo
                 FROM activos a
                 JOIN tipos_activo t ON t.id = a.tipo_activo_id
                 WHERE a.activo = 1
                 ORDER BY a.codigo_activo"
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
            'activo_id' => (int) ($_POST['activo_id'] ?? 0),
            'tipo' => $_POST['tipo'] ?? 'PREVENTIVO',
            'problema' => trim($_POST['problema'] ?? ''),
            'diagnostico' => trim($_POST['diagnostico'] ?? ''),
            'acciones' => trim($_POST['acciones'] ?? ''),
            'costo' => trim($_POST['costo'] ?? '0'),
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
            'diagnostico' => trim($_POST['diagnostico'] ?? ''),
            'acciones' => trim($_POST['acciones'] ?? ''),
            'repuestos' => trim($_POST['repuestos'] ?? ''),
            'costo' => trim($_POST['costo'] ?? '0'),
            'proxima_fecha' => trim($_POST['proxima_fecha'] ?? ''),
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