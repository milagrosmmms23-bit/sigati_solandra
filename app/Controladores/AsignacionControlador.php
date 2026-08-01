<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador, Csrf, Flash, Vista};
use App\Modelos\{Activo, Asignacion, Trabajador};
use Throwable;

final class AsignacionControlador extends Controlador
{
    private Asignacion $modelo;

    public function __construct()
    {
        Auth::requerirIngreso();
        $this->modelo = new Asignacion();
    }

    public function listado(): void
    {
        $this->vista('asignaciones', [
            'modo' => 'listado',
            'titulo' => 'Asignaciones',
            'filas' => $this->modelo->listar(),
        ]);
    }

    public function crear(): void
    {
        $this->vista('asignaciones', [
            'modo' => 'formulario',
            'titulo' => 'Nueva asignación',
            'trabajadores' => (new Trabajador())->listar(),
            'activos' => (new Activo())->disponibles(),
        ]);
    }

    public function guardar(): void
    {
        Csrf::verificar();

        $trabajadorId = (int) ($_POST['trabajador_id'] ?? 0);
        $activosIds = array_values(array_unique(array_map('intval', $_POST['activo_ids'] ?? [])));

        if (!$trabajadorId || !$activosIds) {
            Flash::error('Selecciona un trabajador y al menos un activo.');
            redirect('asignaciones/crear');
        }

        $elementos = [];
        foreach ($activosIds as $activoId) {
            $elementos[] = [
                'activo_id' => $activoId,
                'condicion' => trim($_POST['condicion'][$activoId] ?? 'Buen estado'),
            ];
        }

        try {
            $id = $this->modelo->crear(
                $trabajadorId,
                (int) ($_POST['area_id'] ?? 0) ?: null,
                trim($_POST['observaciones'] ?? ''),
                $elementos,
                Auth::id()
            );

            Flash::exito('Asignación confirmada.');
            redirect('asignaciones/'.$id);
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('asignaciones/crear');
        }
    }

    public function ver(string $id): void
    {
        $asignacion = $this->modelo->buscar((int) $id);

        if (!$asignacion) {
            abort(404);
        }

        $this->vista('asignaciones', [
            'modo' => 'detalle',
            'titulo' => $asignacion['numero_asignacion'],
            'registro' => $asignacion,
        ]);
    }

    public function imprimir(string $id): void
    {
        $asignacion = $this->modelo->buscar((int) $id);

        if (!$asignacion) {
            abort(404);
        }

        $this->vista(
            'imprimir',
            ['doc' => 'assignment', 'titulo' => $asignacion['numero_asignacion'], 'registro' => $asignacion],
            'plantilla_impresion'
        );
    }

    public function pdf(string $id): void
    {
        $asignacion = $this->modelo->buscar((int) $id);

        if (!$asignacion) {
            abort(404);
        }

        if (!class_exists('Dompdf\\Dompdf')) {
            redirect('asignaciones/'.$id.'/imprimir');
        }

        $html = Vista::capturar('imprimir', [
            'doc' => 'assignment',
            'registro' => $asignacion,
            'pdf' => true,
        ]);

        $pdf = new \Dompdf\Dompdf();
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        $pdf->stream($asignacion['numero_asignacion'].'.pdf', ['Attachment' => true]);
    }
}