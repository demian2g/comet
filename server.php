<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/DB.php';
ini_set("zlib.output_compression", 1);

exec("hostname -I | awk '{print $1}'", $ip);
$ip = $ip[0];
$app = new Comet\Comet(['host' => $ip]);
$db = DB::getDB();

$app->get('/hello', 
    function ($request, $response) use ($db) {
        $result = $db->query("SELECT TOP 10 * FROM Person.Contact")->fetchAll();
        return $response
            ->setHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
            ->with(gzencode(json_encode($result)));
});
$app->run();
