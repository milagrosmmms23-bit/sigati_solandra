<?php
declare(strict_types=1);

namespace App\Modelos;

use Throwable;

final class Activo extends ModeloBase
{
    public function listar(array $filtros, int $pagina, int $porPagina): array
    {
        [$where, $params] = $this->armarFiltros($filtros);
        $offset = ($pagina - 1) * $porPagina;

        $count = $this->db->prepare("SELECT COUNT(*) FROM activos a WHERE $where");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $consulta = $this->db->prepare(
            "SELECT a.*, t.name type_name, s.name status_name, s.color,
                    b.name brand_name, m.name model_name, ar.name area_name,
                    l.name location_name, CONCAT(e.first_name, ' ', e.last_name) employee_name
             FROM activos a
             JOIN tipos_activo t ON t.id = a.asset_type_id
             JOIN estados_activo s ON s.id = a.status_id
             LEFT JOIN marcas b ON b.id = a.brand_id
             LEFT JOIN modelos m ON m.id = a.model_id
             LEFT JOIN areas ar ON ar.id = a.current_area_id
             LEFT JOIN ubicaciones l ON l.id = a.location_id
             LEFT JOIN trabajadores e ON e.id = a.current_employee_id
             WHERE $where
             ORDER BY a.id DESC
             LIMIT $porPagina OFFSET $offset"
        );
        $consulta->execute($params);

        return [
            'filas' => $consulta->fetchAll(),
            'total' => $total,
            'pagina' => $pagina,
            'paginas' => (int) ceil($total / $porPagina),
        ];
    }

    public function buscar(int $id): ?array
    {
        $consulta = $this->db->prepare(
            "SELECT a.*, t.name type_name, s.name status_name, s.color,
                    b.name brand_name, m.name model_name, ar.name area_name,
                    l.name location_name, CONCAT(e.first_name, ' ', e.last_name) employee_name,
                    sup.name supplier_name
             FROM activos a
             JOIN tipos_activo t ON t.id = a.asset_type_id
             JOIN estados_activo s ON s.id = a.status_id
             LEFT JOIN marcas b ON b.id = a.brand_id
             LEFT JOIN modelos m ON m.id = a.model_id
             LEFT JOIN areas ar ON ar.id = a.current_area_id
             LEFT JOIN ubicaciones l ON l.id = a.location_id
             LEFT JOIN trabajadores e ON e.id = a.current_employee_id
             LEFT JOIN proveedores sup ON sup.id = a.supplier_id
             WHERE a.id = ?"
        );
        $consulta->execute([$id]);
        $activo = $consulta->fetch();

        if (!$activo) {
            return null;
        }

        $activo['specs'] = $this->especificacionesGuardadas($id);
        $activo['movements'] = $this->movimientos($id);
        $activo['mantenimientos'] = $this->mantenimientos($id);

        return $activo;
    }

    public function disponibles(): array
    {
        return $this->db
            ->query(
                "SELECT a.id, a.asset_code, a.serial_number, t.name type_name,
                        b.name brand_name, m.name model_name
                 FROM activos a
                 JOIN tipos_activo t ON t.id = a.asset_type_id
                 JOIN estados_activo s ON s.id = a.status_id
                 LEFT JOIN marcas b ON b.id = a.brand_id
                 LEFT JOIN modelos m ON m.id = a.model_id
                 WHERE a.active = 1 AND s.code = 'DISPONIBLE'
                 ORDER BY t.name, a.asset_code"
            )
            ->fetchAll();
    }

    public function guardar(array $datos, array $especificaciones, int $usuarioId, ?int $id = null): int
    {
        $this->db->beginTransaction();

        try {
            if ($id) {
                $this->actualizar($id, $datos, $usuarioId);
            } else {
                $id = $this->insertar($datos, $usuarioId);
            }

            $this->guardarEspecificaciones($id, $especificaciones);
            $this->db->commit();

            return $id;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function exportar(): array
    {
        return $this->db
            ->query('SELECT * FROM vw_inventario_general ORDER BY asset_code')
            ->fetchAll();
    }

    private function armarFiltros(array $filtros): array
    {
        $where = ['a.active = 1'];
        $params = [];

        if ($filtros['q'] ?? '') {
            $where[] = '(a.asset_code LIKE :q OR a.legacy_code LIKE :q OR a.serial_number LIKE :q OR a.hostname LIKE :q OR a.imei1 LIKE :q OR a.phone_number LIKE :q)';
            $params['q'] = '%'.$filtros['q'].'%';
        }

        $allowedFilters = [
            'type_id' => 'a.asset_type_id',
            'status_id' => 'a.status_id',
            'area_id' => 'a.current_area_id',
        ];

        foreach ($allowedFilters as $clave => $column) {
            if (!empty($filtros[$clave])) {
                $where[] = "$column = :$clave";
                $params[$clave] = (int) $filtros[$clave];
            }
        }

        return [implode(' AND ', $where), $params];
    }

    private function insertar(array $datos, int $usuarioId): int
    {
        $consulta = $this->db->prepare('CALL sp_generar_codigo_activo(?, @code)');
        $consulta->execute([$datos['asset_type_id']]);
        $consulta->closeCursor();

        $codigo = $this->db->query('SELECT @code')->fetchColumn();
        $consulta = $this->db->prepare(
            'INSERT INTO activos(
                asset_code, legacy_code, asset_type_id, brand_id, model_id, status_id,
                current_area_id, location_id, serial_number, hostname, ip_address, mac_address,
                imei1, imei2, phone_number, purchase_date, invoice_number, supplier_id,
                cost, warranty_end, notes, active, created_by, updated_by
             ) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)'
        );
        $consulta->execute(array_merge([$codigo], $this->argumentos($datos), [$usuarioId, $usuarioId]));

        $id = (int) $this->db->lastInsertId();
        $this->registrarMovimientoInicial($id, $datos, $usuarioId);

        return $id;
    }

    private function actualizar(int $id, array $datos, int $usuarioId): void
    {
        $consulta = $this->db->prepare(
            'UPDATE activos
             SET legacy_code = ?, asset_type_id = ?, brand_id = ?, model_id = ?, status_id = ?,
                 current_area_id = ?, location_id = ?, serial_number = ?, hostname = ?, ip_address = ?,
                 mac_address = ?, imei1 = ?, imei2 = ?, phone_number = ?, purchase_date = ?,
                 invoice_number = ?, supplier_id = ?, cost = ?, warranty_end = ?, notes = ?,
                 updated_by = ?, updated_at = NOW()
             WHERE id = ?'
        );

        $argumentos = $this->argumentos($datos);
        $argumentos[] = $usuarioId;
        $argumentos[] = $id;
        $consulta->execute($argumentos);

        $this->db
            ->prepare('DELETE FROM especificaciones_activo WHERE asset_id = ?')
            ->execute([$id]);
    }

    private function argumentos(array $datos): array
    {
        return [
            $datos['legacy_code'] ?: null,
            $datos['asset_type_id'],
            $datos['brand_id'] ?: null,
            $datos['model_id'] ?: null,
            $datos['status_id'],
            $datos['current_area_id'] ?: null,
            $datos['location_id'] ?: null,
            $datos['serial_number'] ?: null,
            $datos['hostname'] ?: null,
            $datos['ip_address'] ?: null,
            $datos['mac_address'] ?: null,
            $datos['imei1'] ?: null,
            $datos['imei2'] ?: null,
            $datos['phone_number'] ?: null,
            $datos['purchase_date'] ?: null,
            $datos['invoice_number'] ?: null,
            $datos['supplier_id'] ?: null,
            $datos['cost'] ?: null,
            $datos['warranty_end'] ?: null,
            $datos['notes'] ?: null,
        ];
    }

    private function guardarEspecificaciones(int $activoId, array $especificaciones): void
    {
        $consulta = $this->db->prepare(
            'INSERT INTO especificaciones_activo(asset_id, spec_key, spec_value) VALUES(?, ?, ?)'
        );

        foreach ($especificaciones as $clave => $valor) {
            $clave = trim($clave);
            $valor = trim($valor);

            if ($clave !== '' && $valor !== '') {
                $consulta->execute([$activoId, $clave, $valor]);
            }
        }
    }

    private function registrarMovimientoInicial(int $activoId, array $datos, int $usuarioId): void
    {
        $consulta = $this->db->prepare(
            "INSERT INTO movimientos_activo(asset_id, movement_type, to_status_id, to_area_id, notes, user_id)
             VALUES(?, 'REGISTRO', ?, ?, ?, ?)"
        );
        $consulta->execute([
            $activoId,
            $datos['status_id'],
            $datos['current_area_id'] ?: null,
            'Registro inicial',
            $usuarioId,
        ]);
    }

    private function especificacionesGuardadas(int $activoId): array
    {
        $consulta = $this->db->prepare(
            'SELECT spec_key, spec_value FROM especificaciones_activo WHERE asset_id = ? ORDER BY spec_key'
        );
        $consulta->execute([$activoId]);

        return $consulta->fetchAll();
    }

    private function movimientos(int $activoId): array
    {
        $consulta = $this->db->prepare(
            "SELECT am.*, u.name user_name
             FROM movimientos_activo am
             LEFT JOIN usuarios u ON u.id = am.user_id
             WHERE am.asset_id = ?
             ORDER BY am.id DESC
             LIMIT 50"
        );
        $consulta->execute([$activoId]);

        return $consulta->fetchAll();
    }

    private function mantenimientos(int $activoId): array
    {
        $consulta = $this->db->prepare(
            'SELECT * FROM mantenimientos WHERE asset_id = ? ORDER BY id DESC'
        );
        $consulta->execute([$activoId]);

        return $consulta->fetchAll();
    }
}