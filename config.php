<?php
/**
 * Main MSSQL params to override "Database", "LoginTimeout", "Server"
 * For more references refer to https://www.php.net/manual/ru/ref.pdo-sqlsrv.connection.php
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

exec("hostname -I | awk '{print $1}'", $ip);
$ip = $ip[0];

$formatter = new LineFormatter("\n%datetime% >> %channel%:%level_name% >> %message%", "Y-m-d H:i:s");
$stream = new StreamHandler(__DIR__ . '/log/app.log', Logger::INFO);
$stream->setFormatter($formatter);
$logger = new Logger('server');
$logger->pushHandler($stream);

return [
    'MSSQL' => [
        'Database' => 'AdventureWorks',
        'LoginTimeout' => 5,
        'Server' => '192.168.1.226'
    ],
    'PHPProxy' => [
        'port' => 8081,
        'host' => $ip,
        'debug' => 'true',
        'logger' => $logger
    ],
];
