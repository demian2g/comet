<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = new Comet\Comet();

$app->get('/hello', 
    function ($request, $response) {              
        return $response
            ->with("Hello, Comet!");
});

$app->run();
