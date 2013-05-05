<?php

include __DIR__.'/../vendor/dropbox-php/dropbox-php/src/Dropbox/autoload.php';

const DROPBOX_STATE = 'dropbox_state';
const DROPBOX_STATE_STARTED = 1;
const DROPBOX_STATE_FINISHED = 4;

$loginDropbox = $app['controllers_factory'];


$loginDropbox->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app)
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

            $dropboxLoginUrl = $oauth->getAuthorizeUrl($request->getUri());
            return $app->redirect($dropboxLoginUrl);

        case 2:
            $oauth->setToken($session->get('dropbox_oauth_tokens'));
            try
            {
                $tokens = $oauth->getAccessToken();

                $accountInfo = $dropbox->getAccountInfo();

                $stmt = $app['db']->prepare("INSERT IGNORE INTO user_dropbox (id, access_token, display_name) VALUES(:id, :access_token, :display_name)");
                $stmt->bindValue('access_token', json_encode($tokens));
                $stmt->bindValue('id', $accountInfo['uid']);
                $stmt->bindValue('display_name', $accountInfo['display_name']);
                $stmt->execute();

                $session->set('dropbox_access_token', $tokens);
                $session->set('dropbox_user_id', $accountInfo['uid']);

                return $app->redirect($app['url_generator']->generate('main'));
            }
            catch(Exception $exc)
            {
                var_dump($exc);exit;
                $session->set(DROPBOX_STATE, 1);
                return \Symfony\Component\HttpFoundation\RedirectResponse::create($request->getUri());
            }

        case 3 :
            $oauth->setToken($session->get('dropbox_oauth_tokens'));
            return $app->redirect($app['url_generator']->generate('main'));
    }

})->bind('login-dropbox');

return $loginDropbox;

