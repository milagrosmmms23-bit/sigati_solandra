<?php
declare(strict_types=1);

namespace App\Modelos;

use Throwable;

final class Asignacion extends ModeloBase
{
    public function listar(): array
    {
        return $this->db
            ->query("SELECT a.*, CONCAT(e.nombres,' ',e.apellidos) nombre_trabajador, ar.nombre nombre_area, COUNT(ai.id) cantidad_items
                     FROM asignaciones a
                     JOIN trabajadores e ON e.id = a.trabajador_id
                     LEFT JOIN areas ar ON ar.id = a.area_id
                     LEFT JOIN items_asignacion ai ON ai.asignacion_id = a.id
                     GROUP BY a.id
                     ORDER BY a.id DESC")
            ->fetchAll();
    }

    public function activas(): array
    {
        return $this->db
            ->query("SELECT a.id, a.numero_asignacion, CONCAT(e.nombres,' ',e.apellidos) nombre_trabajador,
                            SUM(ai.devuelto_en IS NULL) pendientes
                     FROM asignaciones a
                     JOIN trabajadores e ON e.id = a.trabajador_id
                     JOIN items_asignacion ai ON ai.asignacion_id = a.id
                     WHERE a.estado IN('CONFIRMADA','PARCIAL')
                     GROUP BY a.id
                     HAVING pendientes > 0
                     ORDER BY a.id DESC")
            ->fetchAll();
    }
    public function candidatosDesdeExcel(): array
    {
        return $this->db
            ->query(
                "SELECT a.id activo_id, a.codigo_activo, a.codigo_anterior, a.numero_serie, a.imei1,
                        a.numero_telefono, a.area_actual_id, t.nombre nombre_tipo, b.nombre nombre_marca,
                        m.nombre nombre_modelo, ar.nombre nombre_area, s.codigo codigo_estado,
                        s.nombre nombre_estado, resp.valor_especificacion responsable_excel,
                        EXISTS (
                            SELECT 1
                            FROM items_asignacion ia
                            JOIN asignaciones ag ON ag.id = ia.asignacion_id
                            WHERE ia.activo_id = a.id
                              AND ia.devuelto_en IS NULL
                              AND ag.estado IN ('CONFIRMADA','PARCIAL')
                        ) tiene_acta_vigente
                 FROM especificaciones_activo resp
                 JOIN activos a ON a.id = resp.activo_id
                 JOIN tipos_activo t ON t.id = a.tipo_activo_id
                 JOIN estados_activo s ON s.id = a.estado_id
                 LEFT JOIN marcas b ON b.id = a.marca_id
                 LEFT JOIN modelos m ON m.id = a.modelo_id
                 LEFT JOIN areas ar ON ar.id = a.area_actual_id
                 WHERE a.activo = 1
                   AND resp.clave_especificacion = 'Responsable en Excel'
                   AND NULLIF(TRIM(resp.valor_especificacion), '') IS NOT NULL
                 ORDER BY resp.valor_especificacion, t.nombre, a.codigo_activo"
            )
            ->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $consulta = $this->db->prepare(
            "SELECT a.*, CONCAT(e.nombres,' ',e.apellidos) nombre_trabajador, e.codigo_trabajador, e.cargo,
                    ar.nombre nombre_area, u.nombre nombre_creador
             FROM asignaciones a
             JOIN trabajadores e ON e.id = a.trabajador_id
             LEFT JOIN areas ar ON ar.id = a.area_id
             LEFT JOIN usuarios u ON u.id = a.creado_por
             WHERE a.id = ?"
        );
        $consulta->execute([$id]);
        $asignacion = $consulta->fetch();

        if (!$asignacion) {
            return null;
        }

        $consulta = $this->db->prepare(
            "SELECT ai.*, x.codigo_activo, x.numero_serie, x.numero_telefono, x.imei1, x.imei2, x.observaciones observaciones_activo,
                    t.nombre nombre_tipo, b.nombre nombre_marca, m.nombre nombre_modelo,
                    (
                        SELECT GROUP_CONCAT(CONCAT(s.clave_especificacion, ' ', s.valor_especificacion) SEPARATOR ', ')
                        FROM especificaciones_activo s
                        WHERE s.activo_id = x.id
                    ) texto_especificaciones
             FROM items_asignacion ai
             JOIN activos x ON x.id = ai.activo_id
             JOIN tipos_activo t ON t.id = x.tipo_activo_id
             LEFT JOIN marcas b ON b.id = x.marca_id
             LEFT JOIN modelos m ON m.id = x.modelo_id
             WHERE ai.asignacion_id = ?
             ORDER BY ai.id"
        );
        $consulta->execute([$id]);
        $asignacion['elementos'] = $consulta->fetchAll();

        return $asignacion;
    }

    public function crear(int $trabajadorId, ?int $areaId, string $observaciones, array $elementos, int $usuarioId): int
    {
        $this->db->beginTransaction();

        try {
            $consulta = $this->db->prepare('CALL sp_crear_asignacion(?,?,?,?,@id,@number)');
            $consulta->execute([$trabajadorId, $areaId, $observaciones, $usuarioId]);
            $consulta->closeCursor();
            $id = (int) $this->db->query('SELECT @id')->fetchColumn();

            foreach ($elementos as $registro) {
                $consulta = $this->db->prepare('CALL sp_agregar_activo_asignacion(?,?,?,?)');
                $consulta->execute([$id, $registro['activo_id'], $registro['condicion'], $usuarioId]);
                $consulta->closeCursor();
            }

            $consulta = $this->db->prepare('CALL sp_confirmar_asignacion(?,?)');
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
