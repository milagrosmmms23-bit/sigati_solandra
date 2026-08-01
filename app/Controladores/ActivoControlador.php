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
            'tipo_activo_id' => $_GET['tipo_activo_id'] ?? '',
            'estado_id' => $_GET['estado_id'] ?? '',
            'area_id' => $_GET['area_id'] ?? '',
        ];

        $pagina = max(1, (int) ($_GET['page'] ?? 1));
        $porPagina = (int) config('aplicacion.elementos_por_pagina', 15);

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
            'tipo_activo_id' => 'required',
            'estado_id' => 'required',
            'numero_serie' => 'max:150',
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
            'titulo' => $activo['codigo_activo'],
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
            'titulo' => 'Editar '.$activo['codigo_activo'],
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
            'tipo_activo_id' => 'required',
            'estado_id' => 'required',
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
            Flash::error('CSV vacio.');
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
            'codigo_anterior' => trim($_POST['codigo_anterior'] ?? ''),
            'tipo_activo_id' => (int) ($_POST['tipo_activo_id'] ?? 0),
            'marca_id' => (int) ($_POST['marca_id'] ?? 0),
            'modelo_id' => (int) ($_POST['modelo_id'] ?? 0),
            'estado_id' => (int) ($_POST['estado_id'] ?? 0),
            'area_actual_id' => (int) ($_POST['area_actual_id'] ?? 0),
            'ubicacion_id' => (int) ($_POST['ubicacion_id'] ?? 0),
            'numero_serie' => trim($_POST['numero_serie'] ?? ''),
            'nombre_equipo' => trim($_POST['nombre_equipo'] ?? ''),
            'direccion_ip' => trim($_POST['direccion_ip'] ?? ''),
            'direccion_mac' => trim($_POST['direccion_mac'] ?? ''),
            'imei1' => trim($_POST['imei1'] ?? ''),
            'imei2' => trim($_POST['imei2'] ?? ''),
            'numero_telefono' => trim($_POST['numero_telefono'] ?? ''),
            'fecha_compra' => trim($_POST['fecha_compra'] ?? ''),
            'numero_factura' => trim($_POST['numero_factura'] ?? ''),
            'proveedor_id' => (int) ($_POST['proveedor_id'] ?? 0),
            'costo' => trim($_POST['costo'] ?? ''),
            'fin_garantia' => trim($_POST['fin_garantia'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? ''),
        ];
    }

    private function especificaciones(): array
    {
        $claves = $_POST['clave_especificacion'] ?? [];
        $valors = $_POST['valor_especificacion'] ?? [];
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
            'codigo_anterior' => $registro['codigo_anterior'] ?? '',
            'tipo_activo_id' => $tipo,
            'marca_id' => $marca,
            'modelo_id' => $this->buscarModelo($marca, $registro['modelo'] ?? ''),
            'estado_id' => $this->buscarPorCodigo('estados_activo', 'DISPONIBLE'),
            'area_actual_id' => $area,
            'ubicacion_id' => $this->buscarUbicacion($area, $registro['ubicacion'] ?? ''),
            'numero_serie' => $registro['serie'] ?? '',
            'nombre_equipo' => $registro['nombre_equipo'] ?? '',
            'direccion_ip' => $registro['ip'] ?? '',
            'direccion_mac' => $registro['mac'] ?? '',
            'imei1' => $registro['imei1'] ?? '',
            'imei2' => $registro['imei2'] ?? '',
            'numero_telefono' => $registro['telefono'] ?? '',
            'fecha_compra' => $registro['fecha_compra'] ?? '',
            'numero_factura' => $registro['factura'] ?? '',
            'proveedor_id' => $this->buscarOCrear('proveedores', $registro['proveedor'] ?? ''),
            'costo' => $registro['costo'] ?? '',
            'fin_garantia' => $registro['fin_garantia'] ?? '',
            'observaciones' => $registro['observaciones'] ?? '',
        ];
    }

    private function buscarPorNombre(string $tabla, string $nombre): ?int
    {
        if (trim($nombre) === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare("SELECT id FROM $tabla WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
        $consulta->execute([trim($nombre)]);
        $id = $consulta->fetchColumn();

        return $id ? (int) $id : null;
    }

    private function buscarPorCodigo(string $tabla, string $codigo): int
    {
        $consulta = BD::pdo()->prepare("SELECT id FROM $tabla WHERE codigo = ?");
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

        $consulta = BD::pdo()->prepare("INSERT INTO $tabla(nombre, activo) VALUES(?, 1)");
        $consulta->execute([trim($nombre)]);

        return (int) BD::pdo()->lastInsertId();
    }

    private function buscarModelo(?int $marcaId, string $nombre): ?int
    {
        if (trim($nombre) === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare(
            'SELECT id FROM modelos WHERE marca_id <=> ? AND LOWER(nombre) = LOWER(?)'
        );
        $consulta->execute([$marcaId, trim($nombre)]);
        $id = $consulta->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $consulta = BD::pdo()->prepare('INSERT INTO modelos(marca_id, nombre, activo) VALUES(?, ?, 1)');
        $consulta->execute([$marcaId, trim($nombre)]);

        return (int) BD::pdo()->lastInsertId();
    }

    private function buscarUbicacion(?int $areaId, string $nombre): ?int
    {
        if (trim($nombre) === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare(
            'SELECT id FROM ubicaciones WHERE area_id <=> ? AND LOWER(nombre) = LOWER(?)'
        );
        $consulta->execute([$areaId, trim($nombre)]);
        $id = $consulta->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $consulta = BD::pdo()->prepare('INSERT INTO ubicaciones(area_id, nombre, activo) VALUES(?, ?, 1)');
        $consulta->execute([$areaId, trim($nombre)]);

        return (int) BD::pdo()->lastInsertId();
    }
}