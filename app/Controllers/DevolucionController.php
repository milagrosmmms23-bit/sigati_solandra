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

    public function index(): void
    {
        $this->view('devoluciones', [
            'mode' => 'index',
            'title' => 'Devoluciones',
            'rows' => $this->model->all(),
        ]);
    }

    public function create(): void
    {
        $asignacionId = (int) ($_GET['assignment_id'] ?? 0);
        $asignacion = $asignacionId ? (new Asignacion())->find($asignacionId) : null;

        $this->view('devoluciones', [
            'mode' => 'form',
            'title' => 'Nueva devolución',
            'asignaciones' => (new Asignacion())->active(),
            'assignment' => $asignacion,
            'statuses' => (new Catalogo())->all('estados_activo'),
        ]);
    }

    public function store(): void
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
            $id = $this->model->create(
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

    public function show(string $id): void
    {
        $devolucion = $this->model->find((int) $id);

        if (!$devolucion) {
            abort(404);
        }

        $this->view('devoluciones', [
            'mode' => 'show',
            'title' => $devolucion['return_number'],
            'item' => $devolucion,
        ]);
    }

    public function print(string $id): void
    {
        $devolucion = $this->model->find((int) $id);

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
        $devolucion = $this->model->find((int) $id);

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