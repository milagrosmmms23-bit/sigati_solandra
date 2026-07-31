<?php
declare(strict_types=1);

namespace App\Models;

use Throwable;

final class Asignacion extends ModeloBase
{
    public function all(): array
    {
        return $this->db
            ->query("SELECT a.*, CONCAT(e.first_name,' ',e.last_name) employee_name, ar.name area_name, COUNT(ai.id) item_count
                     FROM asignaciones a
                     JOIN trabajadores e ON e.id = a.employee_id
                     LEFT JOIN areas ar ON ar.id = a.area_id
                     LEFT JOIN items_asignacion ai ON ai.assignment_id = a.id
                     GROUP BY a.id
                     ORDER BY a.id DESC")
            ->fetchAll();
    }

    public function active(): array
    {
        return $this->db
            ->query("SELECT a.id, a.assignment_number, CONCAT(e.first_name,' ',e.last_name) employee_name,
                            SUM(ai.returned_at IS NULL) pending
                     FROM asignaciones a
                     JOIN trabajadores e ON e.id = a.employee_id
                     JOIN items_asignacion ai ON ai.assignment_id = a.id
                     WHERE a.status IN('CONFIRMADA','PARCIAL')
                     GROUP BY a.id
                     HAVING pending > 0
                     ORDER BY a.id DESC")
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            "SELECT a.*, CONCAT(e.first_name,' ',e.last_name) employee_name, e.employee_code, e.position,
                    ar.name area_name, u.name created_by_name
             FROM asignaciones a
             JOIN trabajadores e ON e.id = a.employee_id
             LEFT JOIN areas ar ON ar.id = a.area_id
             LEFT JOIN usuarios u ON u.id = a.created_by
             WHERE a.id = ?"
        );
        $statement->execute([$id]);
        $assignment = $statement->fetch();

        if (!$assignment) {
            return null;
        }

        $statement = $this->db->prepare(
            "SELECT ai.*, x.asset_code, x.serial_number, x.phone_number, x.imei1, x.imei2, x.notes asset_notes,
                    t.name type_name, b.name brand_name, m.name model_name,
                    (
                        SELECT GROUP_CONCAT(CONCAT(s.spec_key, ' ', s.spec_value) SEPARATOR ', ')
                        FROM especificaciones_activo s
                        WHERE s.asset_id = x.id
                    ) specs_text
             FROM items_asignacion ai
             JOIN activos x ON x.id = ai.asset_id
             JOIN tipos_activo t ON t.id = x.asset_type_id
             LEFT JOIN marcas b ON b.id = x.brand_id
             LEFT JOIN modelos m ON m.id = x.model_id
             WHERE ai.assignment_id = ?
             ORDER BY ai.id"
        );
        $statement->execute([$id]);
        $assignment['items'] = $statement->fetchAll();

        return $assignment;
    }

    public function create(int $employee, ?int $area, string $notes, array $items, int $user): int
    {
        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare('CALL sp_crear_asignacion(?,?,?,?,@id,@number)');
            $statement->execute([$employee, $area, $notes, $user]);
            $statement->closeCursor();
            $id = (int) $this->db->query('SELECT @id')->fetchColumn();

            foreach ($items as $item) {
                $statement = $this->db->prepare('CALL sp_agregar_activo_asignacion(?,?,?,?)');
                $statement->execute([$id, $item['asset_id'], $item['condition'], $user]);
                $statement->closeCursor();
            }

            $statement = $this->db->prepare('CALL sp_confirmar_asignacion(?,?)');
            $statement->execute([$id, $user]);
            $statement->closeCursor();
            $this->db->commit();

            return $id;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }
}
