<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash, View};
use App\Models\{Activo, DevolucionActivo, Asignacion, Catalogo, Panel, Trabajador, Mantenimiento};
use Throwable;

final class CodigoQrController extends Controller {
    public function show(string $id):void{Auth::requireLogin();$a=(new Activo())->find((int)$id);if(!$a)abort(404);if(!class_exists('chillerlan\\QRCode\\QRCode')){header('Content-Type: image/svg+xml');echo '<svg xmlns="http://www.w3.org/2000/svg" width="220" height="220"><rect width="100%" height="100%" fill="white"/><rect x="10" y="10" width="200" height="200" fill="none" stroke="#0f172a" stroke-width="4"/><text x="110" y="105" text-anchor="middle" font-family="Arial" font-size="15">QR pendiente</text><text x="110" y="130" text-anchor="middle" font-family="Arial" font-size="13">'.e($a['asset_code']).'</text></svg>';return;}$out=(new \chillerlan\QRCode\QRCode())->render(url('activos/'.$a['id']));if(str_starts_with($out,'data:')){[$meta,$data]=explode(',',$out,2);$mime=str_contains($meta,'svg')?'image/svg+xml':'image/png';header('Content-Type: '.$mime);echo str_contains($meta,'base64')?base64_decode($data):urldecode($data);return;}header('Content-Type: image/svg+xml');echo $out;}
}
