<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

require_once __DIR__.'/../../oauth/oauth_client.php';
require_once __DIR__.'/../../httpclient/http.php';

class BasecampHelper
{
    const CONSUMER_KEY = 'a1976ec6c3d8989a07f7ae75271da0d40045cf8d';
    const CONSUMER_SECRET = '8a72b1c0819528c4919b2ab5e80c3b0fb1ef812d';
    const REDIRECT_URL = 'http://localhost/mgr/web/index.php/login-basecamp';
    const REQUEST_TOKEN_URL = 'https://launchpad.37signals.com/authorization/new';
    const DIALOG_URL = 'https://launchpad.37signals.com/authorization/new?type={SCOPE}&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}';
    public $oauth;
    public $dropboxUserId;
    public $options = array(
        'RequestContentType' => 'application/json',
    );

    public function __construct()
    {
        $this->prepareOauth();
    }

    public function prepareOauth()
    {
        $this->oauth = new oauth_client_class();
        $this->oauth->Initialize();
        $this->oauth->request_token_url = self::REQUEST_TOKEN_URL;
        $this->oauth->dialog_url = self::DIALOG_URL;
        $this->oauth->access_token_url = 'https://launchpad.37signals.com/authorization/token?type=web_server';
        $this->oauth->client_id = self::CONSUMER_KEY;
        $this->oauth->client_secret = self::CONSUMER_SECRET;
        $this->oauth->redirect_uri = self::REDIRECT_URL;
        $this->oauth->scope = 'web_server';
        $this->oauth->debug = true;
    }

    public function processOauth()
    {
        return $this->oauth->Process();
    }

    public function getAccounts()
    {
        $response = null;
        $success = $this->oauth->CallAPI('https://launchpad.37signals.com/authorization.json', 'GET',null, $this->options, $response);

        if($success)
        {
            return $response->accounts;
        }
        else
        {
            return null;
        }
    }

    public function getProjects($url)
    {
        $response = null;
        $success = $this->oauth->CallAPI($url . "/projects.json", 'GET',null, $this->options, $response);

        if($success)
        {
            return $response;
        }
        else
        {
            return null;
        }
    }

    public function getAttachments($accountUrl, $projectId)
    {
        $response = null;
        $success = $this->oauth->CallAPI($accountUrl . "/projects/" . $projectId . "/attachments.json", 'GET',null, $this->options, $response);

        if($success)
        {
            return $response;
        }
        else
        {
            return null;
        }
    }

    public function getAttachmentData($attachmentUrl)
    {
        $response = null;
        $success = $this->oauth->CallAPI($attachmentUrl, 'GET',null, $this->options, $response);

        if($success)
        {
            return $response;
        }
        else
        {
            return null;
        }
    }



}