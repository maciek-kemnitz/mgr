<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

include __DIR__.'/../oauth/oauth_client.php';
include __DIR__.'/../httpclient/http.php';

$login = $app['controllers_factory'];


$login->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $key = 'a1976ec6c3d8989a07f7ae75271da0d40045cf8d';
    $secret = '8a72b1c0819528c4919b2ab5e80c3b0fb1ef812d';
    $redirectUrl = 'http://localhost/mgr/web/index.php/login-basecamp';

    $oauth = new oauth_client_class();
    $oauth->Initialize();
    $oauth->request_token_url = 'https://launchpad.37signals.com/authorization/new';
    $oauth->dialog_url = 'https://launchpad.37signals.com/authorization/new?type={SCOPE}&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}';
    $oauth->access_token_url = 'https://launchpad.37signals.com/authorization/token?type=web_server';
    $oauth->client_id = $key;
    $oauth->redirect_uri = $redirectUrl;
    $oauth->client_secret = $secret;
    $oauth->scope = 'web_server';
    $oauth->debug = true;

    $result = $oauth->Process();

    if ($result)
    {
        $session = $app['session'];

        if (!$session->has('dropbox_user_id'))
        {
            return $app->redirect($app['url_generator']->generate('main'));
        }

        $dropboxUserId = $session->get('dropbox_user_id');

        $sql = 'SELECT id FROM user_basecamp WHERE dropbox_user_id = :dropbox_user_id';
        $params = array('dropbox_user_id' => $dropboxUserId);
        $stmt = $app['db']->executeQuery($sql, $params);
        $id = $stmt->fetch();

        if ($id)
        {
            $stmt = $app['db']->prepare('
                UPDATE user_basecamp set
                    access_token = :access_token,
                    access_token_secret = :access_token_secret,
                    access_token_expiry = :access_token_expiry,
                    refresh_token = :refresh_token
                WHERE id = :id
            ');

            $stmt->bindValue('id', $id);

        }
        else
        {
            $stmt = $app['db']->prepare('
                INSERT INTO user_basecamp (access_token, access_token_secret, access_token_expiry, refresh_token, dropbox_user_id)
                VALUES(:access_token, :access_token_secret, :access_token_expiry, :refresh_token, :dropbox_user_id)
            ');

            $stmt->bindValue('dropbox_user_id', $dropboxUserId);
        }

        $stmt->bindValue('access_token', $oauth->access_token);
        $stmt->bindValue('access_token_secret', $oauth->access_token_secret);
        $stmt->bindValue('access_token_expiry', $oauth->access_token_expiry);
        $stmt->bindValue('refresh_token', $oauth->refresh_token);

        $stmt->execute();

        $session->set('basecamp_access_token', $oauth->access_token);
        return $app->redirect($app['url_generator']->generate('main'));
    }
    else
    {
        exit;
    }


})->bind('login-basecamp');

return $login;