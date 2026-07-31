<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Activo;

final class CodigoQrController
{
    public function show(string $id): void
    {
        Auth::requireLogin();

        $activo = (new Activo())->find((int) $id);

        if (!$activo) {
            abort(404);
        }

        if (!class_exists('chillerlan\\QRCode\\QRCode')) {
            $this->renderFallbackSvg($activo['asset_code']);
            return;
        }

        $qr = (new \chillerlan\QRCode\QRCode())->render(url('activos/'.$activo['id']));

        if (str_starts_with($qr, 'data:')) {
            $this->renderDataUri($qr);
            return;
        }

        header('Content-Type: image/svg+xml');
        echo $qr;
    }

    private function renderFallbackSvg(string $codigo): void
    {
        header('Content-Type: image/svg+xml');
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="220" height="220">';
        echo '<rect width="100%" height="100%" fill="white"/>';
        echo '<rect x="10" y="10" width="200" height="200" fill="none" stroke="#0f172a" stroke-width="4"/>';
        echo '<text x="110" y="105" text-anchor="middle" font-family="Arial" font-size="15">QR pendiente</text>';
        echo '<text x="110" y="130" text-anchor="middle" font-family="Arial" font-size="13">'.e($codigo).'</text>';
        echo '</svg>';
    }

    private function renderDataUri(string $dataUri): void
    {
        [$meta, $data] = explode(',', $dataUri, 2);
        $mime = str_contains($meta, 'svg') ? 'image/svg+xml' : 'image/png';

        header('Content-Type: '.$mime);
        echo str_contains($meta, 'base64') ? base64_decode($data) : urldecode($data);
    }
}