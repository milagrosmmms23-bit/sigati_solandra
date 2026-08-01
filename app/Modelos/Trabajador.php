<?php
declare(strict_types=1);

namespace App\Modelos;

final class Trabajador extends ModeloBase
{
    public function listar(string $q = ''): array
    {
        $sql = "SELECT e.*, a.name area_name
                FROM trabajadores e
                LEFT JOIN areas a ON a.id = e.area_id
                WHERE e.active = 1";
        $params = [];

        if ($q !== '') {
            $sql .= ' AND (e.employee_code LIKE ? OR CONCAT(e.first_name, " ", e.last_name) LIKE ? OR e.position LIKE ?)';
            $like = "%$q%";
            $params = [$like, $like, $like];
        }

        $sql .= ' ORDER BY e.last_name, e.first_name';

        $consulta = $this->db->prepare($sql);
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $consulta = $this->db->prepare('SELECT * FROM trabajadores WHERE id = ?');
        $consulta->execute([$id]);

        return $consulta->fetch() ?: null;
    }

    public function guardar(array $datos, ?int $id = null): int
    {
        if ($id) {
            $consulta = $this->db->prepare(
                'UPDATE trabajadores
                 SET employee_code = ?, first_name = ?, last_name = ?, email = ?, phone = ?,
                     position = ?, area_id = ?, updated_at = NOW()
                 WHERE id = ?'
            );
            $consulta->execute($this->argumentos($datos, $id));

            return $id;
        }

        $consulta = $this->db->prepare(
            'INSERT INTO trabajadores(employee_code, first_name, last_name, email, phone, position, area_id, active)
             VALUES(?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $consulta->execute($this->argumentos($datos));

        return (int) $this->db->lastInsertId();
    }

    private function argumentos(array $datos, ?int $id = null): array
    {
        $arguments = [
            $datos['employee_code'],
            $datos['first_name'],
            $datos['last_name'],
            $datos['email'] ?: null,
            $datos['phone'] ?: null,
            $datos['position'] ?: null,
            $datos['area_id'] ?: null,
        ];

        if ($id) {
            $arguments[] = $id;
        }

        return $arguments;
    }
}