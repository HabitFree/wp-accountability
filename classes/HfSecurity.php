<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfSecurity implements Hf_iSecurity {
    public function createRandomString( $length ) {
        $randomBits = openssl_random_pseudo_bytes( $length / 2 );

        return bin2hex( $randomBits );
    }

    public function requireLogin() {
        return '<p class="fail">You must be logged in to view this page.</p> ' . wp_login_form( array('echo' => false) );
    }
}
