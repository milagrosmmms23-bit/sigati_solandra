<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Auditoria, Controlador, Csrf, BD, Flash};
use App\Modelos\{Activo, Catalogo};
use Throwable;

final class ActivoControlador extends Controlador
{
    private Activo $modelo;
    private Catalogo $catalogo;

    public function __construct()
    {
        Auth::requerirIngreso();
        $this->modelo = new Activo();
        $this->catalogo = new Catalogo();
    }

    public function listado(): void
    {
        $filtros = [
            'q' => trim($_GET['q'] ?? ''),
            'type_id' => $_GET['type_id'] ?? '',
            'status_id' => $_GET['status_id'] ?? '',
            'area_id' => $_GET['area_id'] ?? '',
        ];

        $pagina = max(1, (int) ($_GET['page'] ?? 1));
        $porPagina = (int) config('app.items_per_page', 15);

        $this->vista('activos', [
            'modo' => 'listado',
            'titulo' => 'Inventario',
            'resultado' => $this->modelo->listar($filtros, $pagina, $porPagina),
            'filtros' => $filtros,
        ] + $this->catalogos());
    }

    public function crear(): void
    {
        $this->vista('activos', [
            'modo' => 'formulario',
            'titulo' => 'Nuevo activo',
            'registro' => null,
        ] + $this->catalogos());
    }

    public function guardar(): void
    {
        Csrf::verificar();

        $datos = $this->datosFormulario();
        $errores = $this->validar($datos, [
            'asset_type_id' => 'required',
            'status_id' => 'required',
            'serial_number' => 'max:150',
        ]);

        if ($errores) {
            $this->enviarErrores($errores, $_POST, 'activos/crear');
        }

        try {
            $id = $this->modelo->guardar($datos, $this->especificaciones(), Auth::id());
            Auditoria::registrar('Inventario', 'CREAR', 'activo', $id, null, $datos);
            Flash::exito('Activo registrado correctamente.');
            redirect('activos/'.$id);
        } catch (Throwable $exception) {
            Flash::error('No se pudo registrar: '.$exception->getMessage());
            $_SESSION['_old'] = $_POST;
            redirect('activos/crear');
        }
    }

    public function ver(string $id): void
    {
        $activo = $this->modelo->buscar((int) $id);

        if (!$activo) {
            abort(404, 'Activo no encontrado.');
        }

        $this->vista('activos', [
            'modo' => 'detalle',
            'titulo' => $activo['asset_code'],
            'registro' => $activo,
        ]);
    }

    public function editar(string $id): void
    {
        $activo = $this->modelo->buscar((int) $id);

        if (!$activo) {
            abort(404);
        }

        $this->vista('activos', [
            'modo' => 'formulario',
            'titulo' => 'Editar '.$activo['asset_code'],
            'registro' => $activo,
        ] + $this->catalogos());
    }

    public function actualizar(string $id): void
    {
        Csrf::verificar();

        $anterior = $this->modelo->buscar((int) $id);

        if (!$anterior) {
            abort(404);
        }

        $datos = $this->datosFormulario();
        $errores = $this->validar($datos, [
            'asset_type_id' => 'required',
            'status_id' => 'required',
        ]);

        if ($errores) {
            $this->enviarErrores($errores, $_POST, 'activos/'.$id.'/editar');
        }

        try {
            $this->modelo->guardar($datos, $this->especificaciones(), Auth::id(), (int) $id);
            Auditoria::registrar('Inventario', 'ACTUALIZAR', 'activo', (int) $id, $anterior, $datos);
            Flash::exito('Activo actualizado.');
            redirect('activos/'.$id);
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('activos/'.$id.'/editar');
        }
    }

    public function formularioImportacion(): void
    {
        $this->vista('activos', [
            'modo' => 'importar',
            'titulo' => 'Importar inventario',
        ]);
    }

    public function importarCsv(): void
    {
        Csrf::verificar();

        if (empty($_FILES['csv']['tmp_name'])) {
            Flash::error('Selecciona un CSV.');
            redirect('activos/importar');
        }

        $archivo = fopen($_FILES['csv']['tmp_name'], 'r');
        $cabeceras = fgetcsv($archivo, 0, ',');

        if (!$cabeceras) {
            Flash::error('CSV vacío.');
            redirect('activos/importar');
        }

        $cabeceras = array_map(fn ($header) => trim(strtolower($header)), $cabeceras);
        $importados = 0;
        $errores = [];

        while (($fila = fgetcsv($archivo, 0, ',')) !== false) {
            if (count($fila) !== count($cabeceras)) {
                continue;
            }

            $registro = array_combine($cabeceras, $fila);

            try {
                $this->modelo->guardar($this->mapearFilaCsv($registro), [], Auth::id());
                $importados++;
            } catch (Throwable $exception) {
                $errores[] = $exception->getMessage();
            }
        }

        fclose($archivo);

        Flash::exito("Se importaron $importados activos.");

        if ($errores) {
            Flash::advertencia(implode(' | ', array_slice($errores, 0, 4)));
        }

        redirect('activos');
    }

    private function catalogos(): array
    {
        return [
            'tipos' => $this->catalogo->listar('tipos_activo'),
            'estados' => $this->catalogo->listar('estados_activo'),
            'marcas' => $this->catalogo->listar('marcas'),
            'modelos' => $this->catalogo->listar('modelos'),
            'areas' => $this->catalogo->listar('areas'),
            'ubicaciones' => $this->catalogo->listar('ubicaciones'),
            'proveedores' => $this->catalogo->listar('proveedores'),
        ];
    }

    private function datosFormulario(): array
    {
        return [
            'legacy_code' => trim($_POST['legacy_code'] ?? ''),
            'asset_type_id' => (int) ($_POST['asset_type_id'] ?? 0),
            'brand_id' => (int) ($_POST['brand_id'] ?? 0),
            'model_id' => (int) ($_POST['model_id'] ?? 0),
            'status_id' => (int) ($_POST['status_id'] ?? 0),
            'current_area_id' => (int) ($_POST['current_area_id'] ?? 0),
            'location_id' => (int) ($_POST['location_id'] ?? 0),
            'serial_number' => trim($_POST['serial_number'] ?? ''),
            'hostname' => trim($_POST['hostname'] ?? ''),
            'ip_address' => trim($_POST['ip_address'] ?? ''),
            'mac_address' => trim($_POST['mac_address'] ?? ''),
            'imei1' => trim($_POST['imei1'] ?? ''),
            'imei2' => trim($_POST['imei2'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'purchase_date' => trim($_POST['purchase_date'] ?? ''),
            'invoice_number' => trim($_POST['invoice_number'] ?? ''),
            'supplier_id' => (int) ($_POST['supplier_id'] ?? 0),
            'cost' => trim($_POST['cost'] ?? ''),
            'warranty_end' => trim($_POST['warranty_end'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];
    }

    private function especificaciones(): array
    {
        $claves = $_POST['spec_key'] ?? [];
        $valors = $_POST['spec_value'] ?? [];
        $especificaciones = [];

        foreach ($claves as $indice => $clave) {
            $clave = trim($clave);

            if ($clave !== '') {
                $especificaciones[$clave] = trim($valors[$indice] ?? '');
            }
        }

        return $especificaciones;
    }

    private function mapearFilaCsv(array $registro): array
    {
        $tipo = $this->buscarPorNombre('tipos_activo', $registro['tipo'] ?? '');

        if (!$tipo) {
            throw new \RuntimeException('Tipo inexistente: '.($registro['tipo'] ?? ''));
        }

        $marca = $this->buscarOCrear('marcas', $registro['marca'] ?? '');
        $area = $this->buscarOCrear('areas', $registro['area'] ?? '');

        return [
            'legacy_code' => $registro['codigo_anterior'] ?? '',
            'asset_type_id' => $tipo,
            'brand_id' => $marca,
            'model_id' => $this->buscarModelo($marca, $registro['modelo'] ?? ''),
            'status_id' => $this->buscarPorCodigo('estados_activo', 'DISPONIBLE'),
            'current_area_id' => $area,
            'location_id' => $this->buscarUbicacion($area, $registro['ubicacion'] ?? ''),
            'serial_number' => $registro['serie'] ?? '',
            'hostname' => $registro['hostname'] ?? '',
            'ip_address' => $registro['ip'] ?? '',
            'mac_address' => $registro['mac'] ?? '',
            'imei1' => $registro['imei1'] ?? '',
            'imei2' => $registro['imei2'] ?? '',
            'phone_number' => $registro['telefono'] ?? '',
            'purchase_date' => $registro['fecha_compra'] ?? '',
            'invoice_number' => $registro['factura'] ?? '',
            'supplier_id' => $this->buscarOCrear('proveedores', $registro['proveedor'] ?? ''),
            'cost' => $registro['costo'] ?? '',
            'warranty_end' => $registro['fin_garantia'] ?? '',
            'notes' => $registro['observaciones'] ?? '',
        ];
    }

    private function buscarPorNombre(string $tabla, string $nombre): ?int
    {
        if (trim($nombre) === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare("SELECT id FROM $tabla WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $consulta->execute([trim($nombre)]);
        $id = $consulta->fetchColumn();

        return $id ? (int) $id : null;
    }

    private function buscarPorCodigo(string $tabla, string $codigo): int
    {
        $consulta = BD::pdo()->prepare("SELECT id FROM $tabla WHERE code = ?");
        $consulta->execute([$codigo]);

        return (int) $consulta->fetchColumn();
    }

    private function buscarOCrear(string $tabla, string $nombre): ?int
    {
        if (trim($nombre) === '') {
            return null;
        }

        $id = $this->buscarPorNombre($tabla, $nombre);

        if ($id) {
            return $id;
        }

        $consulta = BD::pdo()->prepare("INSERT INTO $tabla(name, active) VALUES(?, 1)");
        $consulta->execute([trim($nombre)]);

        return (int) BD::pdo()->lastInsertId();
    }

    private function buscarModelo(?int $marcaId, string $nombre): ?int
    {
        if (trim($nombre) === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare(
            'SELECT id FROM modelos WHERE brand_id <=> ? AND LOWER(name) = LOWER(?)'
        );
        $consulta->execute([$marcaId, trim($nombre)]);
        $id = $consulta->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $consulta = BD::pdo()->prepare('INSERT INTO modelos(brand_id, name, active) VALUES(?, ?, 1)');
        $consulta->execute([$marcaId, trim($nombre)]);

        return (int) BD::pdo()->lastInsertId();
    }

    private function buscarUbicacion(?int $areaId, string $nombre): ?int
    {
        if (trim($nombre) === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare(
            'SELECT id FROM ubicaciones WHERE area_id <=> ? AND LOWER(name) = LOWER(?)'
        );
        $consulta->execute([$areaId, trim($nombre)]);
        $id = $consulta->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $consulta = BD::pdo()->prepare('INSERT INTO ubicaciones(area_id, name, active) VALUES(?, ?, 1)');
        $consulta->execute([$areaId, trim($nombre)]);

        return (int) BD::pdo()->lastInsertId();
    }
}