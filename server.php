<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/DB.php';

$db = DB::getDB();
$config = require "config.php";

if (is_file('config-local.php')) {
    $configFromFile = require 'config-local.php';
    if (isset($configFromFile['PHPProxy']))
        $config = array_merge($config, $configFromFile);
}

$app = new Comet\Comet($config['PHPProxy']);

$app->get('/hello', 
    function ($request, $response) use ($db) {
        $fetchReady = $db->query("SELECT TOP 10 * FROM Production.Product");
        if ($fetchReady) {
            $result = $fetchReady->fetchAll(PDO::FETCH_ASSOC);
            $result = array_merge($result, $request->getQueryParams());
            return $response
                ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
                ->with(gzencode(json_encode($result)));
        } else {
            return $response
                ->with($db->errorInfo());
        }
});
$app->run();
