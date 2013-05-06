<?php

require_once __DIR__.'/../../vendor/dropbox-php/dropbox-php/src/Dropbox/autoload.php';

class DropboxHelper
{
    const CONSUMER_KEY = 'w2iwfqgcatz12y7';
    const CONSUMER_SECRET = 'oig0ca9xtwplre5';
    const MAIN_FOLDER = 'basecamp_file_uploader';
    const ROOT = 'dropbox';

    public $oauth;
    public $dropbox;
    /** @var \Symfony\Component\HttpFoundation\Session\Session $session  */
    public $session;

    public function __construct()
    {
        $this->oauth = new Dropbox_OAuth_PHP(self::CONSUMER_KEY, self::CONSUMER_SECRET);
        $this->dropbox = new Dropbox_API($this->oauth);
    }

    public function createMainFolder()
    {
        $this->dropbox->createFolder(self::MAIN_FOLDER, self::ROOT);
    }

    public function createAccountFolder($name)
    {
        $this->dropbox->createFolder(self::MAIN_FOLDER . $name, self::ROOT);
    }

    public function createProjectFolder($accountName, $projectName)
    {
        $this->dropbox->createFolder(self::MAIN_FOLDER . $name, self::ROOT);
    }

    public function uploadAttachment($accountName, $projectName, $fileName, $path)
    {
        $this->dropbox->putFile(self::MAIN_FOLDER . $accountName . "/" . $projectName . "/" . $fileName, $path, 'dropbox');
    }

}