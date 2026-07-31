<?php
declare(strict_types=1);

namespace App\Models;

final class Panel extends ModeloBase
{
    public function data(): array
    {
        return [
            'summary' => $this->db->query('SELECT * FROM vw_resumen_panel')->fetch(),
            'byStatus' => $this->db->query('SELECT * FROM vw_activos_por_estado')->fetchAll(),
            'byType' => $this->db->query('SELECT * FROM vw_activos_por_tipo')->fetchAll(),
            'byArea' => $this->db->query('SELECT * FROM vw_activos_por_area LIMIT 10')->fetchAll(),
            'recent' => $this->recentMovements(),
        ];
    }

    private function recentMovements(): array
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