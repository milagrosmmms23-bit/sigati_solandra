<?php
declare(strict_types=1);

namespace App\Models;

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

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM trabajadores WHERE id = ?');
        $statement->execute([$id]);

        return $statement->fetch() ?: null;
    }

    public function guardar(array $data, ?int $id = null): int
    {
        if ($id) {
            $statement = $this->db->prepare(
                'UPDATE trabajadores
                 SET employee_code = ?, first_name = ?, last_name = ?, email = ?, phone = ?,
                     position = ?, area_id = ?, updated_at = NOW()
                 WHERE id = ?'
            );
            $statement->execute($this->argumentos($data, $id));

            return $id;
        }

        $statement = $this->db->prepare(
            'INSERT INTO trabajadores(employee_code, first_name, last_name, email, phone, position, area_id, active)
             VALUES(?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $statement->execute($this->argumentos($data));

        return (int) $this->db->lastInsertId();
    }

    private function argumentos(array $data, ?int $id = null): array
    {
        $arguments = [
            $data['employee_code'],
            $data['first_name'],
            $data['last_name'],
            $data['email'] ?: null,
            $data['phone'] ?: null,
            $data['position'] ?: null,
            $data['area_id'] ?: null,
        ];

        if ($id) {
            $arguments[] = $id;
        }

        return $arguments;
    }
}