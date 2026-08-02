<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, BD, Controlador, Csrf, Flash, LectorExcel, Vista};
use App\Modelos\{Activo, Asignacion, Trabajador};
use Throwable;

final class AsignacionControlador extends Controlador
{
    private Asignacion $modelo;

    public function __construct()
    {
        Auth::requerirIngreso();
        $this->modelo = new Asignacion();
    }

    public function listado(): void
    {
        $this->vista('asignaciones', [
            'modo' => 'listado',
            'titulo' => 'Asignaciones',
            'filas' => $this->modelo->listar(),
        ]);
    }

    public function crear(): void
    {
        $this->vista('asignaciones', [
            'modo' => 'formulario',
            'titulo' => 'Nueva asignacion',
            'trabajadores' => (new Trabajador())->listar(),
            'activos' => (new Activo())->disponibles(),
        ]);
    }

    public function guardar(): void
    {
        Csrf::verificar();

        $trabajadorId = (int) ($_POST['trabajador_id'] ?? 0);
        $activosIds = array_values(array_unique(array_map('intval', $_POST['activo_ids'] ?? [])));

        if (!$trabajadorId || !$activosIds) {
            Flash::error('Selecciona un trabajador y al menos un activo.');
            redirect('asignaciones/crear');
        }

        $elementos = [];
        foreach ($activosIds as $activoId) {
            $elementos[] = [
                'activo_id' => $activoId,
                'condicion' => trim($_POST['condicion'][$activoId] ?? 'Buen estado'),
            ];
        }

        try {
            $id = $this->modelo->crear(
                $trabajadorId,
                (int) ($_POST['area_id'] ?? 0) ?: null,
                trim($_POST['observaciones'] ?? ''),
                $elementos,
                Auth::id()
            );

            Flash::exito('Asignacion confirmada.');
            redirect('asignaciones/'.$id);
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('asignaciones/crear');
        }
    }

    public function formularioImportacion(): void
    {
        $this->vista('asignaciones', [
            'modo' => 'importar',
            'titulo' => 'Importar asignaciones',
            'preview' => $_SESSION['importacion_asignaciones'] ?? null,
        ]);
    }

    public function importarArchivo(): void
    {
        Csrf::verificar();

        if (isset($_POST['cancelar'])) {
            unset($_SESSION['importacion_asignaciones']);
            Flash::advertencia('Importacion cancelada.');
            redirect('asignaciones/importar');
        }

        if (isset($_POST['confirmar'])) {
            $preview = $_SESSION['importacion_asignaciones'] ?? null;

            if (!$preview || !empty($preview['bloqueado'])) {
                Flash::error('No hay una importacion valida para confirmar.');
                redirect('asignaciones/importar');
            }

            [$creadas, $errores] = $this->guardarAsignacionesImportadas($preview['grupos']);
            unset($_SESSION['importacion_asignaciones']);

            Flash::exito("Se crearon $creadas asignaciones.");

            if ($errores) {
                Flash::advertencia(implode(' | ', array_slice($errores, 0, 4)));
            }

            redirect('asignaciones');
        }

        if (empty($_FILES['archivo']['tmp_name'])) {
            Flash::error('Selecciona un Excel o CSV.');
            redirect('asignaciones/importar');
        }

        $nombreArchivo = $_FILES['archivo']['name'] ?? 'asignaciones.xlsx';
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        try {
            $filas = match ($extension) {
                'csv', 'txt' => $this->filasDesdeCsv($_FILES['archivo']['tmp_name'], $nombreArchivo),
                'xlsx' => $this->filasDesdeExcel($_FILES['archivo']['tmp_name'], $nombreArchivo),
                default => throw new \RuntimeException('Formato no permitido. Usa CSV o XLSX.'),
            };

            $preview = $this->validarImportacion($filas, $nombreArchivo);
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('asignaciones/importar');
        }

        $_SESSION['importacion_asignaciones'] = $preview;

        $this->vista('asignaciones', [
            'modo' => 'importar',
            'titulo' => 'Importar asignaciones',
            'preview' => $preview,
        ]);
    }

    public function ver(string $id): void
    {
        $asignacion = $this->modelo->buscar((int) $id);

        if (!$asignacion) {
            abort(404);
        }

        $this->vista('asignaciones', [
            'modo' => 'detalle',
            'titulo' => $asignacion['numero_asignacion'],
            'registro' => $asignacion,
        ]);
    }

    public function imprimir(string $id): void
    {
        $asignacion = $this->modelo->buscar((int) $id);

        if (!$asignacion) {
            abort(404);
        }

        $this->vista(
            'imprimir',
            ['doc' => 'assignment', 'titulo' => $asignacion['numero_asignacion'], 'registro' => $asignacion],
            'plantilla_impresion'
        );
    }

    public function pdf(string $id): void
    {
        $asignacion = $this->modelo->buscar((int) $id);

        if (!$asignacion) {
            abort(404);
        }

        if (!class_exists('Dompdf\\Dompdf')) {
            redirect('asignaciones/'.$id.'/imprimir');
        }

        $html = Vista::capturar('imprimir', [
            'doc' => 'assignment',
            'registro' => $asignacion,
            'pdf' => true,
        ]);

        $pdf = new \Dompdf\Dompdf();
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        $pdf->stream($asignacion['numero_asignacion'].'.pdf', ['Attachment' => true]);
    }

    private function filasDesdeCsv(string $ruta, string $nombreArchivo): array
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

            $filas[] = $this->filaDesdeColumnas($cabeceras, $fila, (string) $numero, $nombreArchivo);
        }

        fclose($archivo);

        return $filas;
    }

    private function filasDesdeExcel(string $ruta, string $nombreArchivo): array
    {
        $hojas = LectorExcel::leerHojas($ruta);
        $filas = [];

        foreach ($hojas as $nombreHoja => $filasExcel) {
            if (!$this->hojaImportable((string) $nombreHoja)) {
                continue;
            }

            $cabeceras = null;
            $filaCabecera = null;

            foreach ($filasExcel as $indice => $filaExcel) {
                $posibles = array_map(fn ($cabecera) => $this->normalizarCabecera((string) $cabecera), $filaExcel);

                if ($this->cabeceraTieneAsignacion($posibles)) {
                    $cabeceras = $posibles;
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

                $filas[] = $this->filaDesdeColumnas(
                    $cabeceras,
                    $filaExcel,
                    ($filaCabecera + $indice + 2).' / '.$nombreHoja,
                    (string) $nombreHoja
                );
            }
        }

        if (!$filas) {
            throw new \RuntimeException('No se encontraron filas de asignacion en el archivo.');
        }

        return $filas;
    }

    private function filaDesdeColumnas(array $cabeceras, array $valores, string $numero, string $origen): array
    {
        $registro = [];

        foreach ($cabeceras as $indice => $cabecera) {
            if ($cabecera !== '') {
                $registro[$cabecera] = trim((string) ($valores[$indice] ?? ''));
            }
        }

        return [
            'numero' => $numero,
            'origen' => $origen,
            'datos' => [
                'trabajador' => $this->limpiarTexto($registro['trabajador'] ?? ''),
                'codigo_activo' => $this->primerValor($registro, ['codigo_activo', 'codigo_anterior', 'codigo', 'item']),
                'serie' => $this->primerValor($registro, ['serie', 'imei', 'telefono', 'nombre_equipo']),
                'area' => $this->limpiarTexto($registro['area'] ?? ''),
                'fecha' => $this->normalizarFecha($registro['fecha'] ?? ''),
                'condicion' => $this->limpiarTexto($registro['condicion'] ?? 'Buen estado'),
            ],
            'trabajador_id' => null,
            'activo_id' => null,
            'area_id' => null,
            'errores' => [],
            'advertencias' => [],
        ];
    }

    private function validarImportacion(array $filas, string $nombreArchivo): array
    {
        $errores = 0;
        $advertencias = 0;
        $activosVistos = [];
        $grupos = [];

        foreach ($filas as $indice => $fila) {
            $datos = $fila['datos'];
            $trabajador = $this->buscarTrabajador($datos['trabajador']);
            $activo = $this->buscarActivo($datos['codigo_activo'], $datos['serie']);

            if ($datos['trabajador'] === '') {
                $filas[$indice]['errores'][] = 'Falta trabajador.';
            } elseif (!$trabajador) {
                $filas[$indice]['errores'][] = 'Trabajador no encontrado.';
            } else {
                $filas[$indice]['trabajador_id'] = (int) $trabajador['id'];
                $filas[$indice]['area_id'] = (int) ($trabajador['area_id'] ?? 0) ?: $this->buscarArea($datos['area']);
            }

            if ($datos['codigo_activo'] === '' && $datos['serie'] === '') {
                $filas[$indice]['errores'][] = 'Falta codigo, serie, IMEI o telefono del activo.';
            } elseif (!$activo) {
                $filas[$indice]['errores'][] = 'Activo no encontrado.';
            } else {
                $filas[$indice]['activo_id'] = (int) $activo['id'];

                if (($activo['codigo_estado'] ?? '') !== 'DISPONIBLE') {
                    $filas[$indice]['errores'][] = 'Activo no disponible: '.$activo['codigo_estado'].'.';
                }

                if (isset($activosVistos[$activo['id']])) {
                    $filas[$indice]['errores'][] = 'Activo repetido en el archivo.';
                }

                $activosVistos[$activo['id']] = true;
            }

            if ($datos['condicion'] === '') {
                $filas[$indice]['datos']['condicion'] = 'Buen estado';
            }

            if ($datos['fecha'] === '') {
                $filas[$indice]['advertencias'][] = 'Sin fecha; se usara la fecha actual.';
            }

            if (empty($filas[$indice]['errores'])) {
                $grupoClave = $filas[$indice]['trabajador_id'].'|'.($datos['fecha'] ?: 'actual');
                $grupos[$grupoClave]['trabajador_id'] = $filas[$indice]['trabajador_id'];
                $grupos[$grupoClave]['area_id'] = $filas[$indice]['area_id'];
                $grupos[$grupoClave]['fecha'] = $datos['fecha'];
                $grupos[$grupoClave]['trabajador'] = $datos['trabajador'];
                $grupos[$grupoClave]['elementos'][] = [
                    'activo_id' => $filas[$indice]['activo_id'],
                    'condicion' => $filas[$indice]['datos']['condicion'],
                ];
            }

            $errores += count($filas[$indice]['errores']);
            $advertencias += count($filas[$indice]['advertencias']);
        }

        return [
            'archivo' => $nombreArchivo,
            'bloqueado' => $errores > 0,
            'resumen' => [
                'filas' => count($filas),
                'asignaciones' => count($grupos),
                'activos' => array_sum(array_map(fn ($grupo) => count($grupo['elementos']), $grupos)),
                'errores' => $errores,
                'advertencias' => $advertencias,
            ],
            'filas' => $filas,
            'grupos' => array_values($grupos),
        ];
    }

    private function guardarAsignacionesImportadas(array $grupos): array
    {
        $creadas = 0;
        $errores = [];

        foreach ($grupos as $grupo) {
            try {
                $notaFecha = $grupo['fecha'] ? ' Fecha de entrega: '.$grupo['fecha'].'.' : '';
                $id = $this->modelo->crear(
                    (int) $grupo['trabajador_id'],
                    (int) ($grupo['area_id'] ?? 0) ?: null,
                    'Asignacion importada desde Excel.'.$notaFecha,
                    $grupo['elementos'],
                    Auth::id()
                );

                if (!empty($grupo['fecha'])) {
                    $consulta = BD::pdo()->prepare('UPDATE asignaciones SET asignado_en = ? WHERE id = ?');
                    $consulta->execute([$grupo['fecha'].' 09:00:00', $id]);
                }

                $creadas++;
            } catch (Throwable $exception) {
                $errores[] = 'Grupo '.$grupo['trabajador'].': '.$exception->getMessage();
            }
        }

        return [$creadas, $errores];
    }

    private function normalizarCabecera(string $cabecera): string
    {
        $cabecera = preg_replace('/^\\xEF\\xBB\\xBF/', '', trim($cabecera)) ?? '';
        $cabecera = strtolower($cabecera);

        if (function_exists('iconv')) {
            $convertida = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cabecera);
            $cabecera = $convertida !== false ? $convertida : $cabecera;
        }

        $cabecera = trim(preg_replace('/[^a-z0-9]+/', '_', $cabecera) ?? '', '_');
        $alias = [
            'codigo' => 'codigo',
            'cod' => 'codigo',
            'item' => 'codigo',
            'cod_monitor' => 'codigo',
            'codigo_activo' => 'codigo_activo',
            'codigo_anterior' => 'codigo_anterior',
            'codigo_serie' => 'codigo',
            'serie_s_n' => 'serie',
            's_n' => 'serie',
            'sn' => 'serie',
            'serial' => 'serie',
            'imei' => 'imei',
            'imei_1' => 'imei',
            'n_celular' => 'telefono',
            'numero_telefono' => 'telefono',
            'chip_de_linea' => 'telefono',
            'nombre_del_equipo' => 'nombre_equipo',
            'nombre_del_dispositivo' => 'nombre_equipo',
            'trabajador' => 'trabajador',
            'colaborador' => 'trabajador',
            'asignado_a' => 'trabajador',
            'asignado_al_colaborador' => 'trabajador',
            'encargado' => 'trabajador',
            'responsable' => 'trabajador',
            'usuario' => 'trabajador',
            'area' => 'area',
            'sede' => 'area',
            'fecha_entrega' => 'fecha',
            'fecha_de_entrega' => 'fecha',
            'fecha' => 'fecha',
            'condicion' => 'condicion',
            'estado_de_entrega' => 'condicion',
            'observaciones' => 'condicion',
        ];

        return $alias[$cabecera] ?? $cabecera;
    }

    private function cabeceraTieneAsignacion(array $cabeceras): bool
    {
        $tieneTrabajador = in_array('trabajador', $cabeceras, true);
        $tieneActivo = (bool) array_intersect($cabeceras, ['codigo', 'codigo_activo', 'codigo_anterior', 'serie', 'imei', 'telefono', 'nombre_equipo']);

        return $tieneTrabajador && $tieneActivo;
    }

    private function hojaImportable(string $nombreHoja): bool
    {
        $nombre = strtoupper($nombreHoja);

        return str_contains($nombre, 'PC')
            || str_contains($nombre, 'LAPTOP')
            || str_contains($nombre, 'CEL')
            || str_contains($nombre, 'MONITOR')
            || str_contains($nombre, 'RADIO')
            || str_contains($nombre, 'IMPRES');
    }

    private function primerValor(array $registro, array $campos): string
    {
        foreach ($campos as $campo) {
            if (!empty($registro[$campo])) {
                return $this->limpiarTexto($registro[$campo]);
            }
        }

        return '';
    }

    private function limpiarTexto(string $valor): string
    {
        $valor = trim(preg_replace('/\\s+/', ' ', $valor) ?? '');
        $invalidos = ['', '-', '--', 'N/A', 'NA', 'NO ASIGNADO', 'SIN ASIGNAR', 'DISPONIBLE', 'ALMACEN', 'STOCK', 'BAJA'];

        return in_array(strtoupper($valor), $invalidos, true) ? '' : $valor;
    }

    private function normalizarFecha(string $fecha): string
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

        return '';
    }

    private function buscarTrabajador(string $nombre): ?array
    {
        $nombre = $this->limpiarTexto($nombre);

        if ($nombre === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare(
            'SELECT id, area_id, CONCAT(nombres, " ", apellidos) nombre_completo
             FROM trabajadores
             WHERE activo = 1 AND (
                LOWER(CONCAT(nombres, " ", apellidos)) = LOWER(?)
                OR LOWER(CONCAT(apellidos, " ", nombres)) = LOWER(?)
             )
             LIMIT 1'
        );
        $consulta->execute([$nombre, $nombre]);

        return $consulta->fetch() ?: null;
    }

    private function buscarActivo(string $codigo, string $serie): ?array
    {
        $codigo = $this->limpiarTexto($codigo);
        $serie = $this->limpiarTexto($serie);

        if ($codigo === '' && $serie === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare(
            'SELECT a.id, a.codigo_activo, a.codigo_anterior, a.numero_serie, a.imei1, a.numero_telefono,
                    s.codigo codigo_estado
             FROM activos a
             JOIN estados_activo s ON s.id = a.estado_id
             WHERE a.activo = 1 AND (
                (? <> "" AND (a.codigo_activo = ? OR a.codigo_anterior = ? OR a.nombre_equipo = ?))
                OR (? <> "" AND (a.numero_serie = ? OR a.imei1 = ? OR a.imei2 = ? OR a.numero_telefono = ? OR a.nombre_equipo = ?))
             )
             LIMIT 1'
        );
        $consulta->execute([$codigo, $codigo, $codigo, $codigo, $serie, $serie, $serie, $serie, $serie, $serie]);

        return $consulta->fetch() ?: null;
    }

    private function buscarArea(string $nombre): ?int
    {
        $nombre = $this->limpiarTexto($nombre);

        if ($nombre === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare('SELECT id FROM areas WHERE LOWER(nombre) = LOWER(?) LIMIT 1');
        $consulta->execute([$nombre]);
        $id = $consulta->fetchColumn();

        return $id ? (int) $id : null;
    }
}