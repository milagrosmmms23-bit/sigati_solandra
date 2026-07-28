<?php
return [
    'name' => getenv('APP_NAME') ?: 'SIGATI SOLANDRA',
    'company' => getenv('APP_COMPANY') ?: 'SOLANDRA',
    'site' => getenv('APP_SITE') ?: 'Sede Arequipa',
    'base_url' => getenv('APP_BASE_URL') ?: '/sigati_solandra/public',
    'timezone' => getenv('APP_TIMEZONE') ?: 'America/Lima',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'items_per_page' => (int) (getenv('APP_ITEMS_PER_PAGE') ?: 15),
];
