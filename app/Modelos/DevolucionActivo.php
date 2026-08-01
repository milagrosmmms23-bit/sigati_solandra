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
                "SELECT r.*, a.assignment_number, CONCAT(e.first_name, ' ', e.last_name) employee_name,
                        COUNT(ri.id) item_count
                 FROM devoluciones_activo r
                 JOIN asignaciones a ON a.id = r.assignment_id
                 JOIN trabajadores e ON e.id = a.employee_id
                 LEFT JOIN items_devolucion ri ON ri.return_id = r.id
                 GROUP BY r.id
                 ORDER BY r.id DESC"
            )
            ->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $consulta = $this->db->prepare(
            "SELECT r.*, a.assignment_number, CONCAT(e.first_name, ' ', e.last_name) employee_name,
                    e.employee_code, e.position, ar.name area_name, u.name created_by_name
             FROM devoluciones_activo r
             JOIN asignaciones a ON a.id = r.assignment_id
             JOIN trabajadores e ON e.id = a.employee_id
             LEFT JOIN areas ar ON ar.id = a.area_id
             LEFT JOIN usuarios u ON u.id = r.created_by
             WHERE r.id = ?"
        );
        $consulta->execute([$id]);
        $devolucion = $consulta->fetch();

        if (!$devolucion) {
            return null;
        }

        $consulta = $this->db->prepare(
            "SELECT ri.*, x.asset_code, x.serial_number, t.name type_name, b.name brand_name,
                    m.name model_name, st.name next_status_name
             FROM items_devolucion ri
             JOIN items_asignacion ai ON ai.id = ri.assignment_item_id
             JOIN activos x ON x.id = ai.asset_id
             JOIN tipos_activo t ON t.id = x.asset_type_id
             LEFT JOIN marcas b ON b.id = x.brand_id
             LEFT JOIN modelos m ON m.id = x.model_id
             JOIN estados_activo st ON st.id = ri.next_status_id
             WHERE ri.return_id = ?"
        );
        $consulta->execute([$id]);
        $devolucion['items'] = $consulta->fetchAll();

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
                    $registro['item_id'],
                    $registro['condition'],
                    $registro['damage'] ?: null,
                    $registro['status_id'],
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