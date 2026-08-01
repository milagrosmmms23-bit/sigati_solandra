-- Migracion SIGATI: renombrar tablas de ingles a espanol.
-- Ejecutar UNA SOLA VEZ sobre una base existente creada con los nombres anteriores.
-- Recomendado: hacer backup antes de ejecutar.

SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS vw_inventory_general;
DROP VIEW IF EXISTS vw_dashboard_summary;
DROP VIEW IF EXISTS vw_assets_by_status;
DROP VIEW IF EXISTS vw_assets_by_type;
DROP VIEW IF EXISTS vw_assets_by_area;
DROP VIEW IF EXISTS vw_inventario_general;
DROP VIEW IF EXISTS vw_resumen_panel;
DROP VIEW IF EXISTS vw_activos_por_estado;
DROP VIEW IF EXISTS vw_activos_por_tipo;
DROP VIEW IF EXISTS vw_activos_por_area;

DROP PROCEDURE IF EXISTS sp_generate_asset_code;
DROP PROCEDURE IF EXISTS sp_assignment_create;
DROP PROCEDURE IF EXISTS sp_assignment_add_asset;
DROP PROCEDURE IF EXISTS sp_assignment_confirm;
DROP PROCEDURE IF EXISTS sp_return_create;
DROP PROCEDURE IF EXISTS sp_return_asset;
DROP PROCEDURE IF EXISTS sp_return_confirm;
DROP PROCEDURE IF EXISTS sp_maintenance_open;
DROP PROCEDURE IF EXISTS sp_maintenance_close;
DROP PROCEDURE IF EXISTS sp_generar_codigo_activo;
DROP PROCEDURE IF EXISTS sp_crear_asignacion;
DROP PROCEDURE IF EXISTS sp_agregar_activo_asignacion;
DROP PROCEDURE IF EXISTS sp_confirmar_asignacion;
DROP PROCEDURE IF EXISTS sp_crear_devolucion;
DROP PROCEDURE IF EXISTS sp_devolver_activo;
DROP PROCEDURE IF EXISTS sp_confirmar_devolucion;
DROP PROCEDURE IF EXISTS sp_abrir_mantenimiento;
DROP PROCEDURE IF EXISTS sp_cerrar_mantenimiento;

RENAME TABLE
  users TO usuarios,
  locations TO ubicaciones,
  employees TO trabajadores,
  asset_types TO tipos_activo,
  asset_statuses TO estados_activo,
  brands TO marcas,
  models TO modelos,
  suppliers TO proveedores,
  asset_counters TO contadores_activo,
  document_counters TO contadores_documento,
  assets TO activos,
  asset_specifications TO especificaciones_activo,
  assignments TO asignaciones,
  assignment_items TO items_asignacion,
  asset_returns TO devoluciones_activo,
  return_items TO items_devolucion,
  maintenances TO mantenimientos,
  asset_movements TO movimientos_activo,
  audit_logs TO registros_auditoria;

SET FOREIGN_KEY_CHECKS = 1;
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_generar_codigo_activo$$
CREATE PROCEDURE sp_generar_codigo_activo(IN p_asset_type_id INT, OUT p_code VARCHAR(40))
BEGIN
  DECLARE v_prefix VARCHAR(8);
  DECLARE v_number INT;
  SELECT prefix INTO v_prefix FROM tipos_activo WHERE id=p_asset_type_id AND active=1;
  IF v_prefix IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Tipo de activo invalido'; END IF;
  INSERT INTO contadores_activo(asset_type_id,current_number) VALUES(p_asset_type_id,0)
    ON DUPLICATE KEY UPDATE current_number=current_number;
  UPDATE contadores_activo SET current_number=LAST_INSERT_ID(current_number+1) WHERE asset_type_id=p_asset_type_id;
  SET v_number=LAST_INSERT_ID();
  SET p_code=CONCAT('AQP-',v_prefix,'-',LPAD(v_number,6,'0'));
END$$

DROP PROCEDURE IF EXISTS sp_crear_asignacion$$
CREATE PROCEDURE sp_crear_asignacion(
  IN p_employee_id INT, IN p_area_id INT, IN p_notes TEXT, IN p_user_id INT,
  OUT p_assignment_id BIGINT, OUT p_assignment_number VARCHAR(30)
)
BEGIN
  DECLARE v_year SMALLINT;
  DECLARE v_num INT;
  DECLARE v_area INT;
  DECLARE v_employee_exists INT DEFAULT 0;
  SET v_year=YEAR(CURDATE());
  SELECT COUNT(*),MAX(area_id) INTO v_employee_exists,v_area FROM trabajadores WHERE id=p_employee_id AND active=1;
  IF v_employee_exists=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El trabajador no existe o esta inactivo'; END IF;
  IF p_area_id IS NOT NULL AND p_area_id>0 THEN SET v_area=p_area_id; END IF;
  INSERT INTO contadores_documento(document_type,document_year,current_number) VALUES('ASG',v_year,0)
    ON DUPLICATE KEY UPDATE current_number=current_number;
  UPDATE contadores_documento SET current_number=LAST_INSERT_ID(current_number+1)
    WHERE document_type='ASG' AND document_year=v_year;
  SET v_num=LAST_INSERT_ID();
  SET p_assignment_number=CONCAT('ASG-',v_year,'-',LPAD(v_num,6,'0'));
  INSERT INTO asignaciones(assignment_number,employee_id,area_id,status,notes,created_by)
  VALUES(p_assignment_number,p_employee_id,v_area,'BORRADOR',NULLIF(p_notes,''),p_user_id);
  SET p_assignment_id=LAST_INSERT_ID();
END$$

DROP PROCEDURE IF EXISTS sp_agregar_activo_asignacion$$
CREATE PROCEDURE sp_agregar_activo_asignacion(
  IN p_assignment_id BIGINT, IN p_asset_id BIGINT, IN p_condition VARCHAR(255), IN p_user_id INT
)
BEGIN
  DECLARE v_status_code VARCHAR(40);
  DECLARE v_header_status VARCHAR(20);
  SELECT status INTO v_header_status FROM asignaciones WHERE id=p_assignment_id FOR UPDATE;
  IF v_header_status IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignacion inexistente'; END IF;
  IF v_header_status<>'BORRADOR' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La asignacion ya no esta en borrador'; END IF;
  SELECT s.code INTO v_status_code FROM activos a JOIN estados_activo s ON s.id=a.status_id WHERE a.id=p_asset_id AND a.active=1 FOR UPDATE;
  IF v_status_code IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Activo inexistente'; END IF;
  IF v_status_code<>'DISPONIBLE' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Uno de los activos ya no esta disponible'; END IF;
  INSERT INTO items_asignacion(assignment_id,asset_id,condition_out)
  VALUES(p_assignment_id,p_asset_id,COALESCE(NULLIF(p_condition,''),'Buen estado'));
END$$

DROP PROCEDURE IF EXISTS sp_confirmar_asignacion$$
CREATE PROCEDURE sp_confirmar_asignacion(IN p_assignment_id BIGINT, IN p_user_id INT)
BEGIN
  DECLARE v_employee INT;
  DECLARE v_area INT;
  DECLARE v_status INT;
  DECLARE v_count INT;
  SELECT employee_id,area_id INTO v_employee,v_area FROM asignaciones WHERE id=p_assignment_id AND status='BORRADOR' FOR UPDATE;
  IF v_employee IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignacion no disponible para confirmar'; END IF;
  SELECT COUNT(*) INTO v_count FROM items_asignacion WHERE assignment_id=p_assignment_id;
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La asignacion no contiene activos'; END IF;
  SELECT id INTO v_status FROM estados_activo WHERE code='ASIGNADO';
  INSERT INTO movimientos_activo(asset_id,movement_type,reference_type,reference_id,from_status_id,to_status_id,from_area_id,to_area_id,from_employee_id,to_employee_id,notes,user_id)
  SELECT a.id,'ASIGNACION','assignment',p_assignment_id,a.status_id,v_status,a.current_area_id,v_area,a.current_employee_id,v_employee,CONCAT('Asignado mediante ',x.assignment_number),p_user_id
  FROM items_asignacion ai JOIN activos a ON a.id=ai.asset_id JOIN asignaciones x ON x.id=ai.assignment_id
  WHERE ai.assignment_id=p_assignment_id;
  UPDATE activos a JOIN items_asignacion ai ON ai.asset_id=a.id
  SET a.status_id=v_status,a.current_employee_id=v_employee,a.current_area_id=v_area,a.updated_by=p_user_id
  WHERE ai.assignment_id=p_assignment_id;
  UPDATE asignaciones SET status='CONFIRMADA',assigned_at=NOW() WHERE id=p_assignment_id;
END$$

DROP PROCEDURE IF EXISTS sp_crear_devolucion$$
CREATE PROCEDURE sp_crear_devolucion(
  IN p_assignment_id BIGINT, IN p_notes TEXT, IN p_user_id INT,
  OUT p_return_id BIGINT, OUT p_return_number VARCHAR(30)
)
BEGIN
  DECLARE v_year SMALLINT;
  DECLARE v_num INT;
  DECLARE v_ok INT;
  SELECT COUNT(*) INTO v_ok FROM asignaciones WHERE id=p_assignment_id AND status IN('CONFIRMADA','PARCIAL');
  IF v_ok=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignacion no disponible para devolucion'; END IF;
  SET v_year=YEAR(CURDATE());
  INSERT INTO contadores_documento(document_type,document_year,current_number) VALUES('DEV',v_year,0)
    ON DUPLICATE KEY UPDATE current_number=current_number;
  UPDATE contadores_documento SET current_number=LAST_INSERT_ID(current_number+1)
    WHERE document_type='DEV' AND document_year=v_year;
  SET v_num=LAST_INSERT_ID();
  SET p_return_number=CONCAT('DEV-',v_year,'-',LPAD(v_num,6,'0'));
  INSERT INTO devoluciones_activo(return_number,assignment_id,status,notes,created_by)
  VALUES(p_return_number,p_assignment_id,'BORRADOR',NULLIF(p_notes,''),p_user_id);
  SET p_return_id=LAST_INSERT_ID();
END$$

DROP PROCEDURE IF EXISTS sp_devolver_activo$$
CREATE PROCEDURE sp_devolver_activo(
  IN p_return_id BIGINT, IN p_assignment_item_id BIGINT, IN p_condition VARCHAR(255),
  IN p_damage VARCHAR(500), IN p_next_status_id INT, IN p_user_id INT
)
BEGIN
  DECLARE v_asset BIGINT;
  DECLARE v_assignment BIGINT;
  DECLARE v_return_assignment BIGINT;
  DECLARE v_from_status INT;
  DECLARE v_from_area INT;
  DECLARE v_from_employee INT;
  SELECT assignment_id INTO v_return_assignment FROM devoluciones_activo WHERE id=p_return_id AND status='BORRADOR' FOR UPDATE;
  SELECT ai.asset_id,ai.assignment_id INTO v_asset,v_assignment FROM items_asignacion ai WHERE ai.id=p_assignment_item_id AND ai.returned_at IS NULL FOR UPDATE;
  IF v_asset IS NULL OR v_assignment<>v_return_assignment THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El activo no pertenece a la asignacion o ya fue devuelto'; END IF;
  IF NOT EXISTS(SELECT 1 FROM estados_activo WHERE id=p_next_status_id AND active=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Estado posterior invalido'; END IF;
  SELECT status_id,current_area_id,current_employee_id INTO v_from_status,v_from_area,v_from_employee FROM activos WHERE id=v_asset FOR UPDATE;
  INSERT INTO items_devolucion(return_id,assignment_item_id,condition_in,damage_notes,next_status_id)
  VALUES(p_return_id,p_assignment_item_id,COALESCE(NULLIF(p_condition,''),'Sin especificar'),NULLIF(p_damage,''),p_next_status_id);
  UPDATE items_asignacion SET returned_at=NOW() WHERE id=p_assignment_item_id;
  UPDATE activos SET status_id=p_next_status_id,current_employee_id=NULL,updated_by=p_user_id WHERE id=v_asset;
  INSERT INTO movimientos_activo(asset_id,movement_type,reference_type,reference_id,from_status_id,to_status_id,from_area_id,to_area_id,from_employee_id,to_employee_id,notes,user_id)
  VALUES(v_asset,'DEVOLUCION','return',p_return_id,v_from_status,p_next_status_id,v_from_area,v_from_area,v_from_employee,NULL,COALESCE(NULLIF(p_damage,''),'Equipo recibido sin danos reportados'),p_user_id);
END$$

DROP PROCEDURE IF EXISTS sp_confirmar_devolucion$$
CREATE PROCEDURE sp_confirmar_devolucion(IN p_return_id BIGINT, IN p_user_id INT)
BEGIN
  DECLARE v_assignment BIGINT;
  DECLARE v_items INT;
  DECLARE v_pending INT;
  SELECT assignment_id INTO v_assignment FROM devoluciones_activo WHERE id=p_return_id AND status='BORRADOR' FOR UPDATE;
  IF v_assignment IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Devolucion no disponible para confirmar'; END IF;
  SELECT COUNT(*) INTO v_items FROM items_devolucion WHERE return_id=p_return_id;
  IF v_items=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La devolucion no contiene equipos'; END IF;
  UPDATE devoluciones_activo SET status='CONFIRMADA',returned_at=NOW() WHERE id=p_return_id;
  SELECT COUNT(*) INTO v_pending FROM items_asignacion WHERE assignment_id=v_assignment AND returned_at IS NULL;
  UPDATE asignaciones SET status=IF(v_pending=0,'CERRADA','PARCIAL') WHERE id=v_assignment;
END$$

DROP PROCEDURE IF EXISTS sp_abrir_mantenimiento$$
CREATE PROCEDURE sp_abrir_mantenimiento(
  IN p_asset_id BIGINT, IN p_type VARCHAR(20), IN p_issue TEXT, IN p_diagnosis TEXT,
  IN p_actions TEXT, IN p_cost DECIMAL(12,2), IN p_user_id INT, OUT p_maintenance_id BIGINT
)
BEGIN
  DECLARE v_old_status INT;
  DECLARE v_maintenance_status INT;
  DECLARE v_area INT;
  DECLARE v_employee INT;
  SELECT status_id,current_area_id,current_employee_id INTO v_old_status,v_area,v_employee FROM activos WHERE id=p_asset_id AND active=1 FOR UPDATE;
  IF v_old_status IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Activo inexistente'; END IF;
  IF EXISTS(SELECT 1 FROM mantenimientos WHERE asset_id=p_asset_id AND status='ABIERTO') THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El activo ya tiene un mantenimiento abierto'; END IF;
  SELECT id INTO v_maintenance_status FROM estados_activo WHERE code='MANTENIMIENTO';
  INSERT INTO mantenimientos(asset_id,type,status,previous_status_id,issue,diagnosis,actions,cost,started_at,opened_by)
  VALUES(p_asset_id,IF(p_type='CORRECTIVO','CORRECTIVO','PREVENTIVO'),'ABIERTO',v_old_status,NULLIF(p_issue,''),NULLIF(p_diagnosis,''),NULLIF(p_actions,''),COALESCE(p_cost,0),NOW(),p_user_id);
  SET p_maintenance_id=LAST_INSERT_ID();
  UPDATE activos SET status_id=v_maintenance_status,updated_by=p_user_id WHERE id=p_asset_id;
  INSERT INTO movimientos_activo(asset_id,movement_type,reference_type,reference_id,from_status_id,to_status_id,from_area_id,to_area_id,from_employee_id,to_employee_id,notes,user_id)
  VALUES(p_asset_id,'MANTENIMIENTO','maintenance',p_maintenance_id,v_old_status,v_maintenance_status,v_area,v_area,v_employee,v_employee,COALESCE(NULLIF(p_issue,''),'Ingreso a mantenimiento'),p_user_id);
END$$

DROP PROCEDURE IF EXISTS sp_cerrar_mantenimiento$$
CREATE PROCEDURE sp_cerrar_mantenimiento(
  IN p_maintenance_id BIGINT, IN p_diagnosis TEXT, IN p_actions TEXT, IN p_parts TEXT,
  IN p_cost DECIMAL(12,2), IN p_next_date DATE, IN p_user_id INT
)
BEGIN
  DECLARE v_asset BIGINT;
  DECLARE v_restore_status INT;
  DECLARE v_current_status INT;
  DECLARE v_area INT;
  DECLARE v_employee INT;
  SELECT asset_id,previous_status_id INTO v_asset,v_restore_status FROM mantenimientos WHERE id=p_maintenance_id AND status='ABIERTO' FOR UPDATE;
  IF v_asset IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Mantenimiento no disponible para cerrar'; END IF;
  SELECT status_id,current_area_id,current_employee_id INTO v_current_status,v_area,v_employee FROM activos WHERE id=v_asset FOR UPDATE;
  IF v_restore_status IS NULL THEN SELECT id INTO v_restore_status FROM estados_activo WHERE code='DISPONIBLE'; END IF;
  UPDATE mantenimientos SET status='CERRADO',diagnosis=NULLIF(p_diagnosis,''),actions=NULLIF(p_actions,''),parts_used=NULLIF(p_parts,''),cost=COALESCE(p_cost,0),next_date=p_next_date,finished_at=NOW(),closed_by=p_user_id WHERE id=p_maintenance_id;
  UPDATE activos SET status_id=v_restore_status,updated_by=p_user_id WHERE id=v_asset;
  INSERT INTO movimientos_activo(asset_id,movement_type,reference_type,reference_id,from_status_id,to_status_id,from_area_id,to_area_id,from_employee_id,to_employee_id,notes,user_id)
  VALUES(v_asset,'CIERRE_MANTENIMIENTO','maintenance',p_maintenance_id,v_current_status,v_restore_status,v_area,v_area,v_employee,v_employee,COALESCE(NULLIF(p_actions,''),'Mantenimiento finalizado'),p_user_id);
END$$

DELIMITER ;

-- Vistas para dashboard y reportes
DROP VIEW IF EXISTS vw_inventario_general;
CREATE VIEW vw_inventario_general AS
SELECT
  a.asset_code AS codigo,
  a.legacy_code AS codigo_anterior,
  t.name AS tipo,
  b.name AS marca,
  m.name AS modelo,
  a.serial_number AS serie,
  s.name AS estado,
  ar.name AS area,
  l.name AS ubicacion,
  CONCAT_WS(' ',e.first_name,e.last_name) AS responsable,
  a.hostname,
  a.ip_address AS ip,
  a.mac_address AS mac,
  a.imei1,
  a.phone_number AS telefono,
  DATE_FORMAT(a.purchase_date,'%Y-%m-%d') AS fecha_compra,
  a.invoice_number AS factura,
  sup.name AS proveedor,
  a.cost AS costo,
  DATE_FORMAT(a.warranty_end,'%Y-%m-%d') AS garantia,
  DATE_FORMAT(a.created_at,'%Y-%m-%d %H:%i') AS creado
FROM activos a
JOIN tipos_activo t ON t.id=a.asset_type_id
JOIN estados_activo s ON s.id=a.status_id
LEFT JOIN marcas b ON b.id=a.brand_id
LEFT JOIN modelos m ON m.id=a.model_id
LEFT JOIN areas ar ON ar.id=a.current_area_id
LEFT JOIN ubicaciones l ON l.id=a.location_id
LEFT JOIN trabajadores e ON e.id=a.current_employee_id
LEFT JOIN proveedores sup ON sup.id=a.supplier_id
WHERE a.active=1;

DROP VIEW IF EXISTS vw_activos_por_estado;
CREATE VIEW vw_activos_por_estado AS
SELECT s.id,s.name,s.code,s.color,COUNT(a.id) total
FROM estados_activo s LEFT JOIN activos a ON a.status_id=s.id AND a.active=1
WHERE s.active=1 GROUP BY s.id,s.name,s.code,s.color HAVING total>0 ORDER BY total DESC;

DROP VIEW IF EXISTS vw_activos_por_tipo;
CREATE VIEW vw_activos_por_tipo AS
SELECT t.id,t.name,t.prefix,COUNT(a.id) total
FROM tipos_activo t LEFT JOIN activos a ON a.asset_type_id=t.id AND a.active=1
WHERE t.active=1 GROUP BY t.id,t.name,t.prefix HAVING total>0 ORDER BY total DESC;

DROP VIEW IF EXISTS vw_activos_por_area;
CREATE VIEW vw_activos_por_area AS
SELECT COALESCE(ar.id,0) id,COALESCE(ar.name,'Sin area') name,COUNT(a.id) total
FROM activos a LEFT JOIN areas ar ON ar.id=a.current_area_id
WHERE a.active=1 GROUP BY ar.id,ar.name ORDER BY total DESC;

DROP VIEW IF EXISTS vw_resumen_panel;
CREATE VIEW vw_resumen_panel AS
SELECT
  (SELECT COUNT(*) FROM activos WHERE active=1) total_assets,
  (SELECT COUNT(*) FROM activos a JOIN estados_activo s ON s.id=a.status_id WHERE a.active=1 AND s.code='ASIGNADO') assigned_assets,
  (SELECT COUNT(*) FROM activos a JOIN estados_activo s ON s.id=a.status_id WHERE a.active=1 AND s.code='DISPONIBLE') available_assets,
  (SELECT COUNT(*) FROM activos a JOIN estados_activo s ON s.id=a.status_id WHERE a.active=1 AND s.code IN('MANTENIMIENTO','REPARACION')) maintenance_assets,
  (SELECT COUNT(*) FROM trabajadores WHERE active=1) total_employees,
  (SELECT COUNT(*) FROM asignaciones WHERE status IN('CONFIRMADA','PARCIAL')) active_assignments,
  (SELECT COUNT(*) FROM mantenimientos WHERE status='ABIERTO') open_maintenances;

-- Asignacion demostrativa, creada mediante los procedimientos del sistema
