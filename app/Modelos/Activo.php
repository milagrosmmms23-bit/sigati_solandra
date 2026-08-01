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
            "SELECT a.*, t.nombre nombre_tipo, s.nombre nombre_estado, s.color,
                    b.nombre nombre_marca, m.nombre nombre_modelo, ar.nombre nombre_area,
                    l.nombre nombre_ubicacion, CONCAT(e.nombres, ' ', e.apellidos) nombre_trabajador
             FROM activos a
             JOIN tipos_activo t ON t.id = a.tipo_activo_id
             JOIN estados_activo s ON s.id = a.estado_id
             LEFT JOIN marcas b ON b.id = a.marca_id
             LEFT JOIN modelos m ON m.id = a.modelo_id
             LEFT JOIN areas ar ON ar.id = a.area_actual_id
             LEFT JOIN ubicaciones l ON l.id = a.ubicacion_id
             LEFT JOIN trabajadores e ON e.id = a.trabajador_actual_id
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
            "SELECT a.*, t.nombre nombre_tipo, s.nombre nombre_estado, s.color,
                    b.nombre nombre_marca, m.nombre nombre_modelo, ar.nombre nombre_area,
                    l.nombre nombre_ubicacion, CONCAT(e.nombres, ' ', e.apellidos) nombre_trabajador,
                    sup.nombre nombre_proveedor
             FROM activos a
             JOIN tipos_activo t ON t.id = a.tipo_activo_id
             JOIN estados_activo s ON s.id = a.estado_id
             LEFT JOIN marcas b ON b.id = a.marca_id
             LEFT JOIN modelos m ON m.id = a.modelo_id
             LEFT JOIN areas ar ON ar.id = a.area_actual_id
             LEFT JOIN ubicaciones l ON l.id = a.ubicacion_id
             LEFT JOIN trabajadores e ON e.id = a.trabajador_actual_id
             LEFT JOIN proveedores sup ON sup.id = a.proveedor_id
             WHERE a.id = ?"
        );
        $consulta->execute([$id]);
        $activo = $consulta->fetch();

        if (!$activo) {
            return null;
        }

        $activo['especificaciones'] = $this->especificacionesGuardadas($id);
        $activo['movimientos'] = $this->movimientos($id);
        $activo['mantenimientos'] = $this->mantenimientos($id);

        return $activo;
    }

    public function disponibles(): array
    {
        return $this->db
            ->query(
                "SELECT a.id, a.codigo_activo, a.numero_serie, t.nombre nombre_tipo,
                        b.nombre nombre_marca, m.nombre nombre_modelo
                 FROM activos a
                 JOIN tipos_activo t ON t.id = a.tipo_activo_id
                 JOIN estados_activo s ON s.id = a.estado_id
                 LEFT JOIN marcas b ON b.id = a.marca_id
                 LEFT JOIN modelos m ON m.id = a.modelo_id
                 WHERE a.activo = 1 AND s.codigo = 'DISPONIBLE'
                 ORDER BY t.nombre, a.codigo_activo"
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
            ->query('SELECT * FROM vw_inventario_general ORDER BY codigo_activo')
            ->fetchAll();
    }

    private function armarFiltros(array $filtros): array
    {
        $where = ['a.activo = 1'];
        $params = [];

        if ($filtros['q'] ?? '') {
            $where[] = '(a.codigo_activo LIKE :q OR a.codigo_anterior LIKE :q OR a.numero_serie LIKE :q OR a.nombre_equipo LIKE :q OR a.imei1 LIKE :q OR a.numero_telefono LIKE :q)';
            $params['q'] = '%'.$filtros['q'].'%';
        }

        $allowedFilters = [
            'tipo_activo_id' => 'a.tipo_activo_id',
            'estado_id' => 'a.estado_id',
            'area_id' => 'a.area_actual_id',
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
        $consulta = $this->db->prepare('CALL sp_generar_codigo_activo(?, @codigo)');
        $consulta->execute([$datos['tipo_activo_id']]);
        $consulta->closeCursor();

        $codigo = $this->db->query('SELECT @codigo')->fetchColumn();
        $consulta = $this->db->prepare(
            'INSERT INTO activos(
                codigo_activo, codigo_anterior, tipo_activo_id, marca_id, modelo_id, estado_id,
                area_actual_id, ubicacion_id, numero_serie, nombre_equipo, direccion_ip, direccion_mac,
                imei1, imei2, numero_telefono, fecha_compra, numero_factura, proveedor_id,
                costo, fin_garantia, observaciones, activo, creado_por, actualizado_por
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
             SET codigo_anterior = ?, tipo_activo_id = ?, marca_id = ?, modelo_id = ?, estado_id = ?,
                 area_actual_id = ?, ubicacion_id = ?, numero_serie = ?, nombre_equipo = ?, direccion_ip = ?,
                 direccion_mac = ?, imei1 = ?, imei2 = ?, numero_telefono = ?, fecha_compra = ?,
                 numero_factura = ?, proveedor_id = ?, costo = ?, fin_garantia = ?, observaciones = ?,
                 actualizado_por = ?, actualizado_en = NOW()
             WHERE id = ?'
        );

        $argumentos = $this->argumentos($datos);
        $argumentos[] = $usuarioId;
        $argumentos[] = $id;
        $consulta->execute($argumentos);

        $this->db
            ->prepare('DELETE FROM especificaciones_activo WHERE activo_id = ?')
            ->execute([$id]);
    }

    private function argumentos(array $datos): array
    {
        return [
            $datos['codigo_anterior'] ?: null,
            $datos['tipo_activo_id'],
            $datos['marca_id'] ?: null,
            $datos['modelo_id'] ?: null,
            $datos['estado_id'],
            $datos['area_actual_id'] ?: null,
            $datos['ubicacion_id'] ?: null,
            $datos['numero_serie'] ?: null,
            $datos['nombre_equipo'] ?: null,
            $datos['direccion_ip'] ?: null,
            $datos['direccion_mac'] ?: null,
            $datos['imei1'] ?: null,
            $datos['imei2'] ?: null,
            $datos['numero_telefono'] ?: null,
            $datos['fecha_compra'] ?: null,
            $datos['numero_factura'] ?: null,
            $datos['proveedor_id'] ?: null,
            $datos['costo'] ?: null,
            $datos['fin_garantia'] ?: null,
            $datos['observaciones'] ?: null,
        ];
    }

    private function guardarEspecificaciones(int $activoId, array $especificaciones): void
    {
        $consulta = $this->db->prepare(
            'INSERT INTO especificaciones_activo(activo_id, clave_especificacion, valor_especificacion) VALUES(?, ?, ?)'
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
            "INSERT INTO movimientos_activo(activo_id, tipo_movimiento, estado_destino_id, area_destino_id, observaciones, usuario_id)
             VALUES(?, 'REGISTRO', ?, ?, ?, ?)"
        );
        $consulta->execute([
            $activoId,
            $datos['estado_id'],
            $datos['area_actual_id'] ?: null,
            'Registro inicial',
            $usuarioId,
        ]);
    }

    private function especificacionesGuardadas(int $activoId): array
    {
        $consulta = $this->db->prepare(
            'SELECT clave_especificacion, valor_especificacion FROM especificaciones_activo WHERE activo_id = ? ORDER BY clave_especificacion'
        );
        $consulta->execute([$activoId]);

        return $consulta->fetchAll();
    }

    private function movimientos(int $activoId): array
    {
        $consulta = $this->db->prepare(
            "SELECT am.*, u.nombre nombre_usuario
             FROM movimientos_activo am
             LEFT JOIN usuarios u ON u.id = am.usuario_id
             WHERE am.activo_id = ?
             ORDER BY am.id DESC
             LIMIT 50"
        );
        $consulta->execute([$activoId]);

        return $consulta->fetchAll();
    }

    private function mantenimientos(int $activoId): array
    {
        $consulta = $this->db->prepare(
            'SELECT * FROM mantenimientos WHERE activo_id = ? ORDER BY id DESC'
        );
        $consulta->execute([$activoId]);

        return $consulta->fetchAll();
    }
}