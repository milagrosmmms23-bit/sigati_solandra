# Instalación local en XAMPP

## 1. Copiar el proyecto

Descomprime la carpeta con este nombre exacto:

```text
C:\xampp\htdocs\sigati_solandra
```

Si cambias el nombre de la carpeta, actualiza `base_url` en `config/app.php`.

## 2. Iniciar servicios

Abre XAMPP Control Panel e inicia:

- Apache
- MySQL

## 3. Crear la base de datos

1. Abre `http://localhost/phpmyadmin`.
2. Entra a la pestaña **Importar**.
3. Selecciona `database/schema.sql`.
4. Ejecuta la importación completa.

El archivo crea automáticamente:

- Base `sigati_solandra`.
- 21 tablas.
- 9 procedimientos almacenados.
- 5 vistas para dashboard y reportes.
- Catálogos, trabajadores y activos demostrativos.
- Usuario administrador.

> El archivo elimina y vuelve a crear la base `sigati_solandra`. No lo ejecutes nuevamente cuando ya tengas datos reales sin realizar un respaldo.

## 4. Configurar la conexión

El archivo `config/database.php` viene preparado para XAMPP:

```php
'host' => '127.0.0.1',
'port' => 3306,
'database' => 'sigati_solandra',
'username' => 'root',
'password' => '',
```

Si configuraste contraseña para `root`, colócala allí.

## 5. Abrir SIGATI

```text
http://localhost/sigati_solandra/public
```

Credenciales:

```text
Usuario: admin
Contraseña: Admin123*
```

## 6. Instalar PDF y QR

El sistema funciona sin Composer usando la impresión del navegador. Para habilitar descarga PDF directa y códigos QR reales:

1. Instala Composer para Windows.
2. Abre CMD.
3. Ejecuta:

```bat
cd C:\xampp\htdocs\sigati_solandra
composer install
```

También puedes ejecutar `INSTALAR_DEPENDENCIAS.bat`.

Comprueba que en `C:\xampp\php\php.ini` estén habilitadas las extensiones:

```ini
extension=pdo_mysql
extension=mbstring
extension=fileinfo
extension=openssl
```

La extensión DOM normalmente está incluida con PHP de XAMPP.

## 7. Solucionar error 404

Verifica que Apache tenga activado `mod_rewrite`. En `C:\xampp\apache\conf\httpd.conf`:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

En el bloque correspondiente a `htdocs`, usa:

```apache
AllowOverride All
```

Reinicia Apache.

## 8. Verificar requisitos

Desde CMD:

```bat
cd C:\xampp\htdocs\sigati_solandra
C:\xampp\php\php.exe scripts\check_requirements.php
```

## 9. Importar tu inventario real

Usa `database/plantilla_importacion.csv`. Conserva los encabezados y guarda el archivo como CSV UTF-8 separado por comas. Después ingresa a:

```text
Inventario > Importar CSV
```

Realiza primero una prueba con cinco registros.

## 10. Antes de usarlo en producción

- Cambia `debug` a `false` en `config/app.php`.
- Cambia la contraseña inicial.
- Crea un usuario exclusivo para la base de datos.
- Programa respaldo diario de la base y de la carpeta `public/uploads`.
- Usa HTTPS si el sistema será accesible desde otras computadoras.
