USE sigati_solandra;

-- Debe devolver una fila con los indicadores del dashboard
SELECT * FROM vw_dashboard_summary;

-- Inventario consolidado
SELECT * FROM vw_inventory_general ORDER BY codigo;

-- Procedimientos instalados
SHOW PROCEDURE STATUS WHERE Db='sigati_solandra';

-- Vistas instaladas
SHOW FULL TABLES IN sigati_solandra WHERE Table_type='VIEW';

-- Asignación demostrativa
SELECT a.assignment_number,a.status,e.employee_code,e.first_name,e.last_name,ai.asset_id
FROM assignments a
JOIN employees e ON e.id=a.employee_id
JOIN assignment_items ai ON ai.assignment_id=a.id;

-- Movimientos de los activos asignados
SELECT x.asset_code,m.movement_type,m.notes,m.created_at
FROM asset_movements m JOIN assets x ON x.id=m.asset_id
ORDER BY m.id DESC LIMIT 20;
