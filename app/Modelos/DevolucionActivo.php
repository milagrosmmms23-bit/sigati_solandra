<?php
declare(strict_types=1);

namespace App\Modelos;

use Throwable;

final class DevolucionActivo extends ModeloBase
{
    public function listar(): array
    {
        return $this->db
            ->query(
                "SELECT r.*, a.numero_asignacion, CONCAT(e.nombres, ' ', e.apellidos) nombre_trabajador,
                        COUNT(ri.id) cantidad_items
                 FROM devoluciones_activo r
                 JOIN asignaciones a ON a.id = r.asignacion_id
                 JOIN trabajadores e ON e.id = a.trabajador_id
                 LEFT JOIN items_devolucion ri ON ri.devolucion_id = r.id
                 GROUP BY r.id
                 ORDER BY r.id DESC"
            )
            ->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $consulta = $this->db->prepare(
            "SELECT r.*, a.numero_asignacion, CONCAT(e.nombres, ' ', e.apellidos) nombre_trabajador,
                    e.codigo_trabajador, e.cargo, ar.nombre nombre_area, u.nombre nombre_creador
             FROM devoluciones_activo r
             JOIN asignaciones a ON a.id = r.asignacion_id
             JOIN trabajadores e ON e.id = a.trabajador_id
             LEFT JOIN areas ar ON ar.id = a.area_id
             LEFT JOIN usuarios u ON u.id = r.creado_por
             WHERE r.id = ?"
        );
        $consulta->execute([$id]);
        $devolucion = $consulta->fetch();

        if (!$devolucion) {
            return null;
        }

        $consulta = $this->db->prepare(
            "SELECT ri.*, x.codigo_activo, x.numero_serie, t.nombre nombre_tipo, b.nombre nombre_marca,
                    m.nombre nombre_modelo, st.nombre nombre_siguiente_estado
             FROM items_devolucion ri
             JOIN items_asignacion ai ON ai.id = ri.item_asignacion_id
             JOIN activos x ON x.id = ai.activo_id
             JOIN tipos_activo t ON t.id = x.tipo_activo_id
             LEFT JOIN marcas b ON b.id = x.marca_id
             LEFT JOIN modelos m ON m.id = x.modelo_id
             JOIN estados_activo st ON st.id = ri.siguiente_estado_id
             WHERE ri.devolucion_id = ?"
        );
        $consulta->execute([$id]);
        $devolucion['elementos'] = $consulta->fetchAll();

        return $devolucion;
    }

    public function crear(int $asignacionId, string $observaciones, array $elementos, int $usuarioId): int
    {
        $this->db->beginTransaction();

        try {
            $consulta = $this->db->prepare('CALL sp_crear_devolucion(?,?,?,@id,@number)');
            $consulta->execute([$asignacionId, $observaciones, $usuarioId]);
            $consulta->closeCursor();

            $id = (int) $this->db->query('SELECT @id')->fetchColumn();

            foreach ($elementos as $registro) {
                $consulta = $this->db->prepare('CALL sp_devolver_activo(?,?,?,?,?,?)');
                $consulta->execute([
                    $id,
                    $registro['item_asignacion_id'],
                    $registro['condicion'],
                    $registro['danos'] ?: null,
                    $registro['estado_id'],
                    $usuarioId,
                ]);
                $consulta->closeCursor();
            }

            $consulta = $this->db->prepare('CALL sp_confirmar_devolucion(?,?)');
            $consulta->execute([$id, $usuarioId]);
            $consulta->closeCursor();

            $this->db->commit();
            return $id;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }
}