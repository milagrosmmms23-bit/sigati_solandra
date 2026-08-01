<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador, Csrf, Flash, Vista};
use App\Modelos\{Asignacion, Catalogo, DevolucionActivo};
use Throwable;

final class DevolucionControlador extends Controlador
{
    private DevolucionActivo $modelo;

    public function __construct()
    {
        Auth::requerirIngreso();
        $this->modelo = new DevolucionActivo();
    }

    public function listado(): void
    {
        $this->vista('devoluciones', [
            'modo' => 'listado',
            'titulo' => 'Devoluciones',
            'filas' => $this->modelo->listar(),
        ]);
    }

    public function crear(): void
    {
        $asignacionId = (int) ($_GET['asignacion_id'] ?? 0);
        $asignacion = $asignacionId ? (new Asignacion())->buscar($asignacionId) : null;

        $this->vista('devoluciones', [
            'modo' => 'formulario',
            'titulo' => 'Nueva devolución',
            'asignaciones' => (new Asignacion())->activas(),
            'asignacion' => $asignacion,
            'estados' => (new Catalogo())->listar('estados_activo'),
        ]);
    }

    public function guardar(): void
    {
        Csrf::verificar();

        $asignacionId = (int) ($_POST['asignacion_id'] ?? 0);
        $elementosIds = array_values(array_unique(array_map('intval', $_POST['item_asignacion_ids'] ?? [])));

        if (!$asignacionId || !$elementosIds) {
            Flash::error('Selecciona al menos un equipo.');
            redirect('devoluciones/crear?asignacion_id='.$asignacionId);
        }

        $elementos = [];
        foreach ($elementosIds as $itemId) {
            $elementos[] = [
                'item_asignacion_id' => $itemId,
                'condicion' => trim($_POST['condicion'][$itemId] ?? 'Buen estado'),
                'danos' => trim($_POST['danos'][$itemId] ?? ''),
                'estado_id' => (int) ($_POST['estado_id'][$itemId] ?? 0),
            ];
        }

        try {
            $id = $this->modelo->crear(
                $asignacionId,
                trim($_POST['observaciones'] ?? ''),
                $elementos,
                Auth::id()
            );

            Flash::exito('Devolución registrada.');
            redirect('devoluciones/'.$id);
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('devoluciones/crear?asignacion_id='.$asignacionId);
        }
    }

    public function ver(string $id): void
    {
        $devolucion = $this->modelo->buscar((int) $id);

        if (!$devolucion) {
            abort(404);
        }

        $this->vista('devoluciones', [
            'modo' => 'detalle',
            'titulo' => $devolucion['numero_devolucion'],
            'registro' => $devolucion,
        ]);
    }

    public function imprimir(string $id): void
    {
        $devolucion = $this->modelo->buscar((int) $id);

        if (!$devolucion) {
            abort(404);
        }

        $this->vista(
            'imprimir',
            ['doc' => 'return', 'titulo' => $devolucion['numero_devolucion'], 'registro' => $devolucion],
            'plantilla_impresion'
        );
    }

    public function pdf(string $id): void
    {
        $devolucion = $this->modelo->buscar((int) $id);

        if (!$devolucion) {
            abort(404);
        }

        if (!class_exists('Dompdf\\Dompdf')) {
            redirect('devoluciones/'.$id.'/imprimir');
        }

        $html = Vista::capturar('imprimir', [
            'doc' => 'return',
            'registro' => $devolucion,
            'pdf' => true,
        ]);

        $pdf = new \Dompdf\Dompdf();
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        $pdf->stream($devolucion['numero_devolucion'].'.pdf', ['Attachment' => true]);
    }
}