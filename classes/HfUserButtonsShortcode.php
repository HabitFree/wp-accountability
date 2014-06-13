<?php

class HfUserButtonsShortcode implements Hf_iShortcode {
    private $UserManager;

    function __construct( Hf_iUserManager $UserManager ) {
        $this->UserManager = $UserManager;
    }

    public function getOutput() {
        return '<p>Welcome back, ' . $this->UserManager->getCurrentUserLogin();
    }
} 