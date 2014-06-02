<?php

class HfWordPressInterface {
    private $DatabaseConnection;

    function __construct() {
        global $wpdb;
        $this->DatabaseConnection = $wpdb;
    }

    public function getUserEmail($userID) {
        return get_userdata( $userID )->user_email;
    }

    public function sendWpEmail($to, $subject, $message) {
        return wp_mail( $to, $subject, $message );
    }

    public function getSubscribedUsers() {
        return get_users(array(
            'meta_key' => 'hfSubscribed',
            'meta_value' => true
        ));
    }

    public function currentUser() {
        return wp_get_current_user();
    }

    public function getVar($table, $select, $where = null) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $tableName = $prefix . $table;
        if ($where === null) {
            return $wpdb->get_var("SELECT " . $select . " FROM " . $tableName);
        } else {
            return $wpdb->get_var("SELECT " . $select .
                " FROM " . $tableName .
                " WHERE " . $where );
        }
    }

    public function getDbPrefix() {
        return $this->DatabaseConnection->prefix;
    }

    public function executeQuery($query) {
        $this->wpdb->query($query);
    }
} 