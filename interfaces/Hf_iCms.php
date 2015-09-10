<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iCms {
    public function getUserEmail( $userID );

    public function sendEmail( $to, $subject, $message );

    public function getSubscribedUsers();

    public function currentUser();

    public function getVar( $query );

    public function getDbPrefix();

    public function executeQuery( $query );

    public function createUser( $username, $password, $email );

    public function isUserLoggedIn();

    public function getRow( $query );

    public function deleteRows( $table, $where );

    public function isEmailTaken( $email );

    public function authenticateUser( $username, $password );

    public function isError( $thing );

    public function getShortcodeOutput( $shortcode );

    public function addPageToAdminMenu($name, $slug, $function, $iconUrlOrName, $position);

    public function getPluginAssetUrl( $fileName );

    public function isUsernameTaken( $username );

    public function expandShortcodes( $string );

    public function getUserIdByEmail($email);

    public function getLogoutUrl($redirect);

    public function getResults($query);

    public function insertIntoDb($table, $data, $format);

    public function updateRowsSafe($table, $data, $where);

    public function getPageByTitle($title);

    public function getPermalink($pageId);

    public function getHomeUrl();

    public function prepareQuery($query, $valueParameters);

    public function insertOrReplaceRow($table, $data, $format);

    public function getOption($option);

    public function getPluginDirectoryUrl($plugin);

    public function getUsers();

    public function getNonceField($action);

    public function isNonceValid($nonce, $action);

    public function updateUserMeta($userId, $metaName, $metaValue);

    public function registerPostType($postType, $args);
}