# Documentación técnica — SIGATI SOLANDRA

## Arquitectura

```text
Navegador
  ↓
public/index.php
  ↓
Router + CSRF + sesión
  ↓
Controladores MVC
  ↓
Modelos / servicios de dominio
  ↓
PDO + procedimientos almacenados
  ↓
MariaDB / MySQL
  ↓
Vistas SQL para dashboard y reportes
```

## Estructura

```text
app/
  Controllers/   Control de solicitudes y validación
  Core/          Router, sesión, CSRF, PDO, vistas y auditoría
  Models/        Acceso a datos y operaciones del dominio
  Views/         Interfaz, actas y reportes
config/           Aplicación y base de datos
database/         Esquema, datos de prueba y plantilla CSV
public/           Punto de entrada, CSS, JS y archivos públicos
scripts/          Verificación del entorno
storage/logs/     Registro de errores
```

## Procedimientos almacenados

| Procedimiento | Responsabilidad |
|---|---|
| `sp_generate_asset_code` | Generar correlativo por tipo de activo |
| `sp_assignment_create` | Crear cabecera y número de asignación |
| `sp_assignment_add_asset` | Validar y agregar un activo disponible |
| `sp_assignment_confirm` | Actualizar responsable, área, estado e historial |
| `sp_return_create` | Crear cabecera y número de devolución |
| `sp_return_asset` | Evaluar activo, liberar responsable y registrar movimiento |
| `sp_return_confirm` | Cerrar devolución y actualizar asignación |
| `sp_maintenance_open` | Abrir orden y cambiar estado del activo |
| `sp_maintenance_close` | Cerrar orden y restaurar estado anterior |

## Vistas SQL

- `vw_inventory_general`
- `vw_assets_by_status`
- `vw_assets_by_type`
- `vw_assets_by_area`
- `vw_dashboard_summary`

## Reglas de negocio implementadas

1. Un equipo solo se puede asignar cuando está disponible.
2. Una asignación debe contener al menos un activo.
3. La confirmación actualiza activo, responsable, área e historial en una transacción.
4. Se admiten devoluciones parciales.
5. Un activo no puede devolverse dos veces desde el mismo detalle.
6. Un activo no puede tener dos mantenimientos abiertos.
7. Los movimientos históricos no se eliminan.
8. Los códigos de activo y documentos son únicos y correlativos.
9. El código anterior se conserva como referencia.
10. Los activos dados de baja se controlan por estado, no borrando su historia.

## Seguridad incorporada

- Contraseñas con `password_hash`/`password_verify`.
- Consultas preparadas con PDO.
- Protección CSRF en operaciones POST.
- Regeneración de sesión al iniciar/cerrar sesión.
- Perfiles básicos ADMIN, TECNICO y AUDITOR.
- Registro de IP, navegador, valores anteriores y nuevos en auditoría.
- Escape HTML centralizado.
- Front controller y carpetas internas fuera de `public`.

## Próximas extensiones recomendadas

- Gestión visual de usuarios y cambio de contraseña.
- Adjuntar actas firmadas y fotografías.
- Préstamos temporales y traslados entre ubicaciones.
- Importación XLSX directa.
- Envío de notificaciones.
- Integración LDAP/Active Directory.
- API REST para aplicación móvil.
- Firma electrónica interna.
- Inventario automático mediante agente.
