<?php
declare(strict_types=1);

namespace App\Modelos;

use InvalidArgumentException;

final class Catalogo extends ModeloBase
{
    public array $allowed = [
        'areas' => 'Áreas',
        'ubicaciones' => 'Ubicaciones',
        'tipos_activo' => 'Tipos de activo',
        'estados_activo' => 'Estados',
        'marcas' => 'Marcas',
        'modelos' => 'Modelos',
        'proveedores' => 'Proveedores',
    ];

    public function listar(string $tabla): array
    {
        $this->validarTablaPermitida($tabla);

        return $this->db
            ->query("SELECT * FROM $tabla WHERE activo = 1 ORDER BY nombre")
            ->fetchAll();
    }

    public function crear(string $tabla, array $datos): int
    {
        $this->validarTablaPermitida($tabla);

        if ($tabla === 'tipos_activo') {
            $consulta = $this->db->prepare(
                'INSERT INTO tipos_activo(nombre, prefijo, activo) VALUES(?, ?, 1)'
            );
            $consulta->execute([$datos['nombre'], strtoupper($datos['prefijo'])]);

            return (int) $this->db->lastInsertId();
        }

        if ($tabla === 'estados_activo') {
            $codigo = $this->codigoEstado($datos);
            $consulta = $this->db->prepare(
                'INSERT INTO estados_activo(codigo, nombre, color, activo) VALUES(?, ?, ?, 1)'
            );
            $consulta->execute([$codigo, $datos['nombre'], $datos['color'] ?? 'secondary']);

            return (int) $this->db->lastInsertId();
        }

        if ($tabla === 'modelos') {
            $consulta = $this->db->prepare(
                'INSERT INTO modelos(marca_id, nombre, activo) VALUES(?, ?, 1)'
            );
            $consulta->execute([$datos['marca_id'] ?: null, $datos['nombre']]);

            return (int) $this->db->lastInsertId();
        }

        if ($tabla === 'ubicaciones') {
            $consulta = $this->db->prepare(
                'INSERT INTO ubicaciones(area_id, nombre, activo) VALUES(?, ?, 1)'
            );
            $consulta->execute([$datos['area_id'] ?: null, $datos['nombre']]);

            return (int) $this->db->lastInsertId();
        }

        $consulta = $this->db->prepare("INSERT INTO $tabla(nombre, activo) VALUES(?, 1)");
        $consulta->execute([$datos['nombre']]);

        return (int) $this->db->lastInsertId();
    }

    private function validarTablaPermitida(string $tabla): void
    {
        if (!isset($this->allowed[$tabla])) {
            throw new InvalidArgumentException('Catálogo inválido');
        }
    }

    private function codigoEstado(array $datos): string
    {
        $codigo = strtoupper(trim((string) ($datos['codigo'] ?? '')));

        if ($codigo !== '') {
            return trim($codigo, '_');
        }

        $base = iconv('UTF-8', 'ASCII//TRANSLIT', (string) $datos['nombre']) ?: $datos['nombre'];
        $codigo = preg_replace('/[^A-Z0-9]+/', '_', strtoupper($base));

        return trim($codigo, '_');
    }
}