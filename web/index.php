<?php

require_once __DIR__.'/../vendor/autoload.php';



$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views'));
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), include 'config/db.php');

//$sql = "SELECT * FROM posts";
//$post = $app['db']->fetchAssoc($sql);

//$app->get('/hello/{name}', function($name) use ($app) {
//   return 'Hello ' . $app->escape($name);
//});


$app->mount('/', include 'main.php');
$app->mount('/b', include 'b.php');
$app->mount('/login-basecamp', include 'loginBasecamp.php');
$app->mount('/login-dropbox', include 'loginDropbox.php');
$app->run();