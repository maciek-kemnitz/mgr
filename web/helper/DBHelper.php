<?php

class DBHelper
{
    public $con;

    public function __construct($con)
    {
        $this->con = $con;
    }

    public function saveAccounts($account, $userId)
    {
        $sql = "INSERT IGNORE INTO basecamp_account VALUES(:id, :url, :name, :dropbox_user_id)";
        $params = array(
            'id' => $account->id,
            'url' => $account->href,
            'name' => $account->name,
            'dropbox_user_id' => $userId
        );

        $this->con->executeUpdate($sql, $params);
    }

    public function saveProject($project, $account)
    {
        $url = $project->url;
        $urlParts = preg_split("{/}", $url);
        $nameParts = preg_split("{-}", array_pop($urlParts));
        $projectName = preg_replace("{.json}", '', implode('_', array_slice($nameParts, 1, count($nameParts) -1)));

        $sql = "INSERT IGNORE INTO basecamp_project (id, account_id, name, updated_at, checked_at) VALUES(:id, :account_id, :name, :updated_at, null)";
        $params = array(
            'id' => $project->id,
            'account_id' => $account->id,
            'name' => $projectName,
            'updated_at' => $project->updated_at
        );

        $this->con->executeUpdate($sql, $params);
    }

    public function getProjects($account)
    {
        $sql = "SELECT * FROM basecamp_project WHERE account_id = :id AND (checked_at IS NULL OR updated_at > checked_at)";
        $params = array('id' => $account->id);
        return $this->con->executeQuery($sql, $params)->fetchAll();
    }
}