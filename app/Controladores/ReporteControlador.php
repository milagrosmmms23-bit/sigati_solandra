<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador};
use App\Modelos\Activo;

final class ReporteControlador extends Controlador
{
    public function __construct()
    {
        Auth::requerirIngreso();
    }

    public function inventario(): void
    {
        $this->vista('reporte', [
            'titulo' => 'Reporte de inventario',
            'filas' => (new Activo())->exportar(),
        ]);
    }

    public function exportarCsv(): never
    {
        $filas = (new Activo())->exportar();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="inventario_solandra_'.date('Ymd_His').'.csv"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        if ($filas) {
            fputcsv($output, array_keys($filas[0]), ';');

            foreach ($filas as $fila) {
                fputcsv($output, $fila, ';');
            }
        }

        fclose($output);
        exit;
    }

    public function exportarExcel(): never
    {
        $filas = (new Activo())->exportar();

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="inventario_solandra_'.date('Ymd_His').'.xls"');

        echo "\xEF\xBB\xBF";
        echo '<table border="1">';

        if ($filas) {
            echo '<thead><tr>';

            foreach (array_keys($filas[0]) as $encabezado) {
                echo '<th>'.e(ucwords(str_replace('_', ' ', $encabezado))).'</th>';
            }

            echo '</tr></thead><tbody>';

            foreach ($filas as $fila) {
                echo '<tr>';

                foreach ($fila as $valor) {
                    echo '<td>'.e($valor ?? '').'</td>';
                }

                echo '</tr>';
            }

            echo '</tbody>';
        }

        echo '</table>';
        exit;
    }
}
