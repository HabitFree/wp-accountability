<?php

interface Hf_iContentManagementSystem {
    public function getUserEmail( $userID );

    public function sendWpEmail( $to, $subject, $message );

    public function getSubscribedUsers();

    public function currentUser();

    public function getVar( $query );

    public function getDbPrefix();

    public function executeQuery( $query );

    public function createUser( $username, $password, $email );

    public function isUserLoggedIn();

    public function getRows( $table, $where, $outputType = OBJECT );

    public function getRow( $table, $criterion );

    public function deleteRows( $table, $where );

    public function isEmailTaken( $email );

    public function authenticateUser( $username, $password );

    public function isError( $thing );

    public function getShortcodeOutput( $shortcode );

    public function addPageToAdminMenu($name, $slug, $function);

    public function getPluginAssetUrl( $fileName );

    public function isUsernameTaken( $username );

    public function expandShortcodes( $string );

    public function getUserIdByEmail($email);

    public function getLogoutUrl($redirect);

    public function getResults($query);

    public function insertIntoDb($table, $data);

    public function updateRowsSafe($table, $data, $where);

    public function getPageByTitle($title);

    public function getPermalink($pageId);

    public function getHomeUrl();
}