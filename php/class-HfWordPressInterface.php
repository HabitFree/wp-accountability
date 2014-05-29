<?php

class HfWordPressInterface {

    public function getUserEmail($userID) {
        return get_userdata( $userID )->user_email;
    }

    public function sendWpEmail($to, $subject, $message) {
        return wp_mail( $to, $subject, $message );
    }

} 