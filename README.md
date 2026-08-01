# SIGATI SOLANDRA

Sistema web local para la gestion de activos tecnologicos de SOLANDRA, sede Arequipa.

## Funcionalidades

- Panel con indicadores de inventario.
- Registro y busqueda de activos TI.
- Trabajadores y areas responsables.
- Asignaciones con acta imprimible y PDF.
- Devoluciones parciales o totales.
- Mantenimientos preventivos y correctivos.
- Historial de movimientos por activo.
- Reporte consolidado y descarga CSV.
- Importacion inicial desde CSV.
- Catalogos administrables.
- Etiquetas QR cuando estan instaladas las dependencias de Composer.

## Requisitos

- XAMPP con PHP 8.2 o superior.
- MySQL/MariaDB.
- Composer para instalar dependencias opcionales de PDF y QR.

## Instalacion rapida

1. Copia o clona el proyecto en `C:\xampp\htdocs\sigati_solandra_ok`.
2. Importa `database/schema.sql` en MySQL.
3. Abre `http://localhost/sigati_solandra_ok/public/ingreso`.
4. Ingresa con:
   - Usuario: `admin`
   - Contrasena: `Admin123*`

Cambia la contrasena inicial antes de registrar informacion real.

## Dependencias opcionales

Ejecuta:

```bash
composer install
```

## Configuracion

Los valores por defecto estan en `config/aplicacion.php` y `config/base_datos.php`.
Tambien puedes usar variables de entorno como referencia en `.env.example`.

## Seguridad

El proyecto debe abrirse desde la carpeta `public`. La raiz incluye reglas `.htaccess` para bloquear carpetas internas como `app`, `config`, `database`, `storage` y `vendor`.