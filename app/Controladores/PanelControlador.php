<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador};
use App\Modelos\Panel;

final class PanelControlador extends Controlador
{
    public function inicio(): void
    {
        Auth::requerirIngreso();

        $datos = array_merge(
            ['titulo' => 'Panel'],
            (new Panel())->datos()
        );

        $this->vista('panel', $datos);
    }
}