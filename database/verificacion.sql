USE sigati_solandra;

-- Debe devolver una fila con los indicadores del dashboard
SELECT * FROM vw_resumen_panel;

-- Inventario consolidado
SELECT * FROM vw_inventario_general ORDER BY codigo_activo;

-- Procedimientos instalados
SHOW PROCEDURE STATUS WHERE Db='sigati_solandra';

-- Vistas instaladas
SHOW FULL TABLES IN sigati_solandra WHERE Table_type='VIEW';

-- Asignacion demostrativa
SELECT a.numero_asignacion,a.estado,e.codigo_trabajador,e.nombres,e.apellidos,ai.activo_id
FROM asignaciones a
JOIN trabajadores e ON e.id=a.trabajador_id
JOIN items_asignacion ai ON ai.asignacion_id=a.id;

-- Movimientos de los activos asignados
SELECT x.codigo_activo,m.tipo_movimiento,m.observaciones,m.creado_en
FROM movimientos_activo m JOIN activos x ON x.id=m.activo_id
ORDER BY m.id DESC LIMIT 20;
