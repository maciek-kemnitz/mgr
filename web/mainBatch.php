<?php

/** @var \Symfony\Component\HttpFoundation\Session\Session $session  */
$session = $app['session'];
$dropboxUserId = $session->get('dropbox_user_id');

/*
 * check if user has account and end if he has
 */
$sql = "SELECT * FROM basecamp_account WHERE dropbox_user_id = :id";
$params = array('id' => $dropboxUserId);
$result = $app['db']->executeQuery($sql, $params)->fetchAll();

if (count($result) == 0)
{
    return $app['twig']->render('main.twig');
}

$dbHelper = new DBHelper($app['db']);

$dropboxHelper = new DropboxHelper();
$dropboxHelper->oauth->setToken($session->get('dropbox_access_token'));

$basecampHelper = new BasecampHelper();
$basecampHelper->oauth->access_token = $session->get('basecamp_access_token');

$basecampHelper->dropboxUserId = $dropboxUserId;

$accounts = $basecampHelper->getAccounts();
if ($accounts)
{
    foreach($accounts as $account)
    {
        $dbHelper->saveAccounts($account, $dropboxUserId);

        $projects = $basecampHelper->getProjects($account->href);

        if ($projects)
        {
            foreach($projects as $project)
            {
                if (!is_object($project))
                {
                    break;
                }

                $dbHelper->saveProject($project, $account);
            }
        }

        $projects = $dbHelper->getProjects($account);

        foreach($projects as $item)
        {
            $attachments = $basecampHelper->getAttachments($account->href, $item['id']);

            if ($attachments)
            {
                $projectDate = new DateTime(isset($item['checked_at'])? $item['checked_at'] : "2000-01-01");
                foreach($attachments as $docs)
                {
                    $attachmentData = $basecampHelper->getAttachmentData($docs->url);
                    if ($attachmentData)
                    {
                        $attachmentDate = new DateTime($docs->created_at);

                        if ($attachmentDate > $projectDate)
                        {
                            file_put_contents('tmp/' . $docs->name, $attachmentData);

                            $dropboxHelper->dropbox->putFile("basecamp_file_uploader/". $account->name . "/" . $item['name'] . "/" . $docs->name, 'tmp/' . $docs->name, 'dropbox');
                            $dropboxHelper->uploadAttachment($account->name, $item['name'], $docs->name, 'tmp/' . $docs->name);
                        }
                    }
                }
            }
        }
    }
}

exit;
