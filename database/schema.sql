-- SIGATI SOLANDRA - Sede Arequipa
-- Compatible con MariaDB 10.4+ (XAMPP) y MySQL 8+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS sigati_solandra;
CREATE DATABASE sigati_solandra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sigati_solandra;

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL UNIQUE,
  description VARCHAR(150) NULL
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(150) NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE areas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE locations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  area_id INT UNSIGNED NULL,
  name VARCHAR(140) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_location_area (area_id,name),
  CONSTRAINT fk_locations_area FOREIGN KEY (area_id) REFERENCES areas(id)
) ENGINE=InnoDB;

CREATE TABLE employees (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_code VARCHAR(50) NOT NULL UNIQUE,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NULL,
  phone VARCHAR(30) NULL,
  position VARCHAR(120) NULL,
  area_id INT UNSIGNED NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX ix_employees_name (last_name,first_name),
  CONSTRAINT fk_employees_area FOREIGN KEY (area_id) REFERENCES areas(id)
) ENGINE=InnoDB;

CREATE TABLE asset_types (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  prefix VARCHAR(8) NOT NULL UNIQUE,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE asset_statuses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL UNIQUE,
  color VARCHAR(30) NOT NULL DEFAULT 'secondary',
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE brands (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE models (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  brand_id INT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_model_brand (brand_id,name),
  CONSTRAINT fk_models_brand FOREIGN KEY (brand_id) REFERENCES brands(id)
) ENGINE=InnoDB;

CREATE TABLE suppliers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL UNIQUE,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE asset_counters (
  asset_type_id INT UNSIGNED PRIMARY KEY,
  current_number INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_counter_type FOREIGN KEY (asset_type_id) REFERENCES asset_types(id)
) ENGINE=InnoDB;

CREATE TABLE document_counters (
  document_type VARCHAR(15) NOT NULL,
  document_year SMALLINT UNSIGNED NOT NULL,
  current_number INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(document_type,document_year)
) ENGINE=InnoDB;

CREATE TABLE assets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NULL UNIQUE,
  asset_code VARCHAR(40) NOT NULL UNIQUE,
  legacy_code VARCHAR(80) NULL,
  asset_type_id INT UNSIGNED NOT NULL,
  brand_id INT UNSIGNED NULL,
  model_id INT UNSIGNED NULL,
  status_id INT UNSIGNED NOT NULL,
  current_area_id INT UNSIGNED NULL,
  location_id INT UNSIGNED NULL,
  current_employee_id INT UNSIGNED NULL,
  serial_number VARCHAR(150) NULL,
  hostname VARCHAR(120) NULL,
  ip_address VARCHAR(45) NULL,
  mac_address VARCHAR(30) NULL,
  imei1 VARCHAR(30) NULL,
  imei2 VARCHAR(30) NULL,
  phone_number VARCHAR(30) NULL,
  purchase_date DATE NULL,
  invoice_number VARCHAR(80) NULL,
  supplier_id INT UNSIGNED NULL,
  cost DECIMAL(12,2) NULL,
  warranty_end DATE NULL,
  notes TEXT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED NULL,
  updated_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX ix_assets_search (asset_code,legacy_code,serial_number),
  INDEX ix_assets_status (status_id),
  INDEX ix_assets_area (current_area_id),
  INDEX ix_assets_employee (current_employee_id),
  CONSTRAINT fk_assets_type FOREIGN KEY (asset_type_id) REFERENCES asset_types(id),
  CONSTRAINT fk_assets_brand FOREIGN KEY (brand_id) REFERENCES brands(id),
  CONSTRAINT fk_assets_model FOREIGN KEY (model_id) REFERENCES models(id),
  CONSTRAINT fk_assets_status FOREIGN KEY (status_id) REFERENCES asset_statuses(id),
  CONSTRAINT fk_assets_area FOREIGN KEY (current_area_id) REFERENCES areas(id),
  CONSTRAINT fk_assets_location FOREIGN KEY (location_id) REFERENCES locations(id),
  CONSTRAINT fk_assets_employee FOREIGN KEY (current_employee_id) REFERENCES employees(id),
  CONSTRAINT fk_assets_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
  CONSTRAINT fk_assets_created_by FOREIGN KEY (created_by) REFERENCES users(id),
  CONSTRAINT fk_assets_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE asset_specifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_id BIGINT UNSIGNED NOT NULL,
  spec_key VARCHAR(100) NOT NULL,
  spec_value VARCHAR(255) NOT NULL,
  UNIQUE KEY uq_asset_spec (asset_id,spec_key),
  CONSTRAINT fk_specs_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE assignments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assignment_number VARCHAR(30) NOT NULL UNIQUE,
  employee_id INT UNSIGNED NOT NULL,
  area_id INT UNSIGNED NULL,
  status ENUM('BORRADOR','CONFIRMADA','PARCIAL','CERRADA','ANULADA') NOT NULL DEFAULT 'BORRADOR',
  notes TEXT NULL,
  assigned_at DATETIME NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_assign_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_assign_area FOREIGN KEY (area_id) REFERENCES areas(id),
  CONSTRAINT fk_assign_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE assignment_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assignment_id BIGINT UNSIGNED NOT NULL,
  asset_id BIGINT UNSIGNED NOT NULL,
  condition_out VARCHAR(255) NOT NULL DEFAULT 'Buen estado',
  returned_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_assignment_asset (assignment_id,asset_id),
  INDEX ix_assignment_pending (assignment_id,returned_at),
  CONSTRAINT fk_assign_item_header FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  CONSTRAINT fk_assign_item_asset FOREIGN KEY (asset_id) REFERENCES assets(id)
) ENGINE=InnoDB;

CREATE TABLE asset_returns (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  return_number VARCHAR(30) NOT NULL UNIQUE,
  assignment_id BIGINT UNSIGNED NOT NULL,
  status ENUM('BORRADOR','CONFIRMADA','ANULADA') NOT NULL DEFAULT 'BORRADOR',
  notes TEXT NULL,
  returned_at DATETIME NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_return_assignment FOREIGN KEY (assignment_id) REFERENCES assignments(id),
  CONSTRAINT fk_return_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE return_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  return_id BIGINT UNSIGNED NOT NULL,
  assignment_item_id BIGINT UNSIGNED NOT NULL,
  condition_in VARCHAR(255) NOT NULL,
  damage_notes VARCHAR(500) NULL,
  next_status_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_return_assignment_item (assignment_item_id),
  CONSTRAINT fk_return_item_header FOREIGN KEY (return_id) REFERENCES asset_returns(id) ON DELETE CASCADE,
  CONSTRAINT fk_return_item_assignment FOREIGN KEY (assignment_item_id) REFERENCES assignment_items(id),
  CONSTRAINT fk_return_item_status FOREIGN KEY (next_status_id) REFERENCES asset_statuses(id)
) ENGINE=InnoDB;

CREATE TABLE maintenances (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_id BIGINT UNSIGNED NOT NULL,
  type ENUM('PREVENTIVO','CORRECTIVO') NOT NULL,
  status ENUM('ABIERTO','CERRADO','CANCELADO') NOT NULL DEFAULT 'ABIERTO',
  previous_status_id INT UNSIGNED NULL,
  issue TEXT NULL,
  diagnosis TEXT NULL,
  actions TEXT NULL,
  parts_used TEXT NULL,
  cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  started_at DATETIME NOT NULL,
  finished_at DATETIME NULL,
  next_date DATE NULL,
  opened_by INT UNSIGNED NOT NULL,
  closed_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_maintenance_asset FOREIGN KEY (asset_id) REFERENCES assets(id),
  CONSTRAINT fk_maintenance_previous_status FOREIGN KEY (previous_status_id) REFERENCES asset_statuses(id),
  CONSTRAINT fk_maintenance_opened FOREIGN KEY (opened_by) REFERENCES users(id),
  CONSTRAINT fk_maintenance_closed FOREIGN KEY (closed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE asset_movements (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asset_id BIGINT UNSIGNED NOT NULL,
  movement_type VARCHAR(50) NOT NULL,
  reference_type VARCHAR(40) NULL,
  reference_id BIGINT UNSIGNED NULL,
  from_status_id INT UNSIGNED NULL,
  to_status_id INT UNSIGNED NULL,
  from_area_id INT UNSIGNED NULL,
  to_area_id INT UNSIGNED NULL,
  from_employee_id INT UNSIGNED NULL,
  to_employee_id INT UNSIGNED NULL,
  notes VARCHAR(500) NULL,
  user_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX ix_movement_asset_date (asset_id,created_at),
  CONSTRAINT fk_movement_asset FOREIGN KEY (asset_id) REFERENCES assets(id),
  CONSTRAINT fk_movement_from_status FOREIGN KEY (from_status_id) REFERENCES asset_statuses(id),
  CONSTRAINT fk_movement_to_status FOREIGN KEY (to_status_id) REFERENCES asset_statuses(id),
  CONSTRAINT fk_movement_from_area FOREIGN KEY (from_area_id) REFERENCES areas(id),
  CONSTRAINT fk_movement_to_area FOREIGN KEY (to_area_id) REFERENCES areas(id),
  CONSTRAINT fk_movement_from_employee FOREIGN KEY (from_employee_id) REFERENCES employees(id),
  CONSTRAINT fk_movement_to_employee FOREIGN KEY (to_employee_id) REFERENCES employees(id),
  CONSTRAINT fk_movement_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  module VARCHAR(80) NOT NULL,
  action VARCHAR(50) NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  old_values LONGTEXT NULL,
  new_values LONGTEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(500) NULL,
  created_at DATETIME NOT NULL,
  INDEX ix_audit_date (created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Datos iniciales
INSERT INTO roles(id,name,description) VALUES
(1,'ADMIN','Control total del sistema'),
(2,'TECNICO','Operación del inventario y movimientos'),
(3,'AUDITOR','Consulta y reportes');

INSERT INTO users(id,role_id,name,username,email,password_hash,active) VALUES
(1,1,'Administrador SIGATI','admin','ti.arequipa@solandra.local','$2y$12$xiwecU9euNq9rnpaqIQGnON/pHhL6VXuMquZaG3uI3GlPZQ96HjEW',1);

INSERT INTO areas(id,name) VALUES
(1,'Tecnología de la Información'),(2,'Administración'),(3,'Operaciones'),(4,'Mantenimiento'),
(5,'Seguridad'),(6,'Laboratorio'),(7,'Almacén'),(8,'Recursos Humanos'),(9,'Contabilidad'),
(10,'Logística'),(11,'Medio Ambiente'),(12,'Calidad'),(13,'Producción'),(14,'Sala de Control'),
(15,'Taller Mecánico'),(16,'Taller Eléctrico'),(17,'Servicios Generales'),(18,'Planeamiento'),
(19,'Compras'),(20,'Gerencia de Planta'),(21,'Comedor'),(22,'Campamento'),(23,'Garita Principal'),
(24,'Tratamiento de Agua'),(25,'Proyectos');

INSERT INTO locations(id,area_id,name) VALUES
(1,1,'Oficina TI'),(2,1,'Almacén TI'),(3,1,'Taller TI'),(4,2,'Oficina Administrativa'),
(5,3,'Zona Operativa'),(6,5,'Garita Principal'),(7,6,'Laboratorio Principal'),
(8,14,'Sala de Control'),(9,15,'Taller Mecánico'),(10,16,'Taller Eléctrico');

INSERT INTO asset_types(id,name,prefix) VALUES
(1,'PC','PC'),(2,'Laptop','LAP'),(3,'Monitor','MON'),(4,'Impresora','IMP'),
(5,'Celular','CEL'),(6,'Radio','RAD'),(7,'Switch','SW'),(8,'Access Point','AP'),
(9,'Servidor','SRV'),(10,'UPS','UPS'),(11,'Cámara','CAM'),(12,'NVR','NVR'),
(13,'Starlink','STL'),(14,'Accesorio','ACC'),(15,'Otro','OTR');

INSERT INTO asset_statuses(id,code,name,color) VALUES
(1,'DISPONIBLE','Disponible','success'),(2,'ASIGNADO','Asignado','primary'),
(3,'PRESTAMO','En préstamo','primary'),(4,'MANTENIMIENTO','Mantenimiento','warning'),
(5,'REPARACION','Reparación','danger'),(6,'ALMACEN','En almacén','secondary'),
(7,'EVALUACION','Pendiente de evaluación','warning'),(8,'BAJA_PENDIENTE','Pendiente de baja','danger'),
(9,'BAJA','Dado de baja','dark'),(10,'EXTRAVIADO','Extraviado','danger'),
(11,'ROBADO','Robado','danger'),(12,'TRANSITO','En tránsito','secondary');

INSERT INTO brands(id,name) VALUES
(1,'Dell'),(2,'HP'),(3,'Lenovo'),(4,'Epson'),(5,'Samsung'),(6,'Motorola'),
(7,'TP-Link'),(8,'Starlink'),(9,'LG'),(10,'Acer'),(11,'Brother'),(12,'Cisco');

INSERT INTO models(id,brand_id,name) VALUES
(1,1,'OptiPlex 7090'),(2,1,'Latitude 5420'),(3,2,'ProDesk 400 G7'),(4,2,'LaserJet Pro M404dn'),
(5,3,'ThinkPad E14'),(6,4,'EcoTank L6270'),(7,5,'Galaxy A34'),(8,6,'Moto G54'),
(9,7,'TL-SG3428'),(10,7,'EAP610'),(11,9,'24MP400'),(12,11,'DCP-L5650DN');

INSERT INTO suppliers(id,name) VALUES
(1,'Proveedor Lima TI'),(2,'Distribuidor Arequipa'),(3,'Compra corporativa SOLANDRA');

INSERT INTO employees(id,employee_code,first_name,last_name,email,phone,position,area_id) VALUES
(1,'SOL-AQP-001','Víctor','Mendoza','victor@solandra.local','999111111','Técnico TI',1),
(2,'SOL-AQP-002','Carlos','Quispe','carlos@solandra.local','999222222','Supervisor de Operaciones',3),
(3,'SOL-AQP-003','María','Torres','maria@solandra.local','999333333','Analista Administrativa',2),
(4,'SOL-AQP-004','José','Flores','jose@solandra.local','999444444','Técnico de Mantenimiento',4),
(5,'SOL-AQP-005','Ana','Ramos','ana@solandra.local','999555555','Analista de Laboratorio',6);

INSERT INTO asset_counters(asset_type_id,current_number) VALUES
(1,3),(2,3),(3,4),(4,2),(5,2),(6,2),(7,1),(8,1),(9,1),(10,1),(13,4);

INSERT INTO assets(id,asset_code,legacy_code,asset_type_id,brand_id,model_id,status_id,current_area_id,location_id,current_employee_id,serial_number,hostname,ip_address,mac_address,imei1,phone_number,purchase_date,invoice_number,supplier_id,cost,warranty_end,notes,created_by,updated_by) VALUES
(1,'AQP-PC-000001','FT277701',1,1,1,1,1,2,NULL,'DL-OPT-001','PC-AQP-001','192.168.20.31','00:11:22:33:44:01',NULL,NULL,'2024-02-10','FT2777',1,3200.00,'2027-02-10','Equipo de contingencia',1,1),
(2,'AQP-PC-000002','FT277702',1,2,3,1,1,2,NULL,'HP-PD-002','PC-AQP-002','192.168.20.32','00:11:22:33:44:02',NULL,NULL,'2024-02-10','FT2777',1,3000.00,'2027-02-10',NULL,1,1),
(3,'AQP-PC-000003','FT277703',1,1,1,1,3,5,NULL,'DL-OPT-003','PC-AQP-003','192.168.21.33','00:11:22:33:44:03',NULL,NULL,'2024-02-10','FT2777',1,3200.00,'2027-02-10',NULL,1,1),
(4,'AQP-LAP-000001','FT281101',2,3,5,1,1,2,NULL,'LN-E14-001','LAP-AQP-001','192.168.21.41','00:11:22:33:55:01',NULL,NULL,'2024-05-18','FT2811',1,3800.00,'2027-05-18','Incluye cargador y mochila',1,1),
(5,'AQP-LAP-000002','FT281102',2,1,2,1,1,2,NULL,'DL-LAT-002','LAP-AQP-002','192.168.21.42','00:11:22:33:55:02',NULL,NULL,'2024-05-18','FT2811',1,4200.00,'2027-05-18',NULL,1,1),
(6,'AQP-LAP-000003','FT281103',2,3,5,1,6,7,NULL,'LN-E14-003','LAP-AQP-003','192.168.20.43','00:11:22:33:55:03',NULL,NULL,'2024-05-18','FT2811',1,3800.00,'2027-05-18',NULL,1,1),
(7,'AQP-MON-000001','FT279901',3,9,11,1,1,2,NULL,'LG-MON-001',NULL,NULL,NULL,NULL,NULL,'2024-03-22','FT2799',1,620.00,'2027-03-22',NULL,1,1),
(8,'AQP-MON-000002','FT279902',3,9,11,1,1,2,NULL,'LG-MON-002',NULL,NULL,NULL,NULL,NULL,'2024-03-22','FT2799',1,620.00,'2027-03-22',NULL,1,1),
(9,'AQP-MON-000003','FT279903',3,9,11,1,3,5,NULL,'LG-MON-003',NULL,NULL,NULL,NULL,NULL,'2024-03-22','FT2799',1,620.00,'2027-03-22',NULL,1,1),
(10,'AQP-MON-000004','FT279904',3,9,11,1,6,7,NULL,'LG-MON-004',NULL,NULL,NULL,NULL,NULL,'2024-03-22','FT2799',1,620.00,'2027-03-22',NULL,1,1),
(11,'AQP-IMP-000001','FT270101',4,4,6,1,2,4,NULL,'EP-L6270-01','IMP-ADM-01','192.168.20.80','00:11:22:44:66:01',NULL,NULL,'2023-10-02','FT2701',2,1450.00,'2025-10-02','Impresora administrativa',1,1),
(12,'AQP-IMP-000002','FT270102',4,11,12,1,3,5,NULL,'BR-L5650-02','IMP-OPE-02','192.168.21.81','00:11:22:44:66:02',NULL,NULL,'2023-10-02','FT2701',2,2100.00,'2025-10-02','Impresora de alto volumen',1,1),
(13,'AQP-CEL-000001','FT290101',5,5,7,1,1,2,NULL,'SM-A34-001',NULL,NULL,NULL,'351111111111111','950000001','2025-01-15','FT2901',3,1100.00,'2026-01-15','Incluye cargador y funda',1,1),
(14,'AQP-CEL-000002','FT290102',5,6,8,1,5,6,NULL,'MO-G54-002',NULL,NULL,NULL,'352222222222222','950000002','2025-01-15','FT2901',3,900.00,'2026-01-15',NULL,1,1),
(15,'AQP-RAD-000001','FT266601',6,6,NULL,1,5,6,NULL,'RAD-001',NULL,NULL,NULL,NULL,NULL,'2023-06-12','FT2666',2,780.00,'2025-06-12','Canal Operaciones',1,1),
(16,'AQP-RAD-000002','FT266602',6,6,NULL,1,3,5,NULL,'RAD-002',NULL,NULL,NULL,NULL,NULL,'2023-06-12','FT2666',2,780.00,'2025-06-12','Canal Seguridad',1,1),
(17,'AQP-SW-000001','FT300101',7,7,9,1,1,1,NULL,'TPL-SW-001','SW-CORE-01','192.168.20.2','00:AA:BB:CC:DD:01',NULL,NULL,'2025-04-01','FT3001',3,1800.00,'2028-04-01','Switch administrable de prueba',1,1),
(18,'AQP-AP-000001','FT300201',8,7,10,1,3,5,NULL,'TPL-AP-001','AP-OPE-01','192.168.20.101','00:AA:BB:CC:EE:01',NULL,NULL,'2025-04-01','FT3002',3,650.00,'2028-04-01','AP de zona operativa',1,1);

INSERT INTO asset_specifications(asset_id,spec_key,spec_value) VALUES
(1,'Procesador','Intel Core i5'),(1,'RAM','16 GB'),(1,'Almacenamiento','SSD 512 GB'),
(4,'Procesador','Intel Core i5'),(4,'RAM','16 GB'),(4,'Almacenamiento','SSD 512 GB'),
(11,'Tipo de tóner','Botellas Epson 544'),(11,'Conectividad','Red / Wi-Fi'),
(17,'Puertos','24 Gigabit'),(17,'PoE','Sí');

INSERT INTO asset_movements(asset_id,movement_type,to_status_id,to_area_id,notes,user_id)
SELECT id,'REGISTRO',status_id,current_area_id,'Carga inicial del sistema',1 FROM assets;

SET FOREIGN_KEY_CHECKS = 1;

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_generate_asset_code$$
CREATE PROCEDURE sp_generate_asset_code(IN p_asset_type_id INT, OUT p_code VARCHAR(40))
BEGIN
  DECLARE v_prefix VARCHAR(8);
  DECLARE v_number INT;
  SELECT prefix INTO v_prefix FROM asset_types WHERE id=p_asset_type_id AND active=1;
  IF v_prefix IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Tipo de activo inválido'; END IF;
  INSERT INTO asset_counters(asset_type_id,current_number) VALUES(p_asset_type_id,0)
    ON DUPLICATE KEY UPDATE current_number=current_number;
  UPDATE asset_counters SET current_number=LAST_INSERT_ID(current_number+1) WHERE asset_type_id=p_asset_type_id;
  SET v_number=LAST_INSERT_ID();
  SET p_code=CONCAT('AQP-',v_prefix,'-',LPAD(v_number,6,'0'));
END$$

DROP PROCEDURE IF EXISTS sp_assignment_create$$
CREATE PROCEDURE sp_assignment_create(
  IN p_employee_id INT, IN p_area_id INT, IN p_notes TEXT, IN p_user_id INT,
  OUT p_assignment_id BIGINT, OUT p_assignment_number VARCHAR(30)
)
BEGIN
  DECLARE v_year SMALLINT;
  DECLARE v_num INT;
  DECLARE v_area INT;
  DECLARE v_employee_exists INT DEFAULT 0;
  SET v_year=YEAR(CURDATE());
  SELECT COUNT(*),MAX(area_id) INTO v_employee_exists,v_area FROM employees WHERE id=p_employee_id AND active=1;
  IF v_employee_exists=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El trabajador no existe o está inactivo'; END IF;
  IF p_area_id IS NOT NULL AND p_area_id>0 THEN SET v_area=p_area_id; END IF;
  INSERT INTO document_counters(document_type,document_year,current_number) VALUES('ASG',v_year,0)
    ON DUPLICATE KEY UPDATE current_number=current_number;
  UPDATE document_counters SET current_number=LAST_INSERT_ID(current_number+1)
    WHERE document_type='ASG' AND document_year=v_year;
  SET v_num=LAST_INSERT_ID();
  SET p_assignment_number=CONCAT('ASG-',v_year,'-',LPAD(v_num,6,'0'));
  INSERT INTO assignments(assignment_number,employee_id,area_id,status,notes,created_by)
  VALUES(p_assignment_number,p_employee_id,v_area,'BORRADOR',NULLIF(p_notes,''),p_user_id);
  SET p_assignment_id=LAST_INSERT_ID();
END$$

DROP PROCEDURE IF EXISTS sp_assignment_add_asset$$
CREATE PROCEDURE sp_assignment_add_asset(
  IN p_assignment_id BIGINT, IN p_asset_id BIGINT, IN p_condition VARCHAR(255), IN p_user_id INT
)
BEGIN
  DECLARE v_status_code VARCHAR(40);
  DECLARE v_header_status VARCHAR(20);
  SELECT status INTO v_header_status FROM assignments WHERE id=p_assignment_id FOR UPDATE;
  IF v_header_status IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignación inexistente'; END IF;
  IF v_header_status<>'BORRADOR' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La asignación ya no está en borrador'; END IF;
  SELECT s.code INTO v_status_code FROM assets a JOIN asset_statuses s ON s.id=a.status_id WHERE a.id=p_asset_id AND a.active=1 FOR UPDATE;
  IF v_status_code IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Activo inexistente'; END IF;
  IF v_status_code<>'DISPONIBLE' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Uno de los activos ya no está disponible'; END IF;
  INSERT INTO assignment_items(assignment_id,asset_id,condition_out)
  VALUES(p_assignment_id,p_asset_id,COALESCE(NULLIF(p_condition,''),'Buen estado'));
END$$

DROP PROCEDURE IF EXISTS sp_assignment_confirm$$
CREATE PROCEDURE sp_assignment_confirm(IN p_assignment_id BIGINT, IN p_user_id INT)
BEGIN
  DECLARE v_employee INT;
  DECLARE v_area INT;
  DECLARE v_status INT;
  DECLARE v_count INT;
  SELECT employee_id,area_id INTO v_employee,v_area FROM assignments WHERE id=p_assignment_id AND status='BORRADOR' FOR UPDATE;
  IF v_employee IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignación no disponible para confirmar'; END IF;
  SELECT COUNT(*) INTO v_count FROM assignment_items WHERE assignment_id=p_assignment_id;
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La asignación no contiene activos'; END IF;
  SELECT id INTO v_status FROM asset_statuses WHERE code='ASIGNADO';
  INSERT INTO asset_movements(asset_id,movement_type,reference_type,reference_id,from_status_id,to_status_id,from_area_id,to_area_id,from_employee_id,to_employee_id,notes,user_id)
  SELECT a.id,'ASIGNACION','assignment',p_assignment_id,a.status_id,v_status,a.current_area_id,v_area,a.current_employee_id,v_employee,CONCAT('Asignado mediante ',x.assignment_number),p_user_id
  FROM assignment_items ai JOIN assets a ON a.id=ai.asset_id JOIN assignments x ON x.id=ai.assignment_id
  WHERE ai.assignment_id=p_assignment_id;
  UPDATE assets a JOIN assignment_items ai ON ai.asset_id=a.id
  SET a.status_id=v_status,a.current_employee_id=v_employee,a.current_area_id=v_area,a.updated_by=p_user_id
  WHERE ai.assignment_id=p_assignment_id;
  UPDATE assignments SET status='CONFIRMADA',assigned_at=NOW() WHERE id=p_assignment_id;
END$$

DROP PROCEDURE IF EXISTS sp_return_create$$
CREATE PROCEDURE sp_return_create(
  IN p_assignment_id BIGINT, IN p_notes TEXT, IN p_user_id INT,
  OUT p_return_id BIGINT, OUT p_return_number VARCHAR(30)
)
BEGIN
  DECLARE v_year SMALLINT;
  DECLARE v_num INT;
  DECLARE v_ok INT;
  SELECT COUNT(*) INTO v_ok FROM assignments WHERE id=p_assignment_id AND status IN('CONFIRMADA','PARCIAL');
  IF v_ok=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignación no disponible para devolución'; END IF;
  SET v_year=YEAR(CURDATE());
  INSERT INTO document_counters(document_type,document_year,current_number) VALUES('DEV',v_year,0)
    ON DUPLICATE KEY UPDATE current_number=current_number;
  UPDATE document_counters SET current_number=LAST_INSERT_ID(current_number+1)
    WHERE document_type='DEV' AND document_year=v_year;
  SET v_num=LAST_INSERT_ID();
  SET p_return_number=CONCAT('DEV-',v_year,'-',LPAD(v_num,6,'0'));
  INSERT INTO asset_returns(return_number,assignment_id,status,notes,created_by)
  VALUES(p_return_number,p_assignment_id,'BORRADOR',NULLIF(p_notes,''),p_user_id);
  SET p_return_id=LAST_INSERT_ID();
END$$

DROP PROCEDURE IF EXISTS sp_return_asset$$
CREATE PROCEDURE sp_return_asset(
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
  SELECT assignment_id INTO v_return_assignment FROM asset_returns WHERE id=p_return_id AND status='BORRADOR' FOR UPDATE;
  SELECT ai.asset_id,ai.assignment_id INTO v_asset,v_assignment FROM assignment_items ai WHERE ai.id=p_assignment_item_id AND ai.returned_at IS NULL FOR UPDATE;
  IF v_asset IS NULL OR v_assignment<>v_return_assignment THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El activo no pertenece a la asignación o ya fue devuelto'; END IF;
  IF NOT EXISTS(SELECT 1 FROM asset_statuses WHERE id=p_next_status_id AND active=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Estado posterior inválido'; END IF;
  SELECT status_id,current_area_id,current_employee_id INTO v_from_status,v_from_area,v_from_employee FROM assets WHERE id=v_asset FOR UPDATE;
  INSERT INTO return_items(return_id,assignment_item_id,condition_in,damage_notes,next_status_id)
  VALUES(p_return_id,p_assignment_item_id,COALESCE(NULLIF(p_condition,''),'Sin especificar'),NULLIF(p_damage,''),p_next_status_id);
  UPDATE assignment_items SET returned_at=NOW() WHERE id=p_assignment_item_id;
  UPDATE assets SET status_id=p_next_status_id,current_employee_id=NULL,updated_by=p_user_id WHERE id=v_asset;
  INSERT INTO asset_movements(asset_id,movement_type,reference_type,reference_id,from_status_id,to_status_id,from_area_id,to_area_id,from_employee_id,to_employee_id,notes,user_id)
  VALUES(v_asset,'DEVOLUCION','return',p_return_id,v_from_status,p_next_status_id,v_from_area,v_from_area,v_from_employee,NULL,COALESCE(NULLIF(p_damage,''),'Equipo recibido sin daños reportados'),p_user_id);
END$$

DROP PROCEDURE IF EXISTS sp_return_confirm$$
CREATE PROCEDURE sp_return_confirm(IN p_return_id BIGINT, IN p_user_id INT)
BEGIN
  DECLARE v_assignment BIGINT;
  DECLARE v_items INT;
  DECLARE v_pending INT;
  SELECT assignment_id INTO v_assignment FROM asset_returns WHERE id=p_return_id AND status='BORRADOR' FOR UPDATE;
  IF v_assignment IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Devolución no disponible para confirmar'; END IF;
  SELECT COUNT(*) INTO v_items FROM return_items WHERE return_id=p_return_id;
  IF v_items=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La devolución no contiene equipos'; END IF;
  UPDATE asset_returns SET status='CONFIRMADA',returned_at=NOW() WHERE id=p_return_id;
  SELECT COUNT(*) INTO v_pending FROM assignment_items WHERE assignment_id=v_assignment AND returned_at IS NULL;
  UPDATE assignments SET status=IF(v_pending=0,'CERRADA','PARCIAL') WHERE id=v_assignment;
END$$

DROP PROCEDURE IF EXISTS sp_maintenance_open$$
CREATE PROCEDURE sp_maintenance_open(
  IN p_asset_id BIGINT, IN p_type VARCHAR(20), IN p_issue TEXT, IN p_diagnosis TEXT,
  IN p_actions TEXT, IN p_cost DECIMAL(12,2), IN p_user_id INT, OUT p_maintenance_id BIGINT
)
BEGIN
  DECLARE v_old_status INT;
  DECLARE v_maintenance_status INT;
  DECLARE v_area INT;
  DECLARE v_employee INT;
  SELECT status_id,current_area_id,current_employee_id INTO v_old_status,v_area,v_employee FROM assets WHERE id=p_asset_id AND active=1 FOR UPDATE;
  IF v_old_status IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Activo inexistente'; END IF;
  IF EXISTS(SELECT 1 FROM maintenances WHERE asset_id=p_asset_id AND status='ABIERTO') THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El activo ya tiene un mantenimiento abierto'; END IF;
  SELECT id INTO v_maintenance_status FROM asset_statuses WHERE code='MANTENIMIENTO';
  INSERT INTO maintenances(asset_id,type,status,previous_status_id,issue,diagnosis,actions,cost,started_at,opened_by)
  VALUES(p_asset_id,IF(p_type='CORRECTIVO','CORRECTIVO','PREVENTIVO'),'ABIERTO',v_old_status,NULLIF(p_issue,''),NULLIF(p_diagnosis,''),NULLIF(p_actions,''),COALESCE(p_cost,0),NOW(),p_user_id);
  SET p_maintenance_id=LAST_INSERT_ID();
  UPDATE assets SET status_id=v_maintenance_status,updated_by=p_user_id WHERE id=p_asset_id;
  INSERT INTO asset_movements(asset_id,movement_type,reference_type,reference_id,from_status_id,to_status_id,from_area_id,to_area_id,from_employee_id,to_employee_id,notes,user_id)
  VALUES(p_asset_id,'MANTENIMIENTO','maintenance',p_maintenance_id,v_old_status,v_maintenance_status,v_area,v_area,v_employee,v_employee,COALESCE(NULLIF(p_issue,''),'Ingreso a mantenimiento'),p_user_id);
END$$

DROP PROCEDURE IF EXISTS sp_maintenance_close$$
CREATE PROCEDURE sp_maintenance_close(
  IN p_maintenance_id BIGINT, IN p_diagnosis TEXT, IN p_actions TEXT, IN p_parts TEXT,
  IN p_cost DECIMAL(12,2), IN p_next_date DATE, IN p_user_id INT
)
BEGIN
  DECLARE v_asset BIGINT;
  DECLARE v_restore_status INT;
  DECLARE v_current_status INT;
  DECLARE v_area INT;
  DECLARE v_employee INT;
  SELECT asset_id,previous_status_id INTO v_asset,v_restore_status FROM maintenances WHERE id=p_maintenance_id AND status='ABIERTO' FOR UPDATE;
  IF v_asset IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Mantenimiento no disponible para cerrar'; END IF;
  SELECT status_id,current_area_id,current_employee_id INTO v_current_status,v_area,v_employee FROM assets WHERE id=v_asset FOR UPDATE;
  IF v_restore_status IS NULL THEN SELECT id INTO v_restore_status FROM asset_statuses WHERE code='DISPONIBLE'; END IF;
  UPDATE maintenances SET status='CERRADO',diagnosis=NULLIF(p_diagnosis,''),actions=NULLIF(p_actions,''),parts_used=NULLIF(p_parts,''),cost=COALESCE(p_cost,0),next_date=p_next_date,finished_at=NOW(),closed_by=p_user_id WHERE id=p_maintenance_id;
  UPDATE assets SET status_id=v_restore_status,updated_by=p_user_id WHERE id=v_asset;
  INSERT INTO asset_movements(asset_id,movement_type,reference_type,reference_id,from_status_id,to_status_id,from_area_id,to_area_id,from_employee_id,to_employee_id,notes,user_id)
  VALUES(v_asset,'CIERRE_MANTENIMIENTO','maintenance',p_maintenance_id,v_current_status,v_restore_status,v_area,v_area,v_employee,v_employee,COALESCE(NULLIF(p_actions,''),'Mantenimiento finalizado'),p_user_id);
END$$

DELIMITER ;

-- Vistas para dashboard y reportes
DROP VIEW IF EXISTS vw_inventory_general;
CREATE VIEW vw_inventory_general AS
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
FROM assets a
JOIN asset_types t ON t.id=a.asset_type_id
JOIN asset_statuses s ON s.id=a.status_id
LEFT JOIN brands b ON b.id=a.brand_id
LEFT JOIN models m ON m.id=a.model_id
LEFT JOIN areas ar ON ar.id=a.current_area_id
LEFT JOIN locations l ON l.id=a.location_id
LEFT JOIN employees e ON e.id=a.current_employee_id
LEFT JOIN suppliers sup ON sup.id=a.supplier_id
WHERE a.active=1;

DROP VIEW IF EXISTS vw_assets_by_status;
CREATE VIEW vw_assets_by_status AS
SELECT s.id,s.name,s.code,s.color,COUNT(a.id) total
FROM asset_statuses s LEFT JOIN assets a ON a.status_id=s.id AND a.active=1
WHERE s.active=1 GROUP BY s.id,s.name,s.code,s.color HAVING total>0 ORDER BY total DESC;

DROP VIEW IF EXISTS vw_assets_by_type;
CREATE VIEW vw_assets_by_type AS
SELECT t.id,t.name,t.prefix,COUNT(a.id) total
FROM asset_types t LEFT JOIN assets a ON a.asset_type_id=t.id AND a.active=1
WHERE t.active=1 GROUP BY t.id,t.name,t.prefix HAVING total>0 ORDER BY total DESC;

DROP VIEW IF EXISTS vw_assets_by_area;
CREATE VIEW vw_assets_by_area AS
SELECT COALESCE(ar.id,0) id,COALESCE(ar.name,'Sin área') name,COUNT(a.id) total
FROM assets a LEFT JOIN areas ar ON ar.id=a.current_area_id
WHERE a.active=1 GROUP BY ar.id,ar.name ORDER BY total DESC;

DROP VIEW IF EXISTS vw_dashboard_summary;
CREATE VIEW vw_dashboard_summary AS
SELECT
  (SELECT COUNT(*) FROM assets WHERE active=1) total_assets,
  (SELECT COUNT(*) FROM assets a JOIN asset_statuses s ON s.id=a.status_id WHERE a.active=1 AND s.code='ASIGNADO') assigned_assets,
  (SELECT COUNT(*) FROM assets a JOIN asset_statuses s ON s.id=a.status_id WHERE a.active=1 AND s.code='DISPONIBLE') available_assets,
  (SELECT COUNT(*) FROM assets a JOIN asset_statuses s ON s.id=a.status_id WHERE a.active=1 AND s.code IN('MANTENIMIENTO','REPARACION')) maintenance_assets,
  (SELECT COUNT(*) FROM employees WHERE active=1) total_employees,
  (SELECT COUNT(*) FROM assignments WHERE status IN('CONFIRMADA','PARCIAL')) active_assignments,
  (SELECT COUNT(*) FROM maintenances WHERE status='ABIERTO') open_maintenances;

-- Asignación demostrativa, creada mediante los procedimientos del sistema
CALL sp_assignment_create(3,2,'Asignación inicial de demostración',1,@demo_assignment,@demo_assignment_number);
CALL sp_assignment_add_asset(@demo_assignment,4,'Buen estado, incluye cargador y mochila',1);
CALL sp_assignment_add_asset(@demo_assignment,7,'Buen estado, incluye cable de poder y HDMI',1);
CALL sp_assignment_confirm(@demo_assignment,1);
