<?php

$main = $app['controllers_factory'];


$main->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app)
{
    $session = $app['session'];


    if (!$session->has('dropbox_access_token'))
    {
        return $app['twig']->render('main.twig', array('dropboxLogin' => $app['url_generator']->generate('login-dropbox')));
    }

    if (!$session->has('basecamp_access_token'))
    {
        return $app['twig']->render('main.twig', array('basecampLogin' => $app['url_generator']->generate('login-basecamp')));
    }

    include 'mainBatch.php';



})->bind('main');

return $main;

