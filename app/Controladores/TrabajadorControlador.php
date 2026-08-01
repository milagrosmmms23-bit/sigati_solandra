<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Nucleo\{Auth, Auditoria, Controlador, Csrf, Flash};
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
}