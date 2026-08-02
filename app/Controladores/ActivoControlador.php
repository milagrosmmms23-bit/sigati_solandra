<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Auditoria, Controlador, Csrf, BD, Flash, LectorExcel};
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
            'preview' => $_SESSION['importacion_activos'] ?? null,
        ]);
    }

    public function importarCsv(): void
    {
        Csrf::verificar();

        if (isset($_POST['cancelar'])) {
            unset($_SESSION['importacion_activos']);
            Flash::advertencia('Importacion cancelada.');
            redirect('activos/importar');
        }

        if (isset($_POST['confirmar'])) {
            $preview = $_SESSION['importacion_activos'] ?? null;

            if (!$preview || !empty($preview['bloqueado'])) {
                Flash::error('No hay una importacion valida para confirmar.');
                redirect('activos/importar');
            }

            [$importados, $errores] = $this->guardarImportacionValidada($preview['filas']);
            unset($_SESSION['importacion_activos']);
            Flash::exito("Se importaron $importados activos.");

            if ($errores) {
                Flash::advertencia(implode(' | ', array_slice($errores, 0, 4)));
            }

            redirect('activos');
        }

        if (empty($_FILES['csv']['tmp_name'])) {
            Flash::error('Selecciona un archivo CSV o Excel.');
            redirect('activos/importar');
        }

        $nombreArchivo = $_FILES['csv']['name'] ?? 'inventario.csv';
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        try {
            $preview = match ($extension) {
                'csv', 'txt' => $this->prepararImportacionCsv($_FILES['csv']['tmp_name'], $nombreArchivo),
                'xlsx' => $this->prepararImportacionExcel($_FILES['csv']['tmp_name'], $nombreArchivo),
                default => throw new \RuntimeException('Formato no permitido. Usa CSV o XLSX.'),
            };
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('activos/importar');
        }

        $_SESSION['importacion_activos'] = $preview;

        $this->vista('activos', [
            'modo' => 'importar',
            'titulo' => 'Importar inventario',
            'preview' => $preview,
        ]);
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

    private function prepararImportacionCsv(string $ruta, string $nombreArchivo): array
    {
        $archivo = fopen($ruta, 'r');

        if (!$archivo) {
            throw new \RuntimeException('No se pudo leer el archivo.');
        }

        $primeraLinea = fgets($archivo) ?: '';
        rewind($archivo);
        $delimitador = substr_count($primeraLinea, ';') > substr_count($primeraLinea, ',') ? ';' : ',';
        $cabeceras = fgetcsv($archivo, 0, $delimitador);

        if (!$cabeceras) {
            fclose($archivo);
            throw new \RuntimeException('CSV vacio o sin cabeceras.');
        }

        $cabeceras = array_map(fn ($cabecera) => $this->normalizarCabecera((string) $cabecera), $cabeceras);
        $filas = [];
        $numero = 1;

        while (($fila = fgetcsv($archivo, 0, $delimitador)) !== false) {
            $numero++;

            if (implode('', array_map('trim', $fila)) === '') {
                continue;
            }

            $registro = [];

            foreach ($cabeceras as $indice => $cabecera) {
                if ($cabecera !== '') {
                    $registro[$cabecera] = trim((string) ($fila[$indice] ?? ''));
                }
            }

            $filas[] = [
                'numero' => $numero,
                'datos' => $this->normalizarRegistroImportacion($registro),
                'errores' => [],
                'advertencias' => [],
            ];
        }

        fclose($archivo);

        return $this->validarImportacion($filas, $nombreArchivo);
    }

    private function prepararImportacionExcel(string $ruta, string $nombreArchivo): array
    {
        $hojasExcel = LectorExcel::leerHojas($ruta);
        $camposReconocidos = [
            'tipo', 'codigo_anterior', 'marca', 'modelo', 'serie', 'area', 'ubicacion', 'nombre_equipo',
            'ip', 'mac', 'imei1', 'imei2', 'telefono', 'fecha_compra', 'factura', 'proveedor',
            'costo', 'fin_garantia', 'observaciones',
        ];
        $filas = [];

        foreach ($hojasExcel as $nombreHoja => $filasExcel) {
            if (!$this->hojaExcelImportable((string) $nombreHoja)) {
                continue;
            }
            $cabeceras = null;
            $filaCabecera = null;

            foreach ($filasExcel as $indice => $filaExcel) {
                $posiblesCabeceras = array_map(fn ($cabecera) => $this->normalizarCabecera((string) $cabecera), $filaExcel);
                $reconocidas = count(array_intersect($posiblesCabeceras, $camposReconocidos));

                if ($reconocidas >= 2) {
                    $cabeceras = $posiblesCabeceras;
                    $filaCabecera = $indice;
                    break;
                }
            }

            if ($cabeceras === null || $filaCabecera === null) {
                continue;
            }

            foreach (array_slice($filasExcel, $filaCabecera + 1) as $indice => $filaExcel) {
                if (implode('', array_map('trim', $filaExcel)) === '') {
                    continue;
                }

                $registro = [];

                foreach ($cabeceras as $columna => $cabecera) {
                    if ($cabecera !== '') {
                        $registro[$cabecera] = trim((string) ($filaExcel[$columna] ?? ''));
                    }
                }

                if (empty($registro['tipo'])) {
                    $registro['tipo'] = $this->tipoDesdeNombreHoja((string) $nombreHoja);
                }

                $filas[] = [
                    'numero' => ($filaCabecera + $indice + 2).' / '.$nombreHoja,
                    'datos' => $this->normalizarRegistroImportacion($registro),
                    'errores' => [],
                    'advertencias' => [],
                ];
            }
        }

        if (!$filas) {
            throw new \RuntimeException('No se encontraron hojas con cabeceras de inventario.');
        }

        return $this->validarImportacion($filas, $nombreArchivo);
    }

    private function hojaExcelImportable(string $nombreHoja): bool
    {
        $nombre = strtoupper($nombreHoja);

        return str_contains($nombre, 'PC')
            || str_contains($nombre, 'LAPTOP')
            || str_contains($nombre, 'CEL')
            || str_contains($nombre, 'MONITOR')
            || str_contains($nombre, 'RADIO')
            || str_contains($nombre, 'IMPRES');
    }
    private function tipoDesdeNombreHoja(string $nombreHoja): string
    {
        $nombre = strtoupper($nombreHoja);

        return match (true) {
            str_contains($nombre, 'CEL') => 'Celular',
            str_contains($nombre, 'MONITOR') => 'Monitor',
            str_contains($nombre, 'RADIO') => 'Radio',
            str_contains($nombre, 'IMPRES') => 'Impresora',
            str_contains($nombre, 'SIM') || str_contains($nombre, 'CHIP') => 'SIM',
            default => '',
        };
    }
    private function normalizarCabecera(string $cabecera): string
    {
        $cabecera = preg_replace('/^\xEF\xBB\xBF/', '', trim($cabecera)) ?? '';
        $cabecera = strtolower($cabecera);

        if (function_exists('iconv')) {
            $convertida = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cabecera);
            $cabecera = $convertida !== false ? $convertida : $cabecera;
        }

        $cabecera = trim(preg_replace('/[^a-z0-9]+/', '_', $cabecera) ?? '', '_');
        $alias = [
            'codigo' => 'codigo_anterior', 'cod' => 'codigo_anterior', 'item' => 'codigo_anterior',
            'codigo_activo' => 'codigo_anterior', 'codigo_actual' => 'codigo_anterior',
            'cod_monitor' => 'codigo_anterior',
            'codigo_serie' => 'codigo_anterior',
            'equipo' => 'nombre_equipo', 'nombre' => 'nombre_equipo', 'nombre_de_equipo' => 'nombre_equipo',
            'nombre_del_dispositivo' => 'nombre_equipo',
            'nombre_dispositivo' => 'nombre_equipo',
            'nombre_del_equipo' => 'nombre_equipo',
            'serial' => 'serie', 's_n' => 'serie', 'sn' => 'serie', 'numero_serie' => 'serie',
            'tipo_activo' => 'tipo', 'tipo_de_equipo' => 'tipo',
            'tipo_equipo' => 'tipo', 'direccion_ip' => 'ip', 'direccion_mac' => 'mac',
            'chip' => 'telefono', 'linea' => 'telefono', 'chip_de_linea' => 'telefono', 'numero_telefono' => 'telefono',
            'n_celular' => 'telefono',
            'n_telefono' => 'telefono',
            'imei' => 'imei1', 'imei_1' => 'imei1', 'imei_2' => 'imei2',
            'fecha_de_compra' => 'fecha_compra', 'fecha_entrega' => 'fecha_compra',
            'n_factura' => 'factura', 'numero_factura' => 'factura',
            'fin_de_garantia' => 'fin_garantia', 'vencimiento_garantia' => 'fin_garantia',
        ];

        return $alias[$cabecera] ?? $cabecera;
    }

    private function normalizarRegistroImportacion(array $registro): array
    {
        $campos = [
            'tipo', 'codigo_anterior', 'marca', 'modelo', 'serie', 'area', 'ubicacion', 'nombre_equipo',
            'ip', 'mac', 'imei1', 'imei2', 'telefono', 'fecha_compra', 'factura', 'proveedor',
            'costo', 'fin_garantia', 'observaciones',
        ];
        $datos = [];

        foreach ($campos as $campo) {
            $datos[$campo] = trim((string) ($registro[$campo] ?? ''));
        }

        $datos['tipo'] = $this->normalizarTipoImportacion($datos['tipo']);
        $datos['fecha_compra'] = $this->normalizarFechaImportacion($datos['fecha_compra']);
        $datos['fin_garantia'] = $this->normalizarFechaImportacion($datos['fin_garantia']);
        $datos['costo'] = str_replace(',', '.', $datos['costo']);

        return $datos;
    }

    private function normalizarTipoImportacion(string $tipo): string
    {
        $alias = [
            'CPU' => 'PC', 'TORRE' => 'PC', 'PC' => 'PC', 'LAPTOP' => 'Laptop', 'NOTEBOOK' => 'Laptop',
            'CELULAR' => 'Celular', 'TELEFONO' => 'Celular', 'MOVIL' => 'Celular',
            'MONITOR' => 'Monitor', 'PANTALLA' => 'Monitor', 'RADIO' => 'Radio',
            'IMPRESORA' => 'Impresora', 'PRINTER' => 'Impresora', 'SIM' => 'SIM', 'CHIP' => 'SIM',
        ];

        return $alias[strtoupper(trim($tipo))] ?? trim($tipo);
    }

    private function normalizarFechaImportacion(string $fecha): string
    {
        $fecha = trim($fecha);

        if ($fecha === '') {
            return '';
        }

        if (is_numeric($fecha) && (float) $fecha > 20000) {
            return (new \DateTimeImmutable('1899-12-30'))->modify('+'.(int) $fecha.' days')->format('Y-m-d');
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $formato) {
            $fechaObjeto = \DateTimeImmutable::createFromFormat($formato, $fecha);

            if ($fechaObjeto instanceof \DateTimeImmutable) {
                return $fechaObjeto->format('Y-m-d');
            }
        }

        return $fecha;
    }

    private function validarImportacion(array $filas, string $nombreArchivo): array
    {
        $errores = 0;
        $advertencias = 0;
        $vistos = ['codigo_anterior' => [], 'serie' => [], 'imei1' => [], 'telefono' => []];

        foreach ($filas as $indice => $fila) {
            $datos = $fila['datos'];

            if ($datos['tipo'] === '') {
                $filas[$indice]['errores'][] = 'Falta tipo.';
            } elseif (!$this->buscarPorNombre('tipos_activo', $datos['tipo'])) {
                $filas[$indice]['errores'][] = 'Tipo no existe: '.$datos['tipo'].'.';
            }

            foreach (['codigo_anterior' => 'Sin codigo anterior.', 'marca' => 'Sin marca.', 'area' => 'Sin area.'] as $campo => $mensaje) {
                if (($datos[$campo] ?? '') === '') {
                    $filas[$indice]['advertencias'][] = $mensaje;
                }
            }

            foreach (array_keys($vistos) as $campo) {
                $valor = strtoupper(preg_replace('/\s+/', '', trim($datos[$campo] ?? '')) ?? '');

                if ($valor === '') {
                    continue;
                }

                if (isset($vistos[$campo][$valor])) {
                    $filas[$indice]['errores'][] = 'Duplicado en archivo: '.$campo.'.';
                }

                $vistos[$campo][$valor] = true;

                if ($this->existeActivoImportado($campo, $datos[$campo])) {
                    $filas[$indice]['errores'][] = 'Ya existe en sistema: '.$campo.'.';
                }
            }

            $errores += count($filas[$indice]['errores']);
            $advertencias += count($filas[$indice]['advertencias']);
        }

        return [
            'archivo' => $nombreArchivo,
            'bloqueado' => $errores > 0,
            'resumen' => [
                'total' => count($filas),
                'validas' => count(array_filter($filas, fn ($fila) => empty($fila['errores']))),
                'errores' => $errores,
                'advertencias' => $advertencias,
            ],
            'filas' => $filas,
        ];
    }

    private function existeActivoImportado(string $campo, string $valor): bool
    {
        $valor = trim($valor);

        if ($valor === '') {
            return false;
        }

        $columnas = [
            'codigo_anterior' => 'codigo_anterior',
            'serie' => 'numero_serie',
            'imei1' => 'imei1',
            'telefono' => 'numero_telefono',
        ];

        if (!isset($columnas[$campo])) {
            return false;
        }

        $consulta = BD::pdo()->prepare("SELECT COUNT(*) FROM activos WHERE activo = 1 AND {$columnas[$campo]} = ?");
        $consulta->execute([$valor]);

        return (int) $consulta->fetchColumn() > 0;
    }

    private function guardarImportacionValidada(array $filas): array
    {
        $importados = 0;
        $errores = [];

        foreach ($filas as $fila) {
            if (!empty($fila['errores'])) {
                continue;
            }

            try {
                $datos = $this->mapearFilaCsv($fila['datos']);
                $id = $this->modelo->guardar($datos, [], Auth::id());
                Auditoria::registrar('Inventario', 'IMPORTAR', 'activo', $id, null, $datos);
                $importados++;
            } catch (Throwable $exception) {
                $errores[] = 'Fila '.$fila['numero'].': '.$exception->getMessage();
            }
        }

        return [$importados, $errores];
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
            'codigo_anterior' => trim($registro['codigo_anterior'] ?? ''),
            'tipo_activo_id' => $tipo,
            'marca_id' => $marca,
            'modelo_id' => $this->buscarModelo($marca, $registro['modelo'] ?? ''),
            'estado_id' => $this->buscarPorCodigo('estados_activo', 'DISPONIBLE'),
            'area_actual_id' => $area,
            'ubicacion_id' => $this->buscarUbicacion($area, $registro['ubicacion'] ?? ''),
            'numero_serie' => trim($registro['serie'] ?? ''),
            'nombre_equipo' => trim($registro['nombre_equipo'] ?? ''),
            'direccion_ip' => trim($registro['ip'] ?? ''),
            'direccion_mac' => trim($registro['mac'] ?? ''),
            'imei1' => trim($registro['imei1'] ?? ''),
            'imei2' => trim($registro['imei2'] ?? ''),
            'numero_telefono' => trim($registro['telefono'] ?? ''),
            'fecha_compra' => trim($registro['fecha_compra'] ?? ''),
            'numero_factura' => trim($registro['factura'] ?? ''),
            'proveedor_id' => $this->buscarOCrear('proveedores', $registro['proveedor'] ?? ''),
            'costo' => trim($registro['costo'] ?? ''),
            'fin_garantia' => trim($registro['fin_garantia'] ?? ''),
            'observaciones' => trim($registro['observaciones'] ?? ''),
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