-- SIGATI SOLANDRA - Sede Arequipa
-- Compatible con MariaDB 10.4+ (XAMPP) y MySQL 8+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS sigati_solandra;
CREATE DATABASE sigati_solandra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sigati_solandra;

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(40) NOT NULL UNIQUE,
  descripcion VARCHAR(150) NULL
) ENGINE=InnoDB;

CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rol_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  usuario VARCHAR(60) NOT NULL UNIQUE,
  correo VARCHAR(150) NULL UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_ingreso_en DATETIME NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE areas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE ubicaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  area_id INT UNSIGNED NULL,
  nombre VARCHAR(140) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_location_area (area_id,nombre),
  CONSTRAINT fk_locations_area FOREIGN KEY (area_id) REFERENCES areas(id)
) ENGINE=InnoDB;

CREATE TABLE trabajadores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_trabajador VARCHAR(50) NOT NULL UNIQUE,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NULL,
  telefono VARCHAR(30) NULL,
  cargo VARCHAR(120) NULL,
  area_id INT UNSIGNED NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX ix_employees_name (apellidos,nombres),
  CONSTRAINT fk_employees_area FOREIGN KEY (area_id) REFERENCES areas(id)
) ENGINE=InnoDB;

CREATE TABLE tipos_activo (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  prefijo VARCHAR(8) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE estados_activo (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(40) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  color VARCHAR(30) NOT NULL DEFAULT 'secondary',
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE marcas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE modelos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  marca_id INT UNSIGNED NULL,
  nombre VARCHAR(120) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_model_brand (marca_id,nombre),
  CONSTRAINT fk_models_brand FOREIGN KEY (marca_id) REFERENCES marcas(id)
) ENGINE=InnoDB;

CREATE TABLE proveedores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE contadores_activo (
  tipo_activo_id INT UNSIGNED PRIMARY KEY,
  numero_actual INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_counter_type FOREIGN KEY (tipo_activo_id) REFERENCES tipos_activo(id)
) ENGINE=InnoDB;

CREATE TABLE contadores_documento (
  tipo_documento VARCHAR(15) NOT NULL,
  anio_documento SMALLINT UNSIGNED NOT NULL,
  numero_actual INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(tipo_documento,anio_documento)
) ENGINE=InnoDB;

CREATE TABLE activos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NULL UNIQUE,
  codigo_activo VARCHAR(40) NOT NULL UNIQUE,
  codigo_anterior VARCHAR(80) NULL,
  tipo_activo_id INT UNSIGNED NOT NULL,
  marca_id INT UNSIGNED NULL,
  modelo_id INT UNSIGNED NULL,
  estado_id INT UNSIGNED NOT NULL,
  area_actual_id INT UNSIGNED NULL,
  ubicacion_id INT UNSIGNED NULL,
  trabajador_actual_id INT UNSIGNED NULL,
  numero_serie VARCHAR(150) NULL,
  nombre_equipo VARCHAR(120) NULL,
  direccion_ip VARCHAR(45) NULL,
  direccion_mac VARCHAR(30) NULL,
  imei1 VARCHAR(30) NULL,
  imei2 VARCHAR(30) NULL,
  numero_telefono VARCHAR(30) NULL,
  fecha_compra DATE NULL,
  numero_factura VARCHAR(80) NULL,
  proveedor_id INT UNSIGNED NULL,
  costo DECIMAL(12,2) NULL,
  fin_garantia DATE NULL,
  observaciones TEXT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_por INT UNSIGNED NULL,
  actualizado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX ix_assets_search (codigo_activo,codigo_anterior,numero_serie),
  INDEX ix_assets_status (estado_id),
  INDEX ix_assets_area (area_actual_id),
  INDEX ix_assets_employee (trabajador_actual_id),
  CONSTRAINT fk_assets_type FOREIGN KEY (tipo_activo_id) REFERENCES tipos_activo(id),
  CONSTRAINT fk_assets_brand FOREIGN KEY (marca_id) REFERENCES marcas(id),
  CONSTRAINT fk_assets_model FOREIGN KEY (modelo_id) REFERENCES modelos(id),
  CONSTRAINT fk_assets_status FOREIGN KEY (estado_id) REFERENCES estados_activo(id),
  CONSTRAINT fk_assets_area FOREIGN KEY (area_actual_id) REFERENCES areas(id),
  CONSTRAINT fk_assets_location FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id),
  CONSTRAINT fk_assets_employee FOREIGN KEY (trabajador_actual_id) REFERENCES trabajadores(id),
  CONSTRAINT fk_assets_supplier FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
  CONSTRAINT fk_assets_created_by FOREIGN KEY (creado_por) REFERENCES usuarios(id),
  CONSTRAINT fk_assets_updated_by FOREIGN KEY (actualizado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE especificaciones_activo (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activo_id BIGINT UNSIGNED NOT NULL,
  clave_especificacion VARCHAR(100) NOT NULL,
  valor_especificacion VARCHAR(255) NOT NULL,
  UNIQUE KEY uq_asset_spec (activo_id,clave_especificacion),
  CONSTRAINT fk_specs_asset FOREIGN KEY (activo_id) REFERENCES activos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE asignaciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  numero_asignacion VARCHAR(30) NOT NULL UNIQUE,
  trabajador_id INT UNSIGNED NOT NULL,
  area_id INT UNSIGNED NULL,
  estado ENUM('BORRADOR','CONFIRMADA','PARCIAL','CERRADA','ANULADA') NOT NULL DEFAULT 'BORRADOR',
  observaciones TEXT NULL,
  asignado_en DATETIME NULL,
  creado_por INT UNSIGNED NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_assign_employee FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id),
  CONSTRAINT fk_assign_area FOREIGN KEY (area_id) REFERENCES areas(id),
  CONSTRAINT fk_assign_user FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE items_asignacion (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  asignacion_id BIGINT UNSIGNED NOT NULL,
  activo_id BIGINT UNSIGNED NOT NULL,
  condicion_salida VARCHAR(255) NOT NULL DEFAULT 'Buen estado',
  devuelto_en DATETIME NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_assignment_asset (asignacion_id,activo_id),
  INDEX ix_assignment_pending (asignacion_id,devuelto_en),
  CONSTRAINT fk_assign_item_header FOREIGN KEY (asignacion_id) REFERENCES asignaciones(id) ON DELETE CASCADE,
  CONSTRAINT fk_assign_item_asset FOREIGN KEY (activo_id) REFERENCES activos(id)
) ENGINE=InnoDB;

CREATE TABLE devoluciones_activo (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  numero_devolucion VARCHAR(30) NOT NULL UNIQUE,
  asignacion_id BIGINT UNSIGNED NOT NULL,
  estado ENUM('BORRADOR','CONFIRMADA','ANULADA') NOT NULL DEFAULT 'BORRADOR',
  observaciones TEXT NULL,
  devuelto_en DATETIME NULL,
  creado_por INT UNSIGNED NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_return_assignment FOREIGN KEY (asignacion_id) REFERENCES asignaciones(id),
  CONSTRAINT fk_return_user FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE items_devolucion (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  devolucion_id BIGINT UNSIGNED NOT NULL,
  item_asignacion_id BIGINT UNSIGNED NOT NULL,
  condicion_entrada VARCHAR(255) NOT NULL,
  observaciones_danos VARCHAR(500) NULL,
  siguiente_estado_id INT UNSIGNED NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_return_assignment_item (item_asignacion_id),
  CONSTRAINT fk_return_item_header FOREIGN KEY (devolucion_id) REFERENCES devoluciones_activo(id) ON DELETE CASCADE,
  CONSTRAINT fk_return_item_assignment FOREIGN KEY (item_asignacion_id) REFERENCES items_asignacion(id),
  CONSTRAINT fk_return_item_status FOREIGN KEY (siguiente_estado_id) REFERENCES estados_activo(id)
) ENGINE=InnoDB;

CREATE TABLE mantenimientos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activo_id BIGINT UNSIGNED NOT NULL,
  tipo ENUM('PREVENTIVO','CORRECTIVO') NOT NULL,
  estado ENUM('ABIERTO','CERRADO','CANCELADO') NOT NULL DEFAULT 'ABIERTO',
  estado_anterior_id INT UNSIGNED NULL,
  problema TEXT NULL,
  diagnostico TEXT NULL,
  acciones TEXT NULL,
  repuestos_usados TEXT NULL,
  costo DECIMAL(12,2) NOT NULL DEFAULT 0,
  iniciado_en DATETIME NOT NULL,
  finalizado_en DATETIME NULL,
  proxima_fecha DATE NULL,
  abierto_por INT UNSIGNED NOT NULL,
  cerrado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_maintenance_asset FOREIGN KEY (activo_id) REFERENCES activos(id),
  CONSTRAINT fk_maintenance_previous_status FOREIGN KEY (estado_anterior_id) REFERENCES estados_activo(id),
  CONSTRAINT fk_maintenance_opened FOREIGN KEY (abierto_por) REFERENCES usuarios(id),
  CONSTRAINT fk_maintenance_closed FOREIGN KEY (cerrado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE movimientos_activo (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activo_id BIGINT UNSIGNED NOT NULL,
  tipo_movimiento VARCHAR(50) NOT NULL,
  tipo_referencia VARCHAR(40) NULL,
  referencia_id BIGINT UNSIGNED NULL,
  estado_origen_id INT UNSIGNED NULL,
  estado_destino_id INT UNSIGNED NULL,
  area_origen_id INT UNSIGNED NULL,
  area_destino_id INT UNSIGNED NULL,
  trabajador_origen_id INT UNSIGNED NULL,
  trabajador_destino_id INT UNSIGNED NULL,
  observaciones VARCHAR(500) NULL,
  usuario_id INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX ix_movement_asset_date (activo_id,creado_en),
  CONSTRAINT fk_movement_asset FOREIGN KEY (activo_id) REFERENCES activos(id),
  CONSTRAINT fk_movement_from_status FOREIGN KEY (estado_origen_id) REFERENCES estados_activo(id),
  CONSTRAINT fk_movement_to_status FOREIGN KEY (estado_destino_id) REFERENCES estados_activo(id),
  CONSTRAINT fk_movement_from_area FOREIGN KEY (area_origen_id) REFERENCES areas(id),
  CONSTRAINT fk_movement_to_area FOREIGN KEY (area_destino_id) REFERENCES areas(id),
  CONSTRAINT fk_movement_from_employee FOREIGN KEY (trabajador_origen_id) REFERENCES trabajadores(id),
  CONSTRAINT fk_movement_to_employee FOREIGN KEY (trabajador_destino_id) REFERENCES trabajadores(id),
  CONSTRAINT fk_movement_user FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE registros_auditoria (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NULL,
  modulo VARCHAR(80) NOT NULL,
  accion VARCHAR(50) NOT NULL,
  tipo_entidad VARCHAR(80) NOT NULL,
  entidad_id BIGINT UNSIGNED NULL,
  valores_anteriores LONGTEXT NULL,
  valores_nuevos LONGTEXT NULL,
  direccion_ip VARCHAR(45) NULL,
  navegador VARCHAR(500) NULL,
  creado_en DATETIME NOT NULL,
  INDEX ix_audit_date (creado_en),
  CONSTRAINT fk_audit_user FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Datos iniciales
INSERT INTO roles(id,nombre,descripcion) VALUES
(1,'ADMIN','Control total del sistema'),
(2,'TECNICO','Operacion del inventario y movimientos'),
(3,'AUDITOR','Consulta y reportes');

INSERT INTO usuarios(id,rol_id,nombre,usuario,correo,clave_hash,activo) VALUES
(1,1,'Administrador SIGATI','admin','ti.arequipa@solandra.local','$2y$12$xiwecU9euNq9rnpaqIQGnON/pHhL6VXuMquZaG3uI3GlPZQ96HjEW',1);

INSERT INTO areas(id,nombre) VALUES
(1,'Tecnologia de la Informacion'),(2,'Administracion'),(3,'Operaciones'),(4,'Mantenimiento'),
(5,'Seguridad'),(6,'Laboratorio'),(7,'Almacen'),(8,'Recursos Humanos'),(9,'Contabilidad'),
(10,'Logistica'),(11,'Medio Ambiente'),(12,'Calidad'),(13,'Produccion'),(14,'Sala de Control'),
(15,'Taller Mecanico'),(16,'Taller Electrico'),(17,'Servicios Generales'),(18,'Planeamiento'),
(19,'Compras'),(20,'Gerencia de Planta'),(21,'Comedor'),(22,'Campamento'),(23,'Garita Principal'),
(24,'Tratamiento de Agua'),(25,'Proyectos');

INSERT INTO ubicaciones(id,area_id,nombre) VALUES
(1,1,'Oficina TI'),(2,1,'Almacen TI'),(3,1,'Taller TI'),(4,2,'Oficina Administrativa'),
(5,3,'Zona Operativa'),(6,5,'Garita Principal'),(7,6,'Laboratorio Principal'),
(8,14,'Sala de Control'),(9,15,'Taller Mecanico'),(10,16,'Taller Electrico');

INSERT INTO tipos_activo(id,nombre,prefijo) VALUES
(1,'PC','PC'),(2,'Laptop','LAP'),(3,'Monitor','MON'),(4,'Impresora','IMP'),
(5,'Celular','CEL'),(6,'Radio','RAD'),(7,'Switch','SW'),(8,'Access Point','AP'),
(9,'Servidor','SRV'),(10,'UPS','UPS'),(11,'Camara','CAM'),(12,'NVR','NVR'),
(13,'Starlink','STL'),(14,'Accesorio','ACC'),(15,'Otro','OTR');

INSERT INTO estados_activo(id,codigo,nombre,color) VALUES
(1,'DISPONIBLE','Disponible','success'),(2,'ASIGNADO','Asignado','primary'),
(3,'PRESTAMO','En prestamo','primary'),(4,'MANTENIMIENTO','Mantenimiento','warning'),
(5,'REPARACION','Reparacion','danger'),(6,'ALMACEN','En almacen','secondary'),
(7,'EVALUACION','Pendiente de evaluacion','warning'),(8,'BAJA_PENDIENTE','Pendiente de baja','danger'),
(9,'BAJA','Dado de baja','dark'),(10,'EXTRAVIADO','Extraviado','danger'),
(11,'ROBADO','Robado','danger'),(12,'TRANSITO','En transito','secondary');

INSERT INTO marcas(id,nombre) VALUES
(1,'Dell'),(2,'HP'),(3,'Lenovo'),(4,'Epson'),(5,'Samsung'),(6,'Motorola'),
(7,'TP-Link'),(8,'Starlink'),(9,'LG'),(10,'Acer'),(11,'Brother'),(12,'Cisco');

INSERT INTO modelos(id,marca_id,nombre) VALUES
(1,1,'OptiPlex 7090'),(2,1,'Latitude 5420'),(3,2,'ProDesk 400 G7'),(4,2,'LaserJet Pro M404dn'),
(5,3,'ThinkPad E14'),(6,4,'EcoTank L6270'),(7,5,'Galaxy A34'),(8,6,'Moto G54'),
(9,7,'TL-SG3428'),(10,7,'EAP610'),(11,9,'24MP400'),(12,11,'DCP-L5650DN');

INSERT INTO proveedores(id,nombre) VALUES
(1,'Proveedor Lima TI'),(2,'Distribuidor Arequipa'),(3,'Compra corporativa SOLANDRA');

INSERT INTO trabajadores(id,codigo_trabajador,nombres,apellidos,correo,telefono,cargo,area_id) VALUES
(1,'SOL-AQP-001','Victor','Mendoza','victor@solandra.local','999111111','Tecnico TI',1),
(2,'SOL-AQP-002','Carlos','Quispe','carlos@solandra.local','999222222','Supervisor de Operaciones',3),
(3,'SOL-AQP-003','Maria','Torres','maria@solandra.local','999333333','Analista Administrativa',2),
(4,'SOL-AQP-004','Jose','Flores','jose@solandra.local','999444444','Tecnico de Mantenimiento',4),
(5,'SOL-AQP-005','Ana','Ramos','ana@solandra.local','999555555','Analista de Laboratorio',6);

INSERT INTO contadores_activo(tipo_activo_id,numero_actual) VALUES
(1,3),(2,3),(3,4),(4,2),(5,2),(6,2),(7,1),(8,1),(9,1),(10,1),(13,4);

INSERT INTO activos(id,codigo_activo,codigo_anterior,tipo_activo_id,marca_id,modelo_id,estado_id,area_actual_id,ubicacion_id,trabajador_actual_id,numero_serie,nombre_equipo,direccion_ip,direccion_mac,imei1,numero_telefono,fecha_compra,numero_factura,proveedor_id,costo,fin_garantia,observaciones,creado_por,actualizado_por) VALUES
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

INSERT INTO especificaciones_activo(activo_id,clave_especificacion,valor_especificacion) VALUES
(1,'Procesador','Intel Core i5'),(1,'RAM','16 GB'),(1,'Almacenamiento','SSD 512 GB'),
(4,'Procesador','Intel Core i5'),(4,'RAM','16 GB'),(4,'Almacenamiento','SSD 512 GB'),
(11,'Tipo de toner','Botellas Epson 544'),(11,'Conectividad','Red / Wi-Fi'),
(17,'Puertos','24 Gigabit'),(17,'PoE','Si');

INSERT INTO movimientos_activo(activo_id,tipo_movimiento,estado_destino_id,area_destino_id,observaciones,usuario_id)
SELECT id,'REGISTRO',estado_id,area_actual_id,'Carga inicial del sistema',1 FROM activos;

SET FOREIGN_KEY_CHECKS = 1;

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_generar_codigo_activo$$
CREATE PROCEDURE sp_generar_codigo_activo(IN p_asset_type_id INT, OUT p_code VARCHAR(40))
BEGIN
  DECLARE v_prefix VARCHAR(8);
  DECLARE v_number INT;
  SELECT prefijo INTO v_prefix FROM tipos_activo WHERE id=p_asset_type_id AND activo=1;
  IF v_prefix IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Tipo de activo invalido'; END IF;
  INSERT INTO contadores_activo(tipo_activo_id,numero_actual) VALUES(p_asset_type_id,0)
    ON DUPLICATE KEY UPDATE numero_actual=numero_actual;
  UPDATE contadores_activo SET numero_actual=LAST_INSERT_ID(numero_actual+1) WHERE tipo_activo_id=p_asset_type_id;
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
  SELECT COUNT(*),MAX(area_id) INTO v_employee_exists,v_area FROM trabajadores WHERE id=p_employee_id AND activo=1;
  IF v_employee_exists=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El trabajador no existe o esta inactivo'; END IF;
  IF p_area_id IS NOT NULL AND p_area_id>0 THEN SET v_area=p_area_id; END IF;
  INSERT INTO contadores_documento(tipo_documento,anio_documento,numero_actual) VALUES('ASG',v_year,0)
    ON DUPLICATE KEY UPDATE numero_actual=numero_actual;
  UPDATE contadores_documento SET numero_actual=LAST_INSERT_ID(numero_actual+1)
    WHERE tipo_documento='ASG' AND anio_documento=v_year;
  SET v_num=LAST_INSERT_ID();
  SET p_assignment_number=CONCAT('ASG-',v_year,'-',LPAD(v_num,6,'0'));
  INSERT INTO asignaciones(numero_asignacion,trabajador_id,area_id,estado,observaciones,creado_por)
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
  SELECT estado INTO v_header_status FROM asignaciones WHERE id=p_assignment_id FOR UPDATE;
  IF v_header_status IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignacion inexistente'; END IF;
  IF v_header_status<>'BORRADOR' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La asignacion ya no esta en borrador'; END IF;
  SELECT s.codigo INTO v_status_code FROM activos a JOIN estados_activo s ON s.id=a.estado_id WHERE a.id=p_asset_id AND a.activo=1 FOR UPDATE;
  IF v_status_code IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Activo inexistente'; END IF;
  IF v_status_code<>'DISPONIBLE' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Uno de los activos ya no esta disponible'; END IF;
  INSERT INTO items_asignacion(asignacion_id,activo_id,condicion_salida)
  VALUES(p_assignment_id,p_asset_id,COALESCE(NULLIF(p_condition,''),'Buen estado'));
END$$

DROP PROCEDURE IF EXISTS sp_confirmar_asignacion$$
CREATE PROCEDURE sp_confirmar_asignacion(IN p_assignment_id BIGINT, IN p_user_id INT)
BEGIN
  DECLARE v_employee INT;
  DECLARE v_area INT;
  DECLARE v_status INT;
  DECLARE v_count INT;
  SELECT trabajador_id,area_id INTO v_employee,v_area FROM asignaciones WHERE id=p_assignment_id AND estado='BORRADOR' FOR UPDATE;
  IF v_employee IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignacion no disponible para confirmar'; END IF;
  SELECT COUNT(*) INTO v_count FROM items_asignacion WHERE asignacion_id=p_assignment_id;
  IF v_count=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La asignacion no contiene activos'; END IF;
  SELECT id INTO v_status FROM estados_activo WHERE codigo='ASIGNADO';
  INSERT INTO movimientos_activo(activo_id,tipo_movimiento,tipo_referencia,referencia_id,estado_origen_id,estado_destino_id,area_origen_id,area_destino_id,trabajador_origen_id,trabajador_destino_id,observaciones,usuario_id)
  SELECT a.id,'ASIGNACION','assignment',p_assignment_id,a.estado_id,v_status,a.area_actual_id,v_area,a.trabajador_actual_id,v_employee,CONCAT('Asignado mediante ',x.numero_asignacion),p_user_id
  FROM items_asignacion ai JOIN activos a ON a.id=ai.activo_id JOIN asignaciones x ON x.id=ai.asignacion_id
  WHERE ai.asignacion_id=p_assignment_id;
  UPDATE activos a JOIN items_asignacion ai ON ai.activo_id=a.id
  SET a.estado_id=v_status,a.trabajador_actual_id=v_employee,a.area_actual_id=v_area,a.actualizado_por=p_user_id
  WHERE ai.asignacion_id=p_assignment_id;
  UPDATE asignaciones SET estado='CONFIRMADA',asignado_en=NOW() WHERE id=p_assignment_id;
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
  SELECT COUNT(*) INTO v_ok FROM asignaciones WHERE id=p_assignment_id AND estado IN('CONFIRMADA','PARCIAL');
  IF v_ok=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Asignacion no disponible para devolucion'; END IF;
  SET v_year=YEAR(CURDATE());
  INSERT INTO contadores_documento(tipo_documento,anio_documento,numero_actual) VALUES('DEV',v_year,0)
    ON DUPLICATE KEY UPDATE numero_actual=numero_actual;
  UPDATE contadores_documento SET numero_actual=LAST_INSERT_ID(numero_actual+1)
    WHERE tipo_documento='DEV' AND anio_documento=v_year;
  SET v_num=LAST_INSERT_ID();
  SET p_return_number=CONCAT('DEV-',v_year,'-',LPAD(v_num,6,'0'));
  INSERT INTO devoluciones_activo(numero_devolucion,asignacion_id,estado,observaciones,creado_por)
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
  SELECT asignacion_id INTO v_return_assignment FROM devoluciones_activo WHERE id=p_return_id AND estado='BORRADOR' FOR UPDATE;
  SELECT ai.activo_id,ai.asignacion_id INTO v_asset,v_assignment FROM items_asignacion ai WHERE ai.id=p_assignment_item_id AND ai.devuelto_en IS NULL FOR UPDATE;
  IF v_asset IS NULL OR v_assignment<>v_return_assignment THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El activo no pertenece a la asignacion o ya fue devuelto'; END IF;
  IF NOT EXISTS(SELECT 1 FROM estados_activo WHERE id=p_next_status_id AND activo=1) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Estado posterior invalido'; END IF;
  SELECT estado_id,area_actual_id,trabajador_actual_id INTO v_from_status,v_from_area,v_from_employee FROM activos WHERE id=v_asset FOR UPDATE;
  INSERT INTO items_devolucion(devolucion_id,item_asignacion_id,condicion_entrada,observaciones_danos,siguiente_estado_id)
  VALUES(p_return_id,p_assignment_item_id,COALESCE(NULLIF(p_condition,''),'Sin especificar'),NULLIF(p_damage,''),p_next_status_id);
  UPDATE items_asignacion SET devuelto_en=NOW() WHERE id=p_assignment_item_id;
  UPDATE activos SET estado_id=p_next_status_id,trabajador_actual_id=NULL,actualizado_por=p_user_id WHERE id=v_asset;
  INSERT INTO movimientos_activo(activo_id,tipo_movimiento,tipo_referencia,referencia_id,estado_origen_id,estado_destino_id,area_origen_id,area_destino_id,trabajador_origen_id,trabajador_destino_id,observaciones,usuario_id)
  VALUES(v_asset,'DEVOLUCION','return',p_return_id,v_from_status,p_next_status_id,v_from_area,v_from_area,v_from_employee,NULL,COALESCE(NULLIF(p_damage,''),'Equipo recibido sin danos reportados'),p_user_id);
END$$

DROP PROCEDURE IF EXISTS sp_confirmar_devolucion$$
CREATE PROCEDURE sp_confirmar_devolucion(IN p_return_id BIGINT, IN p_user_id INT)
BEGIN
  DECLARE v_assignment BIGINT;
  DECLARE v_items INT;
  DECLARE v_pending INT;
  SELECT asignacion_id INTO v_assignment FROM devoluciones_activo WHERE id=p_return_id AND estado='BORRADOR' FOR UPDATE;
  IF v_assignment IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Devolucion no disponible para confirmar'; END IF;
  SELECT COUNT(*) INTO v_items FROM items_devolucion WHERE devolucion_id=p_return_id;
  IF v_items=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La devolucion no contiene equipos'; END IF;
  UPDATE devoluciones_activo SET estado='CONFIRMADA',devuelto_en=NOW() WHERE id=p_return_id;
  SELECT COUNT(*) INTO v_pending FROM items_asignacion WHERE asignacion_id=v_assignment AND devuelto_en IS NULL;
  UPDATE asignaciones SET estado=IF(v_pending=0,'CERRADA','PARCIAL') WHERE id=v_assignment;
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
  SELECT estado_id,area_actual_id,trabajador_actual_id INTO v_old_status,v_area,v_employee FROM activos WHERE id=p_asset_id AND activo=1 FOR UPDATE;
  IF v_old_status IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Activo inexistente'; END IF;
  IF EXISTS(SELECT 1 FROM mantenimientos WHERE activo_id=p_asset_id AND estado='ABIERTO') THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El activo ya tiene un mantenimiento abierto'; END IF;
  SELECT id INTO v_maintenance_status FROM estados_activo WHERE codigo='MANTENIMIENTO';
  INSERT INTO mantenimientos(activo_id,tipo,estado,estado_anterior_id,problema,diagnostico,acciones,costo,iniciado_en,abierto_por)
  VALUES(p_asset_id,IF(p_type='CORRECTIVO','CORRECTIVO','PREVENTIVO'),'ABIERTO',v_old_status,NULLIF(p_issue,''),NULLIF(p_diagnosis,''),NULLIF(p_actions,''),COALESCE(p_cost,0),NOW(),p_user_id);
  SET p_maintenance_id=LAST_INSERT_ID();
  UPDATE activos SET estado_id=v_maintenance_status,actualizado_por=p_user_id WHERE id=p_asset_id;
  INSERT INTO movimientos_activo(activo_id,tipo_movimiento,tipo_referencia,referencia_id,estado_origen_id,estado_destino_id,area_origen_id,area_destino_id,trabajador_origen_id,trabajador_destino_id,observaciones,usuario_id)
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
  SELECT activo_id,estado_anterior_id INTO v_asset,v_restore_status FROM mantenimientos WHERE id=p_maintenance_id AND estado='ABIERTO' FOR UPDATE;
  IF v_asset IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Mantenimiento no disponible para cerrar'; END IF;
  SELECT estado_id,area_actual_id,trabajador_actual_id INTO v_current_status,v_area,v_employee FROM activos WHERE id=v_asset FOR UPDATE;
  IF v_restore_status IS NULL THEN SELECT id INTO v_restore_status FROM estados_activo WHERE codigo='DISPONIBLE'; END IF;
  UPDATE mantenimientos SET estado='CERRADO',diagnostico=NULLIF(p_diagnosis,''),acciones=NULLIF(p_actions,''),repuestos_usados=NULLIF(p_parts,''),costo=COALESCE(p_cost,0),proxima_fecha=p_next_date,finalizado_en=NOW(),cerrado_por=p_user_id WHERE id=p_maintenance_id;
  UPDATE activos SET estado_id=v_restore_status,actualizado_por=p_user_id WHERE id=v_asset;
  INSERT INTO movimientos_activo(activo_id,tipo_movimiento,tipo_referencia,referencia_id,estado_origen_id,estado_destino_id,area_origen_id,area_destino_id,trabajador_origen_id,trabajador_destino_id,observaciones,usuario_id)
  VALUES(v_asset,'CIERRE_MANTENIMIENTO','maintenance',p_maintenance_id,v_current_status,v_restore_status,v_area,v_area,v_employee,v_employee,COALESCE(NULLIF(p_actions,''),'Mantenimiento finalizado'),p_user_id);
END$$

DELIMITER ;

-- Vistas para dashboard y reportes
DROP VIEW IF EXISTS vw_inventario_general;
CREATE VIEW vw_inventario_general AS
SELECT
  a.codigo_activo AS codigo_activo,
  a.codigo_anterior AS codigo_anterior,
  t.nombre AS tipo,
  b.nombre AS marca,
  m.nombre AS modelo,
  a.numero_serie AS numero_serie,
  s.nombre AS estado,
  ar.nombre AS area,
  l.nombre AS ubicacion,
  CONCAT_WS(' ',e.nombres,e.apellidos) AS responsable,
  a.nombre_equipo,
  a.direccion_ip AS direccion_ip,
  a.direccion_mac AS direccion_mac,
  a.imei1,
  a.numero_telefono AS numero_telefono,
  DATE_FORMAT(a.fecha_compra,'%Y-%m-%d') AS fecha_compra,
  a.numero_factura AS numero_factura,
  sup.nombre AS proveedor,
  a.costo AS costo,
  DATE_FORMAT(a.fin_garantia,'%Y-%m-%d') AS fin_garantia,
  DATE_FORMAT(a.creado_en,'%Y-%m-%d %H:%i') AS creado_en
FROM activos a
JOIN tipos_activo t ON t.id=a.tipo_activo_id
JOIN estados_activo s ON s.id=a.estado_id
LEFT JOIN marcas b ON b.id=a.marca_id
LEFT JOIN modelos m ON m.id=a.modelo_id
LEFT JOIN areas ar ON ar.id=a.area_actual_id
LEFT JOIN ubicaciones l ON l.id=a.ubicacion_id
LEFT JOIN trabajadores e ON e.id=a.trabajador_actual_id
LEFT JOIN proveedores sup ON sup.id=a.proveedor_id
WHERE a.activo=1;

DROP VIEW IF EXISTS vw_activos_por_estado;
CREATE VIEW vw_activos_por_estado AS
SELECT s.id,s.nombre,s.codigo,s.color,COUNT(a.id) total
FROM estados_activo s LEFT JOIN activos a ON a.estado_id=s.id AND a.activo=1
WHERE s.activo=1 GROUP BY s.id,s.nombre,s.codigo,s.color HAVING total>0 ORDER BY total DESC;

DROP VIEW IF EXISTS vw_activos_por_tipo;
CREATE VIEW vw_activos_por_tipo AS
SELECT t.id,t.nombre,t.prefijo,COUNT(a.id) total
FROM tipos_activo t LEFT JOIN activos a ON a.tipo_activo_id=t.id AND a.activo=1
WHERE t.activo=1 GROUP BY t.id,t.nombre,t.prefijo HAVING total>0 ORDER BY total DESC;

DROP VIEW IF EXISTS vw_activos_por_area;
CREATE VIEW vw_activos_por_area AS
SELECT COALESCE(ar.id,0) id,COALESCE(ar.nombre,'Sin area') nombre,COUNT(a.id) total
FROM activos a LEFT JOIN areas ar ON ar.id=a.area_actual_id
WHERE a.activo=1 GROUP BY ar.id,ar.nombre ORDER BY total DESC;

DROP VIEW IF EXISTS vw_resumen_panel;
CREATE VIEW vw_resumen_panel AS
SELECT
  (SELECT COUNT(*) FROM activos WHERE activo=1) total_activos,
  (SELECT COUNT(*) FROM activos a JOIN estados_activo s ON s.id=a.estado_id WHERE a.activo=1 AND s.codigo='ASIGNADO') activos_asignados,
  (SELECT COUNT(*) FROM activos a JOIN estados_activo s ON s.id=a.estado_id WHERE a.activo=1 AND s.codigo='DISPONIBLE') activos_disponibles,
  (SELECT COUNT(*) FROM activos a JOIN estados_activo s ON s.id=a.estado_id WHERE a.activo=1 AND s.codigo IN('MANTENIMIENTO','REPARACION')) activos_mantenimiento,
  (SELECT COUNT(*) FROM trabajadores WHERE activo=1) total_trabajadores,
  (SELECT COUNT(*) FROM asignaciones WHERE estado IN('CONFIRMADA','PARCIAL')) asignaciones_activas,
  (SELECT COUNT(*) FROM mantenimientos WHERE estado='ABIERTO') mantenimientos_abiertos;

-- Asignacion demostrativa, creada mediante los procedimientos del sistema
CALL sp_crear_asignacion(3,2,'Asignacion inicial de demostracion',1,@demo_assignment,@demo_assignment_number);
CALL sp_agregar_activo_asignacion(@demo_assignment,4,'Buen estado, incluye cargador y mochila',1);
CALL sp_agregar_activo_asignacion(@demo_assignment,7,'Buen estado, incluye cable de poder y HDMI',1);
CALL sp_confirmar_asignacion(@demo_assignment,1);
