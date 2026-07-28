@echo off
cd /d %~dp0
cls
echo ==========================================
echo SIGATI SOLANDRA - Dependencias PDF y QR
echo ==========================================
where composer >nul 2>nul
if errorlevel 1 (
  echo No se encontro Composer en el PATH.
  echo Instala Composer para Windows y vuelve a ejecutar este archivo.
  pause
  exit /b 1
)
composer install
if errorlevel 1 (
  echo La instalacion no finalizo correctamente.
  pause
  exit /b 1
)
echo.
echo Dependencias instaladas correctamente.
pause
