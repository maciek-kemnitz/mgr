<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require_once __DIR__.'/../oauth/oauth_client.php';
require_once __DIR__.'/../httpclient/http.php';

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
$oauth->access_token = $session->get('basecamp_access_token');

$options = array(
    'RequestContentType' => 'application/json',

);

$dropboxUserId = $session->get('dropbox_user_id');

$success = $oauth->CallAPI('https://launchpad.37signals.com/authorization.json', 'GET',null, $options,$response);
if ($success)
{
    foreach($response->accounts as $account)
    {
        $sql = "SELECT id FROM basecamp_account WHERE id = :id";
        $params = array('id' => $account->id);
        $result = $app['db']->executeQuery($sql, $params)->fetch();

        if (!$result)
        {
            $sql = "INSERT INTO basecamp_account VALUES(:id, :url, :name, :dropbox_user_id)";
            $params = array(
                'id' => $account->id,
                'url' => $account->href,
                'name' => $account->name,
                'dropbox_user_id' => $dropboxUserId
            );

            $app['db']->executeUpdate($sql, $params);
        }
    }
}

$sql = "SELECT * FROM basecamp_account WHERE dropbox_user_id = :id";
$params = array('id' => $dropboxUserId);
$result = $app['db']->executeQuery($sql, $params)->fetchAll();
foreach($result as $account)
{
    $success = $oauth->CallAPI($account['url'] . "/projects.json", 'GET',null, $options, $response);
    if ($success)
    {
        foreach($response as $project)
        {
            if (!is_object($project))
            {
                break;
            }

            $sql = "INSERT IGNORE INTO basecamp_project (id, account_id, name, updated_at, checked_at) VALUES(:id, :account_id, :name, :updated_at, null)";
            $params = array(
                'id' => $project->id,
                'account_id' => $account['id'],
                'name' => $project->name,
                'updated_at' => $project->updated_at
            );

            $app['db']->executeUpdate($sql, $params);
        }
    }
}

foreach($result as $account)
{
    $sql = "SELECT * FROM basecamp_project WHERE account_id = :id AND checked_at IS NULL OR updated_at > checked_at";
    $params = array('id' => $account['id']);
    $projects = $app['db']->executeQuery($sql, $params)->fetchAll();

    foreach($projects as $item)
    {
        $success = $oauth->CallAPI($account['url'] . "/projects/{$item['id']}/documents.json", 'GET',null, $options, $response);
        var_dump($success);
        if ($success)
        {
            foreach($response as $docs)
            {
               var_dump($docs);
                echo "<hr>";
            }
        }
    }
}
//
//var_dump($oauth);
exit;
