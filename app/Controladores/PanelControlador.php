<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Controlador};
use App\Modelos\Panel;

final class PanelControlador extends Controlador
{
    public function inicio(): void
    {
        Auth::requireLogin();

        $datos = array_merge(
            ['title' => 'Panel'],
            (new Panel())->datos()
        );

        $this->view('panel', $datos);
    }
}