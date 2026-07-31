<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;
use Throwable;

final class DevolucionActivo extends ModeloBase {
    public function all():array{return $this->db->query("SELECT r.*,a.assignment_number,CONCAT(e.first_name,' ',e.last_name) employee_name,COUNT(ri.id) item_count FROM devoluciones_activo r JOIN asignaciones a ON a.id=r.assignment_id JOIN trabajadores e ON e.id=a.employee_id LEFT JOIN items_devolucion ri ON ri.return_id=r.id GROUP BY r.id ORDER BY r.id DESC")->fetchAll();}
    public function find(int $id):?array{$s=$this->db->prepare("SELECT r.*,a.assignment_number,CONCAT(e.first_name,' ',e.last_name) employee_name,e.employee_code,e.position,ar.name area_name,u.name created_by_name FROM devoluciones_activo r JOIN asignaciones a ON a.id=r.assignment_id JOIN trabajadores e ON e.id=a.employee_id LEFT JOIN areas ar ON ar.id=a.area_id LEFT JOIN usuarios u ON u.id=r.created_by WHERE r.id=?");$s->execute([$id]);$r=$s->fetch();if(!$r)return null;$s=$this->db->prepare("SELECT ri.*,x.asset_code,x.serial_number,t.name type_name,b.name brand_name,m.name model_name,st.name next_status_name FROM items_devolucion ri JOIN items_asignacion ai ON ai.id=ri.assignment_item_id JOIN activos x ON x.id=ai.asset_id JOIN tipos_activo t ON t.id=x.asset_type_id LEFT JOIN marcas b ON b.id=x.brand_id LEFT JOIN modelos m ON m.id=x.model_id JOIN estados_activo st ON st.id=ri.next_status_id WHERE ri.return_id=?");$s->execute([$id]);$r['items']=$s->fetchAll();return $r;}
    public function create(int $assignment,string $notes,array $items,int $user):int{$this->db->beginTransaction();try{$s=$this->db->prepare('CALL sp_crear_devolucion(?,?,?,@id,@number)');$s->execute([$assignment,$notes,$user]);$s->closeCursor();$id=(int)$this->db->query('SELECT @id')->fetchColumn();foreach($items as $i){$s=$this->db->prepare('CALL sp_devolver_activo(?,?,?,?,?,?)');$s->execute([$id,$i['item_id'],$i['condition'],$i['damage']?:null,$i['status_id'],$user]);$s->closeCursor();}$s=$this->db->prepare('CALL sp_confirmar_devolucion(?,?)');$s->execute([$id,$user]);$s->closeCursor();$this->db->commit();return $id;}catch(Throwable $e){$this->db->rollBack();throw $e;}}
}
