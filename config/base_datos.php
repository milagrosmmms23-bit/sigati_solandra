<?php
return [
    'servidor' => getenv('DB_HOST') ?: '127.0.0.1',
    'puerto' => getenv('DB_PORT') ?: '3306',
    'nombre' => getenv('DB_DATABASE') ?: 'sigati_solandra',
    'usuario' => getenv('DB_USERNAME') ?: 'root',
    'clave' => getenv('DB_PASSWORD') ?: '',
    'codificacion' => getenv('DB_CHARSET') ?: 'utf8mb4',
];