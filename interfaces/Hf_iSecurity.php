<?php

interface Hf_iSecurity {
    public function createRandomString( $length );

    public function requireLogin();
}