<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;
use Throwable;

final class Maintenance extends Model {
    public function all():array{return $this->db->query("SELECT m.*,a.asset_code,t.name type_name,b.name brand_name,mo.name model_name FROM maintenances m JOIN assets a ON a.id=m.asset_id JOIN asset_types t ON t.id=a.asset_type_id LEFT JOIN brands b ON b.id=a.brand_id LEFT JOIN models mo ON mo.id=a.model_id ORDER BY m.id DESC")->fetchAll();}
    public function open(array $d,int $user):int{$s=$this->db->prepare('CALL sp_maintenance_open(?,?,?,?,?,?,?,@id)');$s->execute([$d['asset_id'],$d['type'],$d['issue']?:null,$d['diagnosis']?:null,$d['actions']?:null,$d['cost']?:0,$user]);$s->closeCursor();return (int)$this->db->query('SELECT @id')->fetchColumn();}
    public function close(int $id,array $d,int $user):void{$s=$this->db->prepare('CALL sp_maintenance_close(?,?,?,?,?,?,?)');$s->execute([$id,$d['diagnosis']?:null,$d['actions']?:null,$d['parts']?:null,$d['cost']?:0,$d['next_date']?:null,$user]);$s->closeCursor();}
}
