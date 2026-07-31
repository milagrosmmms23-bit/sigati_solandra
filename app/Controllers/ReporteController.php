<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller};
use App\Models\Activo;

final class ReporteController extends Controller
{
    public function __construct()
    {
        Auth::requireLogin();
    }

    public function inventario(): void
    {
        $this->view('reporte', [
            'title' => 'Reporte de inventario',
            'rows' => (new Activo())->exportar(),
        ]);
    }

    public function exportarCsv(): never
    {
        $rows = (new Activo())->exportar();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="inventario_solandra_'.date('Ymd_His').'.csv"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        if ($rows) {
            fputcsv($output, array_keys($rows[0]), ';');

            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }
        }

        fclose($output);
        exit;
    }
}