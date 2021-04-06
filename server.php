<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/DB.php';

exec("hostname -I | awk '{print $1}'", $ip);
$ip = $ip[0];
$app = new Comet\Comet(['host' => $ip]);

$app->get('/hello', 
    function ($request, $response) {              
        return $response
            ->with($response->getAttributes());
});
$app->run();