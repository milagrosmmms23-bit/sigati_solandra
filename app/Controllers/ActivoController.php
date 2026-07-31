<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Auth, Audit, Controller, Csrf, DB, Flash};
use App\Models\{Activo, Catalogo};
use Throwable;

final class ActivoController extends Controller
{
    private Activo $model;
    private Catalogo $catalogo;

    public function __construct()
    {
        Auth::requireLogin();
        $this->model = new Activo();
        $this->catalogo = new Catalogo();
    }

    public function listado(): void
    {
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'type_id' => $_GET['type_id'] ?? '',
            'status_id' => $_GET['status_id'] ?? '',
            'area_id' => $_GET['area_id'] ?? '',
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) config('app.items_per_page', 15);

        $this->view('activos', [
            'mode' => 'listado',
            'title' => 'Inventario',
            'result' => $this->model->listar($filters, $page, $perPage),
            'filters' => $filters,
        ] + $this->catalogos());
    }

    public function crear(): void
    {
        $this->view('activos', [
            'mode' => 'formulario',
            'title' => 'Nuevo activo',
            'item' => null,
        ] + $this->catalogos());
    }

    public function guardar(): void
    {
        Csrf::verify();

        $data = $this->payload();
        $errors = $this->validate($data, [
            'asset_type_id' => 'required',
            'status_id' => 'required',
            'serial_number' => 'max:150',
        ]);

        if ($errors) {
            $this->errors($errors, $_POST, 'activos/crear');
        }

        try {
            $id = $this->model->guardar($data, $this->especificaciones(), Auth::id());
            Audit::log('Inventario', 'CREAR', 'activo', $id, null, $data);
            Flash::success('Activo registrado correctamente.');
            redirect('activos/'.$id);
        } catch (Throwable $exception) {
            Flash::error('No se pudo registrar: '.$exception->getMessage());
            $_SESSION['_old'] = $_POST;
            redirect('activos/crear');
        }
    }

    public function ver(string $id): void
    {
        $activo = $this->model->buscar((int) $id);

        if (!$activo) {
            abort(404, 'Activo no encontrado.');
        }

        $this->view('activos', [
            'mode' => 'detalle',
            'title' => $activo['asset_code'],
            'item' => $activo,
        ]);
    }

    public function editar(string $id): void
    {
        $activo = $this->model->buscar((int) $id);

        if (!$activo) {
            abort(404);
        }

        $this->view('activos', [
            'mode' => 'formulario',
            'title' => 'Editar '.$activo['asset_code'],
            'item' => $activo,
        ] + $this->catalogos());
    }

    public function actualizar(string $id): void
    {
        Csrf::verify();

        $anterior = $this->model->buscar((int) $id);

        if (!$anterior) {
            abort(404);
        }

        $data = $this->payload();
        $errors = $this->validate($data, [
            'asset_type_id' => 'required',
            'status_id' => 'required',
        ]);

        if ($errors) {
            $this->errors($errors, $_POST, 'activos/'.$id.'/editar');
        }

        try {
            $this->model->guardar($data, $this->especificaciones(), Auth::id(), (int) $id);
            Audit::log('Inventario', 'ACTUALIZAR', 'activo', (int) $id, $anterior, $data);
            Flash::success('Activo actualizado.');
            redirect('activos/'.$id);
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('activos/'.$id.'/editar');
        }
    }

    public function formularioImportacion(): void
    {
        $this->view('activos', [
            'mode' => 'importar',
            'title' => 'Importar inventario',
        ]);
    }

    public function importarCsv(): void
    {
        Csrf::verify();

        if (empty($_FILES['csv']['tmp_name'])) {
            Flash::error('Selecciona un CSV.');
            redirect('activos/importar');
        }

        $archivo = fopen($_FILES['csv']['tmp_name'], 'r');
        $headers = fgetcsv($archivo, 0, ',');

        if (!$headers) {
            Flash::error('CSV vacío.');
            redirect('activos/importar');
        }

        $headers = array_map(fn ($header) => trim(strtolower($header)), $headers);
        $importados = 0;
        $errores = [];

        while (($row = fgetcsv($archivo, 0, ',')) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }

            $registro = array_combine($headers, $row);

            try {
                $this->model->guardar($this->mapearFilaCsv($registro), [], Auth::id());
                $importados++;
            } catch (Throwable $exception) {
                $errores[] = $exception->getMessage();
            }
        }

        fclose($archivo);

        Flash::success("Se importaron $importados activos.");

        if ($errores) {
            Flash::warning(implode(' | ', array_slice($errores, 0, 4)));
        }

        redirect('activos');
    }

    private function catalogos(): array
    {
        return [
            'types' => $this->catalogo->listar('tipos_activo'),
            'statuses' => $this->catalogo->listar('estados_activo'),
            'marcas' => $this->catalogo->listar('marcas'),
            'modelos' => $this->catalogo->listar('modelos'),
            'areas' => $this->catalogo->listar('areas'),
            'ubicaciones' => $this->catalogo->listar('ubicaciones'),
            'proveedores' => $this->catalogo->listar('proveedores'),
        ];
    }

    private function payload(): array
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
        $keys = $_POST['spec_key'] ?? [];
        $values = $_POST['spec_value'] ?? [];
        $specs = [];

        foreach ($keys as $index => $key) {
            $key = trim($key);

            if ($key !== '') {
                $specs[$key] = trim($values[$index] ?? '');
            }
        }

        return $specs;
    }

    private function mapearFilaCsv(array $registro): array
    {
        $tipo = $this->findByName('tipos_activo', $registro['tipo'] ?? '');

        if (!$tipo) {
            throw new \RuntimeException('Tipo inexistente: '.($registro['tipo'] ?? ''));
        }

        $marca = $this->findOrCreate('marcas', $registro['marca'] ?? '');
        $area = $this->findOrCreate('areas', $registro['area'] ?? '');

        return [
            'legacy_code' => $registro['codigo_anterior'] ?? '',
            'asset_type_id' => $tipo,
            'brand_id' => $marca,
            'model_id' => $this->findModel($marca, $registro['modelo'] ?? ''),
            'status_id' => $this->findByCode('estados_activo', 'DISPONIBLE'),
            'current_area_id' => $area,
            'location_id' => $this->findLocation($area, $registro['ubicacion'] ?? ''),
            'serial_number' => $registro['serie'] ?? '',
            'hostname' => $registro['hostname'] ?? '',
            'ip_address' => $registro['ip'] ?? '',
            'mac_address' => $registro['mac'] ?? '',
            'imei1' => $registro['imei1'] ?? '',
            'imei2' => $registro['imei2'] ?? '',
            'phone_number' => $registro['telefono'] ?? '',
            'purchase_date' => $registro['fecha_compra'] ?? '',
            'invoice_number' => $registro['factura'] ?? '',
            'supplier_id' => $this->findOrCreate('proveedores', $registro['proveedor'] ?? ''),
            'cost' => $registro['costo'] ?? '',
            'warranty_end' => $registro['fin_garantia'] ?? '',
            'notes' => $registro['observaciones'] ?? '',
        ];
    }

    private function findByName(string $table, string $name): ?int
    {
        if (trim($name) === '') {
            return null;
        }

        $statement = DB::pdo()->prepare("SELECT id FROM $table WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $statement->execute([trim($name)]);
        $id = $statement->fetchColumn();

        return $id ? (int) $id : null;
    }

    private function findByCode(string $table, string $code): int
    {
        $statement = DB::pdo()->prepare("SELECT id FROM $table WHERE code = ?");
        $statement->execute([$code]);

        return (int) $statement->fetchColumn();
    }

    private function findOrCreate(string $table, string $name): ?int
    {
        if (trim($name) === '') {
            return null;
        }

        $id = $this->findByName($table, $name);

        if ($id) {
            return $id;
        }

        $statement = DB::pdo()->prepare("INSERT INTO $table(name, active) VALUES(?, 1)");
        $statement->execute([trim($name)]);

        return (int) DB::pdo()->lastInsertId();
    }

    private function findModel(?int $marcaId, string $name): ?int
    {
        if (trim($name) === '') {
            return null;
        }

        $statement = DB::pdo()->prepare(
            'SELECT id FROM modelos WHERE brand_id <=> ? AND LOWER(name) = LOWER(?)'
        );
        $statement->execute([$marcaId, trim($name)]);
        $id = $statement->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $statement = DB::pdo()->prepare('INSERT INTO modelos(brand_id, name, active) VALUES(?, ?, 1)');
        $statement->execute([$marcaId, trim($name)]);

        return (int) DB::pdo()->lastInsertId();
    }

    private function findLocation(?int $areaId, string $name): ?int
    {
        if (trim($name) === '') {
            return null;
        }

        $statement = DB::pdo()->prepare(
            'SELECT id FROM ubicaciones WHERE area_id <=> ? AND LOWER(name) = LOWER(?)'
        );
        $statement->execute([$areaId, trim($name)]);
        $id = $statement->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $statement = DB::pdo()->prepare('INSERT INTO ubicaciones(area_id, name, active) VALUES(?, ?, 1)');
        $statement->execute([$areaId, trim($name)]);

        return (int) DB::pdo()->lastInsertId();
    }
}