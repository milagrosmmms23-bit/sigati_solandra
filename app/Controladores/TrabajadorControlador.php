<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Auditoria, Controlador, Csrf, Flash, BD, LectorExcel};
use App\Modelos\{Catalogo, Trabajador};
use Throwable;

final class TrabajadorControlador extends Controlador
{
    private Trabajador $modelo;
    private Catalogo $catalogo;

    public function __construct()
    {
        Auth::requerirIngreso();
        $this->modelo = new Trabajador();
        $this->catalogo = new Catalogo();
    }

    public function listado(): void
    {
        $q = trim($_GET['q'] ?? '');

        $this->vista('trabajadores', [
            'modo' => 'listado',
            'titulo' => 'Trabajadores',
            'filas' => $this->modelo->listar($q),
            'q' => $q,
        ]);
    }

    public function crear(): void
    {
        $this->vista('trabajadores', [
            'modo' => 'formulario',
            'titulo' => 'Nuevo trabajador',
            'registro' => null,
            'areas' => $this->catalogo->listar('areas'),
        ]);
    }

    public function guardar(): void
    {
        Csrf::verificar();

        $datos = $this->datosFormulario();
        $errores = $this->validar($datos, [
            'codigo_trabajador' => 'required|max:50',
            'nombres' => 'required',
            'apellidos' => 'required',
            'correo' => 'correo',
        ]);

        if ($errores) {
            $this->enviarErrores($errores, $_POST, 'trabajadores/crear');
        }

        try {
            $id = $this->modelo->guardar($datos);
            Auditoria::registrar('Trabajadores', 'CREAR', 'trabajador', $id, null, $datos);
            Flash::exito('Trabajador registrado.');
            redirect('trabajadores');
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('trabajadores/crear');
        }
    }

    public function formularioImportacion(): void
    {
        $this->vista('trabajadores', [
            'modo' => 'importar',
            'titulo' => 'Importar trabajadores',
            'preview' => $_SESSION['importacion_trabajadores'] ?? null,
        ]);
    }

    public function importarArchivo(): void
    {
        Csrf::verificar();

        if (isset($_POST['cancelar'])) {
            unset($_SESSION['importacion_trabajadores']);
            Flash::advertencia('Importacion cancelada.');
            redirect('trabajadores/importar');
        }

        if (isset($_POST['confirmar'])) {
            $preview = $_SESSION['importacion_trabajadores'] ?? null;

            if (!$preview || !empty($preview['bloqueado'])) {
                Flash::error('No hay una importacion valida para confirmar.');
                redirect('trabajadores/importar');
            }

            [$importados, $omitidos, $errores] = $this->guardarImportacionValidada($preview['filas']);
            unset($_SESSION['importacion_trabajadores']);

            Flash::exito("Se importaron $importados trabajadores. Omitidos: $omitidos.");

            if ($errores) {
                Flash::advertencia(implode(' | ', array_slice($errores, 0, 4)));
            }

            redirect('trabajadores');
        }

        if (empty($_FILES['archivo']['tmp_name'])) {
            Flash::error('Selecciona un Excel o CSV.');
            redirect('trabajadores/importar');
        }

        $nombreArchivo = $_FILES['archivo']['name'] ?? 'trabajadores.xlsx';
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        try {
            $preview = match ($extension) {
                'csv', 'txt' => $this->prepararImportacionCsv($_FILES['archivo']['tmp_name'], $nombreArchivo),
                'xlsx' => $this->prepararImportacionExcel($_FILES['archivo']['tmp_name'], $nombreArchivo),
                default => throw new \RuntimeException('Formato no permitido. Usa CSV o XLSX.'),
            };
        } catch (Throwable $exception) {
            Flash::error($exception->getMessage());
            redirect('trabajadores/importar');
        }

        $_SESSION['importacion_trabajadores'] = $preview;

        $this->vista('trabajadores', [
            'modo' => 'importar',
            'titulo' => 'Importar trabajadores',
            'preview' => $preview,
        ]);
    }

    public function editar(string $id): void
    {
        $trabajador = $this->modelo->buscar((int) $id);

        if (!$trabajador) {
            abort(404);
        }

        $this->vista('trabajadores', [
            'modo' => 'formulario',
            'titulo' => 'Editar trabajador',
            'registro' => $trabajador,
            'areas' => $this->catalogo->listar('areas'),
        ]);
    }

    public function ver(string $id): void
    {
        $trabajador = $this->modelo->buscar((int) $id);

        if (!$trabajador) {
            abort(404, 'Trabajador no encontrado.');
        }

        $this->vista('trabajadores', [
            'modo' => 'detalle',
            'titulo' => trim($trabajador['nombres'].' '.$trabajador['apellidos']),
            'registro' => $trabajador,
            'activos' => $this->modelo->activosAsignados((int) $id),
            'asignaciones' => $this->modelo->asignaciones((int) $id),
        ]);
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
            'codigo_trabajador' => 'required',
            'nombres' => 'required',
            'apellidos' => 'required',
            'correo' => 'correo',
        ]);

        if ($errores) {
            $this->enviarErrores($errores, $_POST, 'trabajadores/'.$id.'/editar');
        }

        $this->modelo->guardar($datos, (int) $id);
        Auditoria::registrar('Trabajadores', 'ACTUALIZAR', 'trabajador', (int) $id, $anterior, $datos);
        Flash::exito('Trabajador actualizado.');
        redirect('trabajadores');
    }

    private function datosFormulario(): array
    {
        return [
            'codigo_trabajador' => trim($_POST['codigo_trabajador'] ?? ''),
            'nombres' => trim($_POST['nombres'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'cargo' => trim($_POST['cargo'] ?? ''),
            'area_id' => (int) ($_POST['area_id'] ?? 0),
        ];
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

            $filas[] = $this->filaDesdeColumnas($cabeceras, $fila, (string) $numero, 'CSV');
        }

        fclose($archivo);

        return $this->validarImportacion($filas, $nombreArchivo);
    }

    private function prepararImportacionExcel(string $ruta, string $nombreArchivo): array
    {
        $hojas = LectorExcel::leerHojas($ruta);
        $filas = [];

        foreach ($hojas as $nombreHoja => $filasExcel) {
            $cabeceras = null;
            $filaCabecera = null;

            foreach ($filasExcel as $indice => $filaExcel) {
                $posibles = array_map(fn ($cabecera) => $this->normalizarCabecera((string) $cabecera), $filaExcel);

                if ($this->contarCabecerasTrabajador($posibles) > 0) {
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
            throw new \RuntimeException('No se encontraron trabajadores en el archivo.');
        }

        return $this->validarImportacion($filas, $nombreArchivo);
    }

    private function filaDesdeColumnas(array $cabeceras, array $valores, string $numero, string $origen): array
    {
        $registro = [];

        foreach ($cabeceras as $indice => $cabecera) {
            if ($cabecera !== '') {
                $registro[$cabecera] = trim((string) ($valores[$indice] ?? ''));
            }
        }

        $datos = $this->normalizarRegistroImportacion($registro);
        $datos['origen'] = $origen;

        return [
            'numero' => $numero,
            'datos' => $datos,
            'errores' => [],
            'advertencias' => [],
            'existente' => false,
        ];
    }

    private function contarCabecerasTrabajador(array $cabeceras): int
    {
        $reconocidas = ['codigo_trabajador', 'nombres', 'apellidos', 'nombre_completo', 'area', 'cargo', 'telefono', 'correo'];
        return count(array_intersect($cabeceras, $reconocidas));
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
            'codigo' => 'codigo_trabajador',
            'cod' => 'codigo_trabajador',
            'codigo_trabajador' => 'codigo_trabajador',
            'dni' => 'codigo_trabajador',
            'nombre' => 'nombre_completo',
            'trabajador' => 'nombre_completo',
            'colaborador' => 'nombre_completo',
            'empleado' => 'nombre_completo',
            'responsable' => 'nombre_completo',
            'usuario' => 'nombre_completo',
            'encargado' => 'nombre_completo',
            'asignado_a' => 'nombre_completo',
            'asignado_al_colaborador' => 'nombre_completo',
            'nombre_y_apellidos' => 'nombre_completo',
            'nombres_y_apellidos' => 'nombre_completo',
            'nombres' => 'nombres',
            'apellidos' => 'apellidos',
            'area' => 'area',
            'sede' => 'area',
            'cargo' => 'cargo',
            'puesto' => 'cargo',
            'telefono' => 'telefono',
            'celular' => 'telefono',
            'n_celular' => 'telefono',
            'correo' => 'correo',
            'email' => 'correo',
        ];

        return $alias[$cabecera] ?? $cabecera;
    }

    private function normalizarRegistroImportacion(array $registro): array
    {
        $nombreCompleto = $this->limpiarNombreTrabajador($registro['nombre_completo'] ?? '');
        $nombres = trim((string) ($registro['nombres'] ?? ''));
        $apellidos = trim((string) ($registro['apellidos'] ?? ''));

        if (($nombres === '' || $apellidos === '') && $nombreCompleto !== '') {
            [$nombresDetectados, $apellidosDetectados] = $this->separarNombre($nombreCompleto);
            $nombres = $nombres !== '' ? $nombres : $nombresDetectados;
            $apellidos = $apellidos !== '' ? $apellidos : $apellidosDetectados;
        }

        return [
            'codigo_trabajador' => trim((string) ($registro['codigo_trabajador'] ?? '')),
            'nombres' => $this->formatearNombre($nombres),
            'apellidos' => $this->formatearNombre($apellidos),
            'correo' => trim((string) ($registro['correo'] ?? '')),
            'telefono' => trim((string) ($registro['telefono'] ?? '')),
            'cargo' => trim((string) ($registro['cargo'] ?? '')),
            'area' => trim((string) ($registro['area'] ?? '')),
            'area_id' => 0,
            'origen' => '',
        ];
    }

    private function limpiarNombreTrabajador(string $nombre): string
    {
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre) ?? '');
        $invalidos = ['', '-', '--', 'N/A', 'NA', 'NO ASIGNADO', 'SIN ASIGNAR', 'DISPONIBLE', 'ALMACEN', 'STOCK', 'BAJA'];

        return in_array(strtoupper($nombre), $invalidos, true) ? '' : $nombre;
    }

    private function separarNombre(string $nombreCompleto): array
    {
        if (str_contains($nombreCompleto, ',')) {
            [$apellidos, $nombres] = array_map('trim', explode(',', $nombreCompleto, 2));
            return [$nombres, $apellidos];
        }

        $partes = preg_split('/\s+/', trim($nombreCompleto)) ?: [];
        $cantidad = count($partes);

        if ($cantidad <= 1) {
            return [$nombreCompleto, 'Sin apellido'];
        }

        if ($cantidad === 2) {
            return [$partes[0], $partes[1]];
        }

        return [implode(' ', array_slice($partes, 2)), implode(' ', array_slice($partes, 0, 2))];
    }

    private function formatearNombre(string $valor): string
    {
        $valor = trim(preg_replace('/\s+/', ' ', $valor) ?? '');

        if ($valor === '') {
            return '';
        }

        return mb_convert_case(mb_strtolower($valor, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function validarImportacion(array $filas, string $nombreArchivo): array
    {
        $errores = 0;
        $advertencias = 0;
        $vistos = [];

        foreach ($filas as $indice => $fila) {
            $datos = $fila['datos'];
            $claveNombre = $this->claveNombre($datos['nombres'], $datos['apellidos']);

            if ($datos['nombres'] === '' || $datos['apellidos'] === '') {
                $filas[$indice]['errores'][] = 'Falta nombre o apellido.';
            }

            if ($claveNombre !== '') {
                if (isset($vistos[$claveNombre])) {
                    $filas[$indice]['advertencias'][] = 'Duplicado en el archivo.';
                    $filas[$indice]['existente'] = true;
                }

                $vistos[$claveNombre] = true;
            }

            if ($datos['codigo_trabajador'] !== '' && $this->existeCodigo($datos['codigo_trabajador'])) {
                $filas[$indice]['advertencias'][] = 'Codigo ya existe.';
                $filas[$indice]['existente'] = true;
            }

            if ($claveNombre !== '' && $this->existeNombre($datos['nombres'], $datos['apellidos'])) {
                $filas[$indice]['advertencias'][] = 'Trabajador ya existe.';
                $filas[$indice]['existente'] = true;
            }

            if ($datos['area'] === '') {
                $filas[$indice]['advertencias'][] = 'Sin area.';
            }

            $errores += count($filas[$indice]['errores']);
            $advertencias += count($filas[$indice]['advertencias']);
        }

        return [
            'archivo' => $nombreArchivo,
            'bloqueado' => $errores > 0,
            'resumen' => [
                'total' => count($filas),
                'nuevos' => count(array_filter($filas, fn ($fila) => empty($fila['errores']) && empty($fila['existente']))),
                'existentes' => count(array_filter($filas, fn ($fila) => !empty($fila['existente']))),
                'errores' => $errores,
                'advertencias' => $advertencias,
            ],
            'filas' => $filas,
        ];
    }

    private function guardarImportacionValidada(array $filas): array
    {
        $importados = 0;
        $omitidos = 0;
        $errores = [];

        foreach ($filas as $fila) {
            if (!empty($fila['errores']) || !empty($fila['existente'])) {
                $omitidos++;
                continue;
            }

            try {
                $datos = $fila['datos'];
                $datos['codigo_trabajador'] = $datos['codigo_trabajador'] ?: $this->generarCodigoTrabajador();
                $datos['area_id'] = $this->buscarOCrearArea($datos['area']);
                unset($datos['area'], $datos['origen']);

                $id = $this->modelo->guardar($datos);
                Auditoria::registrar('Trabajadores', 'IMPORTAR', 'trabajador', $id, null, $datos);
                $importados++;
            } catch (Throwable $exception) {
                $errores[] = 'Fila '.$fila['numero'].': '.$exception->getMessage();
            }
        }

        return [$importados, $omitidos, $errores];
    }

    private function existeCodigo(string $codigo): bool
    {
        $consulta = BD::pdo()->prepare('SELECT COUNT(*) FROM trabajadores WHERE codigo_trabajador = ?');
        $consulta->execute([$codigo]);

        return (int) $consulta->fetchColumn() > 0;
    }

    private function existeNombre(string $nombres, string $apellidos): bool
    {
        $consulta = BD::pdo()->prepare(
            'SELECT COUNT(*) FROM trabajadores
             WHERE activo = 1 AND (
                LOWER(CONCAT(nombres, " ", apellidos)) = LOWER(?)
                OR LOWER(CONCAT(apellidos, " ", nombres)) = LOWER(?)
             )'
        );
        $consulta->execute([$nombres.' '.$apellidos, $apellidos.' '.$nombres]);

        return (int) $consulta->fetchColumn() > 0;
    }

    private function claveNombre(string $nombres, string $apellidos): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($nombres.$apellidos)) ?? '');
    }

    private function buscarOCrearArea(string $nombre): ?int
    {
        $nombre = trim($nombre);

        if ($nombre === '') {
            return null;
        }

        $consulta = BD::pdo()->prepare('SELECT id FROM areas WHERE LOWER(nombre) = LOWER(?) LIMIT 1');
        $consulta->execute([$nombre]);
        $id = $consulta->fetchColumn();

        if ($id) {
            return (int) $id;
        }

        $consulta = BD::pdo()->prepare('INSERT INTO areas(nombre, activo) VALUES(?, 1)');
        $consulta->execute([$nombre]);

        return (int) BD::pdo()->lastInsertId();
    }

    private function generarCodigoTrabajador(): string
    {
        $consulta = BD::pdo()->query("SELECT codigo_trabajador FROM trabajadores WHERE codigo_trabajador LIKE 'SOL-IMP-%' ORDER BY codigo_trabajador DESC LIMIT 1");
        $ultimo = (string) ($consulta->fetchColumn() ?: '');
        $numero = 1;

        if (preg_match('/(\d+)$/', $ultimo, $matches)) {
            $numero = ((int) $matches[1]) + 1;
        }

        do {
            $codigo = 'SOL-IMP-'.str_pad((string) $numero, 5, '0', STR_PAD_LEFT);
            $numero++;
        } while ($this->existeCodigo($codigo));

        return $codigo;
    }
}
