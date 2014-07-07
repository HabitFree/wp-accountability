<?php

class HfPartnerListShortcode implements Hf_iShortcode {
    private $UserManager;

    function __construct( Hf_iUserManager $UserManager ) {
        $this->UserManager = $UserManager;
    }

    public function getOutput() {
        $this->UserManager->getPartners(0);
    }
}