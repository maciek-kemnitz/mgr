<?php

require_once __DIR__.'/../vendor/autoload.php';



$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views'));
$app->register(new Silex\Provider\SessionServiceProvider());

//$app->get('/hello/{name}', function($name) use ($app) {
//   return 'Hello ' . $app->escape($name);
//});


$app->mount('/', include 'main.php');
$app->mount('/b', include 'b.php');
$app->run();