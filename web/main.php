<?php

include __DIR__.'/../vendor/dropbox-php/dropbox-php/src/Dropbox/autoload.php';

const DROPBOX_STATE = 'dropbox_state';
const DROPBOX_STATE_STARTED = 1;
const DROPBOX_STATE_FINISHED = 4;

$main = $app['controllers_factory'];


$main->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app)
{
    $consumerKey = 'w2iwfqgcatz12y7';
    $consumerSecret = 'oig0ca9xtwplre5';

    /** @var \Symfony\Component\HttpFoundation\Session\Session $session  */
    $session = $app['session'];

    $oauth = new Dropbox_OAuth_PHP($consumerKey, $consumerSecret);
    $dropbox = new Dropbox_API($oauth);

    if ($session->has(DROPBOX_STATE))
    {
        $dropboxState = $session->get(DROPBOX_STATE);
    }
    else
    {
        $dropboxState = DROPBOX_STATE_STARTED;
    }

    $dropboxLoginUrl = null;
    $dropboxNotDone = true;

    switch($dropboxState)
    {
        case 1:
            $tokens = $oauth->getRequestToken();
            $session->set(DROPBOX_STATE, 2);
            $session->set("dropbox_oauth_tokens", $tokens);

            $dropboxLoginUrl = $oauth->getAuthorizeUrl("http://localhost/mgr/web/index.php/");
            break;

        case 2:
            $oauth->setToken($session->get('dropbox_oauth_tokens'));
            try
            {
                $tokens = $oauth->getAccessToken();
                $session->set(DROPBOX_STATE, 3);
                $session->set('dropbox_oauth_tokens', $tokens);
            }
            catch(Exception $exc)
            {
                $session->set(DROPBOX_STATE, 1);
                return \Symfony\Component\HttpFoundation\RedirectResponse::create($request->getUri());
            }

        case 3 :
            $oauth->setToken($session->get('dropbox_oauth_tokens'));
            $dropboxNotDone = false;
            break;
    }

    $key = 'a1976ec6c3d8989a07f7ae75271da0d40045cf8d';
    $secret = '8a72b1c0819528c4919b2ab5e80c3b0fb1ef812d';

    $oauth2 = new OAuth($key, $secret, OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
    $oauth2->disableSSLChecks();
    $oauth2->enableDebug();
    $basecampRedirectUrl = "http://localhost/mgr/web/index.php/";
    $basecampLoginUrl = "https://launchpad.37signals.com/authorization/new?type=web_server&client_id={$key}&redirect_uri={$basecampRedirectUrl}";
    $basecampNotDone = true;

	return $app['twig']->render('main.twig', array(
        'dropboxLoginUrl' => $dropboxLoginUrl,
        'dropboxNotDone' => $dropboxNotDone,
        'basecampLoginUrl' => $basecampLoginUrl,
        'basecampNotDone' => $basecampNotDone,
    ));


});

return $main;

