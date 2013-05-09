<?php

$dropbox = $app['controllers_factory'];

$dropbox->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app)
{
    $session = $app['session'];

    if (!$session->has('dropbox_access_token'))
    {
        return $app['twig']->render('dropbox.twig', array('dropboxLogin' => $app['url_generator']->generate('login-dropbox')));
    }

    return $app->redirect($app['url_generator']->generate('main'));

})->bind('step-1');

return $dropbox;