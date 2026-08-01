<?php
$requirements = [
    'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
    'PDO MySQL' => extension_loaded('pdo_mysql'),
    'mbstring' => extension_loaded('mbstring'),
    'DOM' => extension_loaded('dom'),
    'Fileinfo' => extension_loaded('fileinfo'),
    'OpenSSL' => extension_loaded('openssl'),
];

echo "SIGATI SOLANDRA - Verificacion de entorno\n";
echo "PHP: ".PHP_VERSION."\n\n";
$ok = true;
foreach ($requirements as $name => $status) {
    echo sprintf("[%s] %s\n", $status ? 'OK' : 'FALTA', $name);
    $ok = $ok && $status;
}
exit($ok ? 0 : 1);
