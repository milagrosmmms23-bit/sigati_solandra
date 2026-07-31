# SIGATI SOLANDRA

Sistema web local para la gestión de activos tecnológicos de SOLANDRA, sede Arequipa.

## Funcionalidades

- Panel con indicadores de inventario.
- Registro y búsqueda de activos TI.
- Trabajadores y áreas responsables.
- Asignaciones con acta imprimible y PDF.
- Devoluciones parciales o totales.
- Mantenimientos preventivos y correctivos.
- Historial de movimientos por activo.
- Reporte consolidado y descarga CSV.
- Importación inicial desde CSV.
- Catálogos administrables.
- Etiquetas QR cuando están instaladas las dependencias de Composer.

## Requisitos

- XAMPP con PHP 8.2 o superior.
- MySQL/MariaDB.
- Composer para instalar dependencias opcionales de PDF y QR.

## Instalación rápida

1. Copia el proyecto en `C:\xampp\htdocs\sigati_solandra`.
2. Importa `database/schema.sql` en MySQL.
3. Abre `http://localhost/sigati_solandra/public`.
4. Ingresa con:
   - Usuario: `admin`
   - Contraseña: `Admin123*`

Cambia la contraseña inicial antes de registrar información real.

## Dependencias opcionales

Ejecuta:

```bash
composer install
```

## Configuración

Los valores por defecto están en `config/app.php` y `config/database.php`.
También puedes usar variables de entorno como referencia en `.env.example`.

## Seguridad

El proyecto debe abrirse desde la carpeta `public`. La raíz incluye reglas `.htaccess` para bloquear carpetas internas como `app`, `config`, `database`, `storage` y `vendor`.
