<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/DB.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
exec("hostname -I | awk '{print $1}'", $ip);
$ip = $ip[0];
$db = DB::getDB();

$formatter = new LineFormatter("\n%datetime% >> %channel%:%level_name% >> %message%", "Y-m-d H:i:s");
$stream = new StreamHandler(__DIR__ . '/log/app.log', Logger::INFO);
$stream->setFormatter($formatter);
$logger = new Logger('server');
$logger->pushHandler($stream);


$app = new Comet\Comet(['host' => $ip, 'debug' => 'true', 'logger' => $logger]);

$app->get('/hello', 
    function ($request, $response) use ($db) {
        $fetchReady = $db->query("SELECT TOP 10 * FROM Production.Product");
        if ($fetchReady) {
            $result = $fetchReady->fetchAll(PDO::FETCH_ASSOC);
            $result = array_merge($result, DB::getAllowedTables());
            return $response
                ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
                ->with(gzencode(json_encode($result)));
        } else {
            return $response
                ->with($db->errorInfo());
        }
});
$app->run();
