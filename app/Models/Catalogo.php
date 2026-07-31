<?php
declare(strict_types=1);

namespace App\Models;

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

    public function all(string $table): array
    {
        $this->ensureAllowed($table);

        return $this->db
            ->query("SELECT * FROM $table WHERE active = 1 ORDER BY name")
            ->fetchAll();
    }

    public function create(string $table, array $data): int
    {
        $this->ensureAllowed($table);

        if ($table === 'tipos_activo') {
            $statement = $this->db->prepare(
                'INSERT INTO tipos_activo(name, prefix, active) VALUES(?, ?, 1)'
            );
            $statement->execute([$data['name'], strtoupper($data['prefix'])]);

            return (int) $this->db->lastInsertId();
        }

        if ($table === 'estados_activo') {
            $code = $this->statusCode($data);
            $statement = $this->db->prepare(
                'INSERT INTO estados_activo(code, name, color, active) VALUES(?, ?, ?, 1)'
            );
            $statement->execute([$code, $data['name'], $data['color'] ?? 'secondary']);

            return (int) $this->db->lastInsertId();
        }

        if ($table === 'modelos') {
            $statement = $this->db->prepare(
                'INSERT INTO modelos(brand_id, name, active) VALUES(?, ?, 1)'
            );
            $statement->execute([$data['brand_id'] ?: null, $data['name']]);

            return (int) $this->db->lastInsertId();
        }

        if ($table === 'ubicaciones') {
            $statement = $this->db->prepare(
                'INSERT INTO ubicaciones(area_id, name, active) VALUES(?, ?, 1)'
            );
            $statement->execute([$data['area_id'] ?: null, $data['name']]);

            return (int) $this->db->lastInsertId();
        }

        $statement = $this->db->prepare("INSERT INTO $table(name, active) VALUES(?, 1)");
        $statement->execute([$data['name']]);

        return (int) $this->db->lastInsertId();
    }

    private function ensureAllowed(string $table): void
    {
        if (!isset($this->allowed[$table])) {
            throw new InvalidArgumentException('Catálogo inválido');
        }
    }

    private function statusCode(array $data): string
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));

        if ($code !== '') {
            return trim($code, '_');
        }

        $base = iconv('UTF-8', 'ASCII//TRANSLIT', (string) $data['name']) ?: $data['name'];
        $code = preg_replace('/[^A-Z0-9]+/', '_', strtoupper($base));

        return trim($code, '_');
    }
}