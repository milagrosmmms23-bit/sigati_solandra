<?php
return [
    'nombre' => getenv('APP_NAME') ?: 'SIGATI SOLANDRA',
    'empresa' => getenv('APP_COMPANY') ?: 'SOLANDRA',
    'sede' => getenv('APP_SITE') ?: 'Sede Arequipa',
    'url_base' => getenv('APP_BASE_URL') ?: rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/sigati_solandra/public/index.php'), '/'),
    'zona_horaria' => getenv('APP_TIMEZONE') ?: 'America/Lima',
    'depuracion' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'elementos_por_pagina' => (int) (getenv('APP_ITEMS_PER_PAGE') ?: 15),
];