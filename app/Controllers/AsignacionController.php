<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller, Csrf, Flash, View};
use App\Models\{Activo, Asignacion, Trabajador};
use Throwable;

final class AsignacionController extends Controller
{
    private Asignacion $model;

    public function __construct()
    {
        Auth::requireLogin();
        $this->model = new Asignacion();
    }

    public function listado(): void
    {
        $this->view('asignaciones', [
            'mode' => 'listado',
            'title' => 'Asignaciones',
            'rows' => $this->model->listar(),
        ]);
    }

    public function crear(): void
    {
        $this->view('asignaciones', [
            'mode' => 'formulario',
            'title' => 'Nueva asignación',
            'trabajadores' => (new Trabajador())->listar(),
            'activos' => (new Activo())->disponibles(),
        ]);
    }

    public function guardar(): void
    {
        Csrf::verify();

        $trabajadorId = (int) ($_POST['employee_id'] ?? 0);
        $activosIds = array_values(array_unique(array_map('intval', $_POST['asset_ids'] ?? [])));

        if (!$trabajadorId || !$activosIds) {
            Flash::error('Selecciona un trabajador y al menos un activo.');
            redirect('asignaciones/crear');
        }

        $items = [];
        foreach ($activosIds as $activoId) {
            $items[] = [
                'asset_id' => $activoId,
                'condition' => trim($_POST['condition'][$activoId] ?? 'Buen estado'),
            ];
        }

        try {
            $id = $this->model->crear(
                $trabajadorId,
                (int) ($_POST['area_id'] ?? 0) ?: null,
                trim($_POST['notes'] ?? ''),
                $items,
                Auth::id()
            );

            Flash::success('Asignación confirmada.');
            redirect('asignaciones/'.$id);
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('asignaciones/crear');
        }
    }

    public function ver(string $id): void
    {
        $asignacion = $this->model->buscar((int) $id);

        if (!$asignacion) {
            abort(404);
        }

        $this->view('asignaciones', [
            'mode' => 'detalle',
            'title' => $asignacion['assignment_number'],
            'item' => $asignacion,
        ]);
    }

    public function imprimir(string $id): void
    {
        $asignacion = $this->model->buscar((int) $id);

        if (!$asignacion) {
            abort(404);
        }

        $this->view(
            'imprimir',
            ['doc' => 'assignment', 'title' => $asignacion['assignment_number'], 'item' => $asignacion],
            'plantilla_impresion'
        );
    }

    public function pdf(string $id): void
    {
        $asignacion = $this->model->buscar((int) $id);

        if (!$asignacion) {
            abort(404);
        }

        if (!class_exists('Dompdf\\Dompdf')) {
            redirect('asignaciones/'.$id.'/imprimir');
        }

        $html = View::capture('imprimir', [
            'doc' => 'assignment',
            'item' => $asignacion,
            'pdf' => true,
        ]);

        $pdf = new \Dompdf\Dompdf();
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        $pdf->stream($asignacion['assignment_number'].'.pdf', ['Attachment' => true]);
    }
}