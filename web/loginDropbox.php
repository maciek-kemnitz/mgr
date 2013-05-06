<?php

const DROPBOX_STATE = 'dropbox_state';
const DROPBOX_STATE_STARTED = 1;

$loginDropbox = $app['controllers_factory'];


$loginDropbox->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app)
{
    $dropboxHelper = new DropboxHelper();

    /** @var \Symfony\Component\HttpFoundation\Session\Session $session  */
    $session = $app['session'];

    if ($session->has(DROPBOX_STATE))
    {
        $dropboxState = $session->get(DROPBOX_STATE);
    }
    else
    {
        $dropboxState = DROPBOX_STATE_STARTED;
    }

    $dropboxLoginUrl = null;

    switch($dropboxState)
    {
        case 1:
            $tokens = $dropboxHelper->oauth->getRequestToken();
            $session->set(DROPBOX_STATE, 2);
            $session->set("dropbox_oauth_tokens", $tokens);

            $dropboxLoginUrl = $dropboxHelper->oauth->getAuthorizeUrl($request->getUri());
            return $app->redirect($dropboxLoginUrl);

        case 2:
            $dropboxHelper->oauth->setToken($session->get('dropbox_oauth_tokens'));
            try
            {
                $tokens = $dropboxHelper->oauth->getAccessToken();

                $accountInfo = $dropboxHelper->dropbox->getAccountInfo();

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
                $session->set(DROPBOX_STATE, 1);
                return \Symfony\Component\HttpFoundation\RedirectResponse::create($request->getUri());
            }
    }

})->bind('login-dropbox');

return $loginDropbox;

