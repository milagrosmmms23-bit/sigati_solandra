<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Controller};
use App\Models\Panel;

final class PanelController extends Controller
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