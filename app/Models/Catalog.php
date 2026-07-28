<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;
use Throwable;

final class Catalog extends Model {
    public array $allowed=['areas'=>'Áreas','locations'=>'Ubicaciones','asset_types'=>'Tipos de activo','asset_statuses'=>'Estados','brands'=>'Marcas','models'=>'Modelos','suppliers'=>'Proveedores'];
    public function all(string $table):array{if(!isset($this->allowed[$table]))throw new \InvalidArgumentException('Catálogo inválido');return $this->db->query("SELECT * FROM $table WHERE active=1 ORDER BY name")->fetchAll();}
    public function create(string $table,array $d):int{
        if(!isset($this->allowed[$table]))throw new \InvalidArgumentException('Catálogo inválido');
        if($table==='asset_types'){$s=$this->db->prepare('INSERT INTO asset_types(name,prefix,active) VALUES(?,?,1)');$s->execute([$d['name'],strtoupper($d['prefix'])]);}
        elseif($table==='asset_statuses'){$code=strtoupper(trim((string)($d['code']??'')));if($code===''){$base=iconv('UTF-8','ASCII//TRANSLIT',(string)$d['name'])?:$d['name'];$code=preg_replace('/[^A-Z0-9]+/','_',strtoupper($base));}$s=$this->db->prepare('INSERT INTO asset_statuses(code,name,color,active) VALUES(?,?,?,1)');$s->execute([trim($code,'_'),$d['name'],$d['color']??'secondary']);}
        elseif($table==='models'){$s=$this->db->prepare('INSERT INTO models(brand_id,name,active) VALUES(?,?,1)');$s->execute([$d['brand_id']?:null,$d['name']]);}
        elseif($table==='locations'){$s=$this->db->prepare('INSERT INTO locations(area_id,name,active) VALUES(?,?,1)');$s->execute([$d['area_id']?:null,$d['name']]);}
        else{$s=$this->db->prepare("INSERT INTO $table(name,active) VALUES(?,1)");$s->execute([$d['name']]);}
        return (int)$this->db->lastInsertId();
    }
}
