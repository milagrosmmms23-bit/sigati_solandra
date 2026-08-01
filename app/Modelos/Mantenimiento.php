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

    public function abrir(array $datos, int $usuarioId): int
    {
        $consulta = $this->db->prepare('CALL sp_abrir_mantenimiento(?,?,?,?,?,?,?,@id)');
        $consulta->execute([
            $datos['asset_id'],
            $datos['tipo'],
            $datos['problema'] ?: null,
            $datos['diagnostico'] ?: null,
            $datos['acciones'] ?: null,
            $datos['costo'] ?: 0,
            $usuarioId,
        ]);
        $consulta->closeCursor();

        return (int) $this->db->query('SELECT @id')->fetchColumn();
    }

    public function cerrar(int $id, array $datos, int $usuarioId): void
    {
        $consulta = $this->db->prepare('CALL sp_cerrar_mantenimiento(?,?,?,?,?,?,?)');
        $consulta->execute([
            $id,
            $datos['diagnostico'] ?: null,
            $datos['acciones'] ?: null,
            $datos['repuestos'] ?: null,
            $datos['costo'] ?: 0,
            $datos['proxima_fecha'] ?: null,
            $usuarioId,
        ]);
        $consulta->closeCursor();
    }
}