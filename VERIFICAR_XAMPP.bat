@echo off
cd /d %~dp0
cls
if not exist C:\xampp\php\php.exe (
  echo No se encontro PHP en C:\xampp\php\php.exe
  pause
  exit /b 1
)
C:\xampp\php\php.exe scripts\check_requirements.php
pause
