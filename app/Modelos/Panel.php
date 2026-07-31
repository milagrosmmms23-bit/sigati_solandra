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
            'total_activos' => $resumen['total_assets'] ?? 0,
            'activos_asignados' => $resumen['assigned_assets'] ?? 0,
            'activos_disponibles' => $resumen['available_assets'] ?? 0,
            'activos_mantenimiento' => $resumen['maintenance_assets'] ?? 0,
            'total_trabajadores' => $resumen['total_employees'] ?? 0,
            'asignaciones_activas' => $resumen['active_assignments'] ?? 0,
        ];
    }

    private function movimientosRecientes(): array
    {
        return $this->db
            ->query(
                "SELECT am.*, a.asset_code, u.name user_name
                 FROM movimientos_activo am
                 JOIN activos a ON a.id = am.asset_id
                 LEFT JOIN usuarios u ON u.id = am.user_id
                 ORDER BY am.id DESC
                 LIMIT 8"
            )
            ->fetchAll();
    }
}