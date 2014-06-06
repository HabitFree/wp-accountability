<?php

class HfWordPressInterface implements Hf_iContentManagementSystem {
    private $wpdb;

    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function getUserEmail( $userID ) {
        return get_userdata( $userID )->user_email;
    }

    public function sendWpEmail( $to, $subject, $message ) {
        return wp_mail( $to, $subject, $message );
    }

    public function getSubscribedUsers() {
        return get_users( array(
            'meta_key'   => 'hfSubscribed',
            'meta_value' => true
        ) );
    }

    public function currentUser() {
        return wp_get_current_user();
    }

    public function getVar( $query ) {
        global $wpdb;

        return $wpdb->get_var( $query );
    }

    public function getDbPrefix() {
        return $this->wpdb->prefix;
    }

    public function executeQuery( $query ) {
        global $wpdb;
        $wpdb->query( $query );
        print( 'hello' );
    }

    public function createUser( $username, $password, $email ) {
        return wp_create_user( $username, $password, $email );
    }

    public function isUserLoggedIn() {
        return is_user_logged_in();
    }

    public function getRows( $table, $where, $outputType = OBJECT ) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        if ( $where === null ) {
            return $wpdb->get_results( "SELECT * FROM " . $prefix . $table, $outputType );
        } else {
            return $wpdb->get_results( "SELECT * FROM " . $prefix . $table . " WHERE " . $where, $outputType );
        }
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
} 