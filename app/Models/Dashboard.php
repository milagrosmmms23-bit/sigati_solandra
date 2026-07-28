<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;
use Throwable;

final class Dashboard extends Model {
    public function data():array{return ['summary'=>$this->db->query('SELECT * FROM vw_dashboard_summary')->fetch(),'byStatus'=>$this->db->query('SELECT * FROM vw_assets_by_status')->fetchAll(),'byType'=>$this->db->query('SELECT * FROM vw_assets_by_type')->fetchAll(),'byArea'=>$this->db->query('SELECT * FROM vw_assets_by_area LIMIT 10')->fetchAll(),'recent'=>$this->db->query("SELECT am.*,a.asset_code,u.name user_name FROM asset_movements am JOIN assets a ON a.id=am.asset_id LEFT JOIN users u ON u.id=am.user_id ORDER BY am.id DESC LIMIT 8")->fetchAll()];}
}
