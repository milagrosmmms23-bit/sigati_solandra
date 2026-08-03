<?php
declare(strict_types=1);

namespace App\Modelos;

final class Panel extends ModeloBase
{
    public function datos(): array
    {
        $resumen = $this->db->query('SELECT * FROM vw_resumen_panel')->fetch() ?: [];

        return [
            'resumen' => $this->mapearResumen($resumen),
            'alertas' => $this->alertasOperativas(),
            'porEstado' => $this->db->query('SELECT * FROM vw_activos_por_estado')->fetchAll(),
            'porTipo' => $this->db->query('SELECT * FROM vw_activos_por_tipo')->fetchAll(),
            'porArea' => $this->db->query('SELECT * FROM vw_activos_por_area LIMIT 10')->fetchAll(),
            'recientes' => $this->movimientosRecientes(),
        ];
    }

    private function mapearResumen(array $resumen): array
    {
        return [
            'total_activos' => $resumen['total_activos'] ?? 0,
            'activos_asignados' => $resumen['activos_asignados'] ?? 0,
            'activos_disponibles' => $resumen['activos_disponibles'] ?? 0,
            'activos_mantenimiento' => $resumen['activos_mantenimiento'] ?? 0,
            'total_trabajadores' => $resumen['total_trabajadores'] ?? 0,
            'asignaciones_activas' => $resumen['asignaciones_activas'] ?? 0,
            'mantenimientos_abiertos' => $resumen['mantenimientos_abiertos'] ?? 0,
        ];
    }

    private function alertasOperativas(): array
    {
        return [
            [
                'titulo' => 'Activos sin identificador clave',
                'total' => $this->contar(
                    "SELECT COUNT(*)
                     FROM activos a
                     JOIN tipos_activo t ON t.id = a.tipo_activo_id
                     WHERE a.activo = 1
                       AND (
                         (t.nombre IN ('PC','Laptop','Monitor','Radio','Impresora') AND NULLIF(TRIM(a.numero_serie), '') IS NULL)
                         OR (t.nombre = 'Celular' AND NULLIF(TRIM(a.imei1), '') IS NULL)
                       )"
                ),
                'detalle' => 'Equipos importados sin serie o IMEI principal.',
                'tono' => 'warning',
                'link' => 'activos',
            ],
            [
                'titulo' => 'Activos sin area',
                'total' => $this->contar(
                    "SELECT COUNT(*) FROM activos WHERE activo = 1 AND area_actual_id IS NULL"
                ),
                'detalle' => 'Falta completar la ubicacion responsable.',
                'tono' => 'warning',
                'link' => 'activos',
            ],
            [
                'titulo' => 'Asignados sin acta vigente',
                'total' => $this->contar(
                    "SELECT COUNT(*)
                     FROM activos a
                     JOIN estados_activo s ON s.id = a.estado_id
                     WHERE a.activo = 1
                       AND s.codigo = 'ASIGNADO'
                       AND NOT EXISTS (
                         SELECT 1
                         FROM items_asignacion ia
                         JOIN asignaciones ag ON ag.id = ia.asignacion_id
                         WHERE ia.activo_id = a.id
                           AND ia.devuelto_en IS NULL
                           AND ag.estado IN ('CONFIRMADA','PARCIAL')
                       )"
                ),
                'detalle' => 'Conviene regularizar actas para cerrar trazabilidad.',
                'tono' => 'danger',
                'link' => 'asignaciones',
            ],
            [
                'titulo' => 'Mantenimientos vencidos',
                'total' => $this->contar(
                    "SELECT COUNT(*)
                     FROM mantenimientos
                     WHERE estado = 'ABIERTO'
                       AND proxima_fecha IS NOT NULL
                       AND proxima_fecha < CURDATE()"
                ),
                'detalle' => 'Servicios abiertos con fecha programada vencida.',
                'tono' => 'danger',
                'link' => 'mantenimientos',
            ],
            [
                'titulo' => 'Responsables solo desde Excel',
                'total' => $this->contar(
                    "SELECT COUNT(DISTINCT ea.activo_id)
                     FROM especificaciones_activo ea
                     JOIN activos a ON a.id = ea.activo_id
                     WHERE a.activo = 1
                       AND ea.clave_especificacion = 'Responsable en Excel'"
                ),
                'detalle' => 'Falta convertir esos datos en asignaciones reales.',
                'tono' => 'info',
                'link' => 'activos',
            ],
            [
                'titulo' => 'Activos sin factura',
                'total' => $this->contar(
                    "SELECT COUNT(*)
                     FROM activos
                     WHERE activo = 1
                       AND NULLIF(TRIM(numero_factura), '') IS NULL"
                ),
                'detalle' => 'Pendientes de completar con numero de factura o sustento de compra.',
                'tono' => 'warning',
                'link' => 'activos?facturacion=sin_factura',
            ],
            [
                'titulo' => 'Codigos o series repetidos',
                'total' => $this->contar(
                    "SELECT COALESCE(SUM(repetidos), 0)
                     FROM (
                       SELECT COUNT(*) repetidos FROM activos WHERE activo = 1 AND NULLIF(TRIM(codigo_anterior), '') IS NOT NULL GROUP BY codigo_anterior HAVING COUNT(*) > 1
                       UNION ALL
                       SELECT COUNT(*) repetidos FROM activos WHERE activo = 1 AND NULLIF(TRIM(numero_serie), '') IS NOT NULL GROUP BY numero_serie HAVING COUNT(*) > 1
                       UNION ALL
                       SELECT COUNT(*) repetidos FROM activos WHERE activo = 1 AND NULLIF(TRIM(direccion_mac), '') IS NOT NULL GROUP BY direccion_mac HAVING COUNT(*) > 1
                       UNION ALL
                       SELECT COUNT(*) repetidos FROM activos WHERE activo = 1 AND NULLIF(TRIM(imei1), '') IS NOT NULL GROUP BY imei1 HAVING COUNT(*) > 1
                       UNION ALL
                       SELECT COUNT(*) repetidos FROM activos WHERE activo = 1 AND NULLIF(TRIM(numero_telefono), '') IS NOT NULL GROUP BY numero_telefono HAVING COUNT(*) > 1
                     ) duplicados"
                ),
                'detalle' => 'Posibles duplicados que deben revisarse antes de imprimir actas.',
                'tono' => 'danger',
                'link' => 'activos',
            ],
        ];
    }

    private function contar(string $sql): int
    {
        return (int) $this->db->query($sql)->fetchColumn();
    }

    private function movimientosRecientes(): array
    {
        return $this->db
            ->query(
                "SELECT am.*, a.codigo_activo, u.nombre nombre_usuario
                 FROM movimientos_activo am
                 JOIN activos a ON a.id = am.activo_id
                 LEFT JOIN usuarios u ON u.id = am.usuario_id
                 ORDER BY am.id DESC
                 LIMIT 8"
            )
            ->fetchAll();
    }
}
