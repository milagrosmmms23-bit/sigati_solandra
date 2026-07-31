<?php
declare(strict_types=1);

namespace App\Modelos;

final class Mantenimiento extends ModeloBase
{
    public function listar(): array
    {
        return $this->db
            ->query(
                "SELECT m.*, a.asset_code, t.name type_name, b.name brand_name, mo.name model_name
                 FROM mantenimientos m
                 JOIN activos a ON a.id = m.asset_id
                 JOIN tipos_activo t ON t.id = a.asset_type_id
                 LEFT JOIN marcas b ON b.id = a.brand_id
                 LEFT JOIN modelos mo ON mo.id = a.model_id
                 ORDER BY m.id DESC"
            )
            ->fetchAll();
    }

    public function abrir(array $data, int $user): int
    {
        $statement = $this->db->prepare('CALL sp_abrir_mantenimiento(?,?,?,?,?,?,?,@id)');
        $statement->execute([
            $data['asset_id'],
            $data['type'],
            $data['issue'] ?: null,
            $data['diagnosis'] ?: null,
            $data['actions'] ?: null,
            $data['cost'] ?: 0,
            $user,
        ]);
        $statement->closeCursor();

        return (int) $this->db->query('SELECT @id')->fetchColumn();
    }

    public function cerrar(int $id, array $data, int $user): void
    {
        $statement = $this->db->prepare('CALL sp_cerrar_mantenimiento(?,?,?,?,?,?,?)');
        $statement->execute([
            $id,
            $data['diagnosis'] ?: null,
            $data['actions'] ?: null,
            $data['parts'] ?: null,
            $data['cost'] ?: 0,
            $data['next_date'] ?: null,
            $user,
        ]);
        $statement->closeCursor();
    }
}