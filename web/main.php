<?php

include __DIR__.'/../vendor/dropbox-php/dropbox-php/src/Dropbox/autoload.php';


$main = $app['controllers_factory'];






$main->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $consumerKey = 'w2iwfqgcatz12y7';
    $consumerSecret = 'oig0ca9xtwplre5';

    $oauth = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);

    $dropbox = new Dropbox_API($oauth);

    /** @var \Symfony\Component\HttpFoundation\Session\Session $session  */
    $session = $app['session'];

//    $session->set("state", 1);
//    $session->remove("oauth_token");
//    die;

	return $app['twig']->render('main.twig');

    if ($session->has('state'))
    {
        $state = $session->get('state');
    }
    else
    {
        $state = 1;
    }

    switch($state)
    {
        case 1:

            $tokens = $oauth->getRequestToken();
            $session->set("state", 2);
            $session->set("oauth_tokens", $tokens);

            return new \Symfony\Component\HttpFoundation\RedirectResponse($oauth->getAuthorizeUrl("http://localhost/mgr-project/web/index.php/"));

        case 2:
            echo "Step 3: Acquiring access tokens<br>";
            $oauth->setToken($session->get('oauth_tokens'));
            $tokens = $oauth->getAccessToken();
            var_dump($tokens);
            $session->set('state', 3);
            $session->set('oauth_tokens', $tokens);

        case 3 :
            echo "The user is authenticated<br>";
            echo "You should really save the oauth tokens somewhere, so the first steps will no longer be needed<br>";
            print_r($session->get('oauth_tokens'));
            $oauth->setToken($session->get('oauth_tokens'));
            var_dump($dropbox->getAccountInfo());
            break;

    }

    return $app['twig']->render('main.twig');
});

return $main;