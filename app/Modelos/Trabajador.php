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
        $consulta = $this->db->prepare(
            'SELECT e.*, a.nombre nombre_area
             FROM trabajadores e
             LEFT JOIN areas a ON a.id = e.area_id
             WHERE e.id = ?'
        );
        $consulta->execute([$id]);

        return $consulta->fetch() ?: null;
    }

    public function activosAsignados(int $id): array
    {
        $consulta = $this->db->prepare(
            "SELECT a.*, t.nombre nombre_tipo, s.nombre nombre_estado, b.nombre nombre_marca,
                    m.nombre nombre_modelo, ar.nombre nombre_area
             FROM activos a
             JOIN tipos_activo t ON t.id = a.tipo_activo_id
             JOIN estados_activo s ON s.id = a.estado_id
             LEFT JOIN marcas b ON b.id = a.marca_id
             LEFT JOIN modelos m ON m.id = a.modelo_id
             LEFT JOIN areas ar ON ar.id = a.area_actual_id
             WHERE a.activo = 1
               AND a.trabajador_actual_id = ?
             ORDER BY t.nombre, a.codigo_activo"
        );
        $consulta->execute([$id]);

        return $consulta->fetchAll();
    }

    public function asignaciones(int $id): array
    {
        $consulta = $this->db->prepare(
            "SELECT ag.*, ar.nombre nombre_area, COUNT(ai.id) total_activos
             FROM asignaciones ag
             LEFT JOIN areas ar ON ar.id = ag.area_id
             LEFT JOIN items_asignacion ai ON ai.asignacion_id = ag.id
             WHERE ag.trabajador_id = ?
             GROUP BY ag.id, ar.nombre
             ORDER BY ag.id DESC
             LIMIT 20"
        );
        $consulta->execute([$id]);

        return $consulta->fetchAll();
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
