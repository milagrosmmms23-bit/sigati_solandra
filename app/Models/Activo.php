<?php
declare(strict_types=1);

namespace App\Models;

use Throwable;

final class Activo extends ModeloBase
{
    public function list(array $filters, int $page, int $perPage): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $offset = ($page - 1) * $perPage;

        $count = $this->db->prepare("SELECT COUNT(*) FROM activos a WHERE $where");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $statement = $this->db->prepare(
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
             LIMIT $perPage OFFSET $offset"
        );
        $statement->execute($params);

        return [
            'rows' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'pages' => (int) ceil($total / $perPage),
        ];
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
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
        $statement->execute([$id]);
        $activo = $statement->fetch();

        if (!$activo) {
            return null;
        }

        $activo['specs'] = $this->specs($id);
        $activo['movements'] = $this->movements($id);
        $activo['mantenimientos'] = $this->mantenimientos($id);

        return $activo;
    }

    public function available(): array
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

    public function save(array $data, array $specs, int $user, ?int $id = null): int
    {
        $this->db->beginTransaction();

        try {
            if ($id) {
                $this->update($id, $data, $user);
            } else {
                $id = $this->insert($data, $user);
            }

            $this->saveSpecs($id, $specs);
            $this->db->commit();

            return $id;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function export(): array
    {
        return $this->db
            ->query('SELECT * FROM vw_inventario_general ORDER BY asset_code')
            ->fetchAll();
    }

    private function buildFilters(array $filters): array
    {
        $where = ['a.active = 1'];
        $params = [];

        if ($filters['q'] ?? '') {
            $where[] = '(a.asset_code LIKE :q OR a.legacy_code LIKE :q OR a.serial_number LIKE :q OR a.hostname LIKE :q OR a.imei1 LIKE :q OR a.phone_number LIKE :q)';
            $params['q'] = '%'.$filters['q'].'%';
        }

        $allowedFilters = [
            'type_id' => 'a.asset_type_id',
            'status_id' => 'a.status_id',
            'area_id' => 'a.current_area_id',
        ];

        foreach ($allowedFilters as $key => $column) {
            if (!empty($filters[$key])) {
                $where[] = "$column = :$key";
                $params[$key] = (int) $filters[$key];
            }
        }

        return [implode(' AND ', $where), $params];
    }

    private function insert(array $data, int $user): int
    {
        $statement = $this->db->prepare('CALL sp_generar_codigo_activo(?, @code)');
        $statement->execute([$data['asset_type_id']]);
        $statement->closeCursor();

        $code = $this->db->query('SELECT @code')->fetchColumn();
        $statement = $this->db->prepare(
            'INSERT INTO activos(
                asset_code, legacy_code, asset_type_id, brand_id, model_id, status_id,
                current_area_id, location_id, serial_number, hostname, ip_address, mac_address,
                imei1, imei2, phone_number, purchase_date, invoice_number, supplier_id,
                cost, warranty_end, notes, active, created_by, updated_by
             ) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)'
        );
        $statement->execute(array_merge([$code], $this->arguments($data), [$user, $user]));

        $id = (int) $this->db->lastInsertId();
        $this->registerInitialMovement($id, $data, $user);

        return $id;
    }

    private function update(int $id, array $data, int $user): void
    {
        $statement = $this->db->prepare(
            'UPDATE activos
             SET legacy_code = ?, asset_type_id = ?, brand_id = ?, model_id = ?, status_id = ?,
                 current_area_id = ?, location_id = ?, serial_number = ?, hostname = ?, ip_address = ?,
                 mac_address = ?, imei1 = ?, imei2 = ?, phone_number = ?, purchase_date = ?,
                 invoice_number = ?, supplier_id = ?, cost = ?, warranty_end = ?, notes = ?,
                 updated_by = ?, updated_at = NOW()
             WHERE id = ?'
        );

        $arguments = $this->arguments($data);
        $arguments[] = $user;
        $arguments[] = $id;
        $statement->execute($arguments);

        $this->db
            ->prepare('DELETE FROM especificaciones_activo WHERE asset_id = ?')
            ->execute([$id]);
    }

    private function arguments(array $data): array
    {
        return [
            $data['legacy_code'] ?: null,
            $data['asset_type_id'],
            $data['brand_id'] ?: null,
            $data['model_id'] ?: null,
            $data['status_id'],
            $data['current_area_id'] ?: null,
            $data['location_id'] ?: null,
            $data['serial_number'] ?: null,
            $data['hostname'] ?: null,
            $data['ip_address'] ?: null,
            $data['mac_address'] ?: null,
            $data['imei1'] ?: null,
            $data['imei2'] ?: null,
            $data['phone_number'] ?: null,
            $data['purchase_date'] ?: null,
            $data['invoice_number'] ?: null,
            $data['supplier_id'] ?: null,
            $data['cost'] ?: null,
            $data['warranty_end'] ?: null,
            $data['notes'] ?: null,
        ];
    }

    private function saveSpecs(int $assetId, array $specs): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO especificaciones_activo(asset_id, spec_key, spec_value) VALUES(?, ?, ?)'
        );

        foreach ($specs as $key => $value) {
            $key = trim($key);
            $value = trim($value);

            if ($key !== '' && $value !== '') {
                $statement->execute([$assetId, $key, $value]);
            }
        }
    }

    private function registerInitialMovement(int $assetId, array $data, int $user): void
    {
        $statement = $this->db->prepare(
            "INSERT INTO movimientos_activo(asset_id, movement_type, to_status_id, to_area_id, notes, user_id)
             VALUES(?, 'REGISTRO', ?, ?, ?, ?)"
        );
        $statement->execute([
            $assetId,
            $data['status_id'],
            $data['current_area_id'] ?: null,
            'Registro inicial',
            $user,
        ]);
    }

    private function specs(int $assetId): array
    {
        $statement = $this->db->prepare(
            'SELECT spec_key, spec_value FROM especificaciones_activo WHERE asset_id = ? ORDER BY spec_key'
        );
        $statement->execute([$assetId]);

        return $statement->fetchAll();
    }

    private function movements(int $assetId): array
    {
        $statement = $this->db->prepare(
            "SELECT am.*, u.name user_name
             FROM movimientos_activo am
             LEFT JOIN usuarios u ON u.id = am.user_id
             WHERE am.asset_id = ?
             ORDER BY am.id DESC
             LIMIT 50"
        );
        $statement->execute([$assetId]);

        return $statement->fetchAll();
    }

    private function mantenimientos(int $assetId): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM mantenimientos WHERE asset_id = ? ORDER BY id DESC'
        );
        $statement->execute([$assetId]);

        return $statement->fetchAll();
    }
}