<?php
declare(strict_types=1);

namespace App\Modelos;

final class Panel extends ModeloBase
{
    public function datos(): array
    {
        $resumen = $this->db->query('SELECT * FROM vw_resumen_panel')->fetch() ?: [];

        return [
            'resumen' => $this->mapearResumen($resumen),
            'porEstado' => $this->db->query('SELECT * FROM vw_activos_por_estado')->fetchAll(),
            'porTipo' => $this->db->query('SELECT * FROM vw_activos_por_tipo')->fetchAll(),
            'porArea' => $this->db->query('SELECT * FROM vw_activos_por_area LIMIT 10')->fetchAll(),
            'recientes' => $this->movimientosRecientes(),
        ];
    }

    private function mapearResumen(array $resumen): array
    {
        return [
            'total_activos' => $resumen['total_activos'] ?? 0,
            'activos_asignados' => $resumen['activos_asignados'] ?? 0,
            'activos_disponibles' => $resumen['activos_disponibles'] ?? 0,
            'activos_mantenimiento' => $resumen['activos_mantenimiento'] ?? 0,
            'total_trabajadores' => $resumen['total_trabajadores'] ?? 0,
            'asignaciones_activas' => $resumen['asignaciones_activas'] ?? 0,
        ];
    }

    private function movimientosRecientes(): array
    {
        return $this->db
            ->query(
                "SELECT am.*, a.codigo_activo, u.nombre nombre_usuario
                 FROM movimientos_activo am
                 JOIN activos a ON a.id = am.activo_id
                 LEFT JOIN usuarios u ON u.id = am.usuario_id
                 ORDER BY am.id DESC
                 LIMIT 8"
            )
            ->fetchAll();
    }
}