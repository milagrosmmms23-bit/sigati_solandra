<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;
use Throwable;

final class Employee extends Model {
    public function all(string $q=''):array{$sql='SELECT e.*,a.name area_name FROM employees e LEFT JOIN areas a ON a.id=e.area_id WHERE e.active=1';$p=[];if($q!==''){$sql.=' AND (e.employee_code LIKE ? OR CONCAT(e.first_name," ",e.last_name) LIKE ? OR e.position LIKE ?)';$x="%$q%";$p=[$x,$x,$x];}$sql.=' ORDER BY e.last_name,e.first_name';$s=$this->db->prepare($sql);$s->execute($p);return $s->fetchAll();}
    public function find(int $id):?array{$s=$this->db->prepare('SELECT * FROM employees WHERE id=?');$s->execute([$id]);return $s->fetch()?:null;}
    public function save(array $d,?int $id=null):int{if($id){$s=$this->db->prepare('UPDATE employees SET employee_code=?,first_name=?,last_name=?,email=?,phone=?,position=?,area_id=?,updated_at=NOW() WHERE id=?');$s->execute([$d['employee_code'],$d['first_name'],$d['last_name'],$d['email']?:null,$d['phone']?:null,$d['position']?:null,$d['area_id']?:null,$id]);return $id;}$s=$this->db->prepare('INSERT INTO employees(employee_code,first_name,last_name,email,phone,position,area_id,active) VALUES(?,?,?,?,?,?,?,1)');$s->execute([$d['employee_code'],$d['first_name'],$d['last_name'],$d['email']?:null,$d['phone']?:null,$d['position']?:null,$d['area_id']?:null]);return (int)$this->db->lastInsertId();}
}
