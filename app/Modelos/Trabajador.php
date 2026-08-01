<?php
declare(strict_types=1);

namespace App\Modelos;

final class Trabajador extends ModeloBase
{
    public function listar(string $q = ''): array
    {
        $sql = "SELECT e.*, a.nombre nombre_area
                FROM trabajadores e
                LEFT JOIN areas a ON a.id = e.area_id
                WHERE e.activo = 1";
        $params = [];

        if ($q !== '') {
            $sql .= ' AND (e.codigo_trabajador LIKE ? OR CONCAT(e.nombres, " ", e.apellidos) LIKE ? OR e.cargo LIKE ?)';
            $like = "%$q%";
            $params = [$like, $like, $like];
        }

        $sql .= ' ORDER BY e.apellidos, e.nombres';

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
                 SET codigo_trabajador = ?, nombres = ?, apellidos = ?, correo = ?, telefono = ?,
                     cargo = ?, area_id = ?, actualizado_en = NOW()
                 WHERE id = ?'
            );
            $consulta->execute($this->argumentos($datos, $id));

            return $id;
        }

        $consulta = $this->db->prepare(
            'INSERT INTO trabajadores(codigo_trabajador, nombres, apellidos, correo, telefono, cargo, area_id, activo)
             VALUES(?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $consulta->execute($this->argumentos($datos));

        return (int) $this->db->lastInsertId();
    }

    private function argumentos(array $datos, ?int $id = null): array
    {
        $arguments = [
            $datos['codigo_trabajador'],
            $datos['nombres'],
            $datos['apellidos'],
            $datos['correo'] ?: null,
            $datos['telefono'] ?: null,
            $datos['cargo'] ?: null,
            $datos['area_id'] ?: null,
        ];

        if ($id) {
            $arguments[] = $id;
        }

        return $arguments;
    }
}