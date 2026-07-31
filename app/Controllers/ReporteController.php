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

    public function inventory(): void
    {
        $this->view('reporte', [
            'title' => 'Reporte de inventario',
            'rows' => (new Activo())->export(),
        ]);
    }

    public function csv(): never
    {
        $rows = (new Activo())->export();

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