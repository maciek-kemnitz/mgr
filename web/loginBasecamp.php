<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$login = $app['controllers_factory'];


$login->get('/', function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $basecampHelper = new BasecampHelper();
    $result = $basecampHelper->processOauth();

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

        $stmt->bindValue('access_token', $basecampHelper->oauth->access_token);
        $stmt->bindValue('access_token_secret', $basecampHelper->oauth->access_token_secret);
        $stmt->bindValue('access_token_expiry', $basecampHelper->oauth->access_token_expiry);
        $stmt->bindValue('refresh_token', $basecampHelper->oauth->refresh_token);

        $stmt->execute();

        $session->set('basecamp_access_token', $basecampHelper->oauth->access_token);
        return $app->redirect($app['url_generator']->generate('main'));
    }
    else
    {
        return $app->redirect($app['url_generator']->generate('main'));
    }


})->bind('login-basecamp');

return $login;