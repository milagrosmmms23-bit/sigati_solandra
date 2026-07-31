<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller, Csrf, Flash, View};
use App\Models\{Asignacion, Catalogo, DevolucionActivo};
use Throwable;

final class DevolucionController extends Controller
{
    private DevolucionActivo $model;

    public function __construct()
    {
        Auth::requireLogin();
        $this->model = new DevolucionActivo();
    }

    public function listado(): void
    {
        $this->view('devoluciones', [
            'mode' => 'listado',
            'title' => 'Devoluciones',
            'rows' => $this->model->listar(),
        ]);
    }

    public function crear(): void
    {
        $asignacionId = (int) ($_GET['assignment_id'] ?? 0);
        $asignacion = $asignacionId ? (new Asignacion())->buscar($asignacionId) : null;

        $this->view('devoluciones', [
            'mode' => 'formulario',
            'title' => 'Nueva devolución',
            'asignaciones' => (new Asignacion())->activas(),
            'assignment' => $asignacion,
            'statuses' => (new Catalogo())->listar('estados_activo'),
        ]);
    }

    public function guardar(): void
    {
        Csrf::verify();

        $asignacionId = (int) ($_POST['assignment_id'] ?? 0);
        $itemIds = array_values(array_unique(array_map('intval', $_POST['item_ids'] ?? [])));

        if (!$asignacionId || !$itemIds) {
            Flash::error('Selecciona al menos un equipo.');
            redirect('devoluciones/crear?assignment_id='.$asignacionId);
        }

        $items = [];
        foreach ($itemIds as $itemId) {
            $items[] = [
                'item_id' => $itemId,
                'condition' => trim($_POST['condition'][$itemId] ?? 'Buen estado'),
                'damage' => trim($_POST['damage'][$itemId] ?? ''),
                'status_id' => (int) ($_POST['status_id'][$itemId] ?? 0),
            ];
        }

        try {
            $id = $this->model->crear(
                $asignacionId,
                trim($_POST['notes'] ?? ''),
                $items,
                Auth::id()
            );

            Flash::success('Devolución registrada.');
            redirect('devoluciones/'.$id);
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('devoluciones/crear?assignment_id='.$asignacionId);
        }
    }

    public function ver(string $id): void
    {
        $devolucion = $this->model->buscar((int) $id);

        if (!$devolucion) {
            abort(404);
        }

        $this->view('devoluciones', [
            'mode' => 'detalle',
            'title' => $devolucion['return_number'],
            'item' => $devolucion,
        ]);
    }

    public function imprimir(string $id): void
    {
        $devolucion = $this->model->buscar((int) $id);

        if (!$devolucion) {
            abort(404);
        }

        $this->view(
            'imprimir',
            ['doc' => 'return', 'title' => $devolucion['return_number'], 'item' => $devolucion],
            'plantilla_impresion'
        );
    }

    public function pdf(string $id): void
    {
        $devolucion = $this->model->buscar((int) $id);

        if (!$devolucion) {
            abort(404);
        }

        if (!class_exists('Dompdf\\Dompdf')) {
            redirect('devoluciones/'.$id.'/imprimir');
        }

        $html = View::capture('imprimir', [
            'doc' => 'return',
            'item' => $devolucion,
            'pdf' => true,
        ]);

        $pdf = new \Dompdf\Dompdf();
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        $pdf->stream($devolucion['return_number'].'.pdf', ['Attachment' => true]);
    }
}