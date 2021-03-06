<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfWordPress implements Hf_iCms {
    private $wpdb;

    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function getUserEmail( $userID ) {
        return get_userdata( $userID )->user_email;
    }

    public function sendEmail( $to, $subject, $message ) {
        add_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );
        $result = wp_mail($to, $subject, $message);
        remove_filter( 'wp_mail_content_type', array($this, 'set_html_content_type') );
        return $result;
    }

    function set_html_content_type() {
        return 'text/html';
    }

    public function getSubscribedUsers() {
        return get_users( array(
            'meta_key'   => 'hfSubscribed',
            'meta_value' => true
        ) );
    }

    public function currentUser() {
        $CurrentUser = wp_get_current_user();
        return $CurrentUser;
    }

    public function getVar( $query ) {
        return $this->wpdb->get_var( $query );
    }

    public function getDbPrefix() {
        return $this->wpdb->prefix;
    }

    public function executeQuery( $query ) {
        $this->wpdb->query( $query );
    }

    public function createUser( $username, $password, $email ) {
        $userIdOrError = wp_create_user($username, $password, $email);
        return $userIdOrError;
    }

    public function isUserLoggedIn() {
        return is_user_logged_in();
    }

    function getRow( $query ) {
        return $this->wpdb->get_row( $query );
    }

    public function deleteRows( $table, $where ) {
        return $this->wpdb->delete( $table, $where );
    }

    public function isEmailTaken( $email ) {
        return email_exists( $email );
    }

    public function authenticateUser( $username, $password ) {
        $credentials                  = array();
        $credentials['user_login']    = $username;
        $credentials['user_password'] = $password;

        return wp_signon( $credentials );
    }

    public function isError( $thing ) {
        return is_wp_error( $thing );
    }

    public function getShortcodeOutput( $shortcode ) {
        return do_shortcode( $shortcode );
    }

    public function addPageToAdminMenu($name, $slug, $function, $iconUrlOrName, $position) {
        add_menu_page( $name, $name, 'activate_plugins', $slug, $function, $iconUrlOrName, $position );
    }

    public function getPluginAssetUrl( $fileName ) {
        return plugins_url( $fileName, dirname( __FILE__ ) );
    }

    public function isUsernameTaken($username) {
        return username_exists( $username );
    }

    public function expandShortcodes( $string ) {
        return do_shortcode( $string );
    }

    public function getUserIdByEmail($email) {
        return get_user_by('email', $email)->ID;
    }

    public function getLogoutUrl($redirect) {
        return wp_logout_url( $redirect );
    }

    public function getResults($query) {
        return $this->wpdb->get_results($query);
    }

    public function insertIntoDb($table, $data, $format) {
        $this->wpdb->insert($table, $data, $format);
    }

    public function updateRowsSafe($table, $data, $where) {
        $this->wpdb->update($table, $data, $where);
    }

    public function getPageByTitle($title) {
        return get_page_by_title( $title );
    }

    public function getPermalink($pageId) {
        return get_permalink( $pageId );
    }

    public function getHomeUrl() {
        return get_home_url();
    }

    public function prepareQuery($query, $valueParameters) {
        $parameters = array_merge(array($query), $valueParameters);
        $callable = array($this->wpdb, 'prepare');

        return call_user_func_array( $callable, $parameters);
    }

    public function insertOrReplaceRow($table, $data, $format) {
        $this->wpdb->replace($table, $data, $format);
    }

    public function getOption($option) {
        return get_option($option);
    }

    public function getPluginDirectoryUrl($plugin) {
        return plugin_dir_url($plugin);
    }

    public function getUsers() {
        return get_users();
    }

    public function getNonceField($action) {
        return wp_nonce_field($action,'_wpnonce',true,false);
    }

    public function isNonceValid($nonce, $action) {
        return wp_verify_nonce($nonce, $action);
    }

    public function updateUserMeta($userId, $metaName, $metaValue) {
        update_user_meta( $userId, $metaName, $metaValue );
    }

    public function registerPostType($postType, $args) {
        register_post_type($postType, $args);
    }
} 