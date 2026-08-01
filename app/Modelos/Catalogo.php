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
            ->query("SELECT * FROM $tabla WHERE active = 1 ORDER BY name")
            ->fetchAll();
    }

    public function crear(string $tabla, array $datos): int
    {
        $this->validarTablaPermitida($tabla);

        if ($tabla === 'tipos_activo') {
            $consulta = $this->db->prepare(
                'INSERT INTO tipos_activo(name, prefix, active) VALUES(?, ?, 1)'
            );
            $consulta->execute([$datos['name'], strtoupper($datos['prefix'])]);

            return (int) $this->db->lastInsertId();
        }

        if ($tabla === 'estados_activo') {
            $codigo = $this->codigoEstado($datos);
            $consulta = $this->db->prepare(
                'INSERT INTO estados_activo(code, name, color, active) VALUES(?, ?, ?, 1)'
            );
            $consulta->execute([$codigo, $datos['name'], $datos['color'] ?? 'secondary']);

            return (int) $this->db->lastInsertId();
        }

        if ($tabla === 'modelos') {
            $consulta = $this->db->prepare(
                'INSERT INTO modelos(brand_id, name, active) VALUES(?, ?, 1)'
            );
            $consulta->execute([$datos['brand_id'] ?: null, $datos['name']]);

            return (int) $this->db->lastInsertId();
        }

        if ($tabla === 'ubicaciones') {
            $consulta = $this->db->prepare(
                'INSERT INTO ubicaciones(area_id, name, active) VALUES(?, ?, 1)'
            );
            $consulta->execute([$datos['area_id'] ?: null, $datos['name']]);

            return (int) $this->db->lastInsertId();
        }

        $consulta = $this->db->prepare("INSERT INTO $tabla(name, active) VALUES(?, 1)");
        $consulta->execute([$datos['name']]);

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
        $codigo = strtoupper(trim((string) ($datos['code'] ?? '')));

        if ($codigo !== '') {
            return trim($codigo, '_');
        }

        $base = iconv('UTF-8', 'ASCII//TRANSLIT', (string) $datos['name']) ?: $datos['name'];
        $codigo = preg_replace('/[^A-Z0-9]+/', '_', strtoupper($base));

        return trim($codigo, '_');
    }
}