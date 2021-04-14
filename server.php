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

$app->get('/get',
    function ($request, $response) use ($db) {
        $params = $request->getQueryParams();
        if (isset($params['table']) && !empty($params['table'])) {
            if (DB::tableIsAllowed($params['table'])) {
                $fetchReady = $db->query("SELECT * FROM " . $params['table']);
                if ($fetchReady) {
                    $result = $fetchReady->fetchAll(PDO::FETCH_ASSOC);
                    return $response
                        ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
                        ->with(gzencode(json_encode($result)));
                } else {
                    return $response
                        ->with($db->errorInfo());
                }
            } else
                return $response
                    ->with('Wrong TableName');
        } else
            return $response
                ->with('provide query ?table=Schema.TableName');
});
$app->run();
