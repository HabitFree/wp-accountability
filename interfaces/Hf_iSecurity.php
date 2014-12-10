<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iSecurity {
    public function createRandomString( $length );

    public function requireLogin();

    public function makeNonceField($action);

    public function isNonceValid($nonce, $action);
}