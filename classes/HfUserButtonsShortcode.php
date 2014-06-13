<?php

class HfUserButtonsShortcode implements Hf_iShortcode {
    private $UserManager;
    private $AssetLocator;

    function __construct( Hf_iUserManager $UserManager, Hf_iAssetLocator $AssetLocator ) {
        $this->UserManager  = $UserManager;
        $this->AssetLocator = $AssetLocator;
    }

    public function getOutput() {
        return '<p>' . $this->welcomeMessage() . ' | ' . $this->logoutLink();
    }

    private function welcomeMessage() {
        return 'Welcome back, ' . $this->UserManager->getCurrentUserLogin();
    }

    private function logoutLink() {
        $currentPageUrl = $this->AssetLocator->getCurrentPageUrl();
        $logoutUrl = $this->AssetLocator->getLogoutUrl( $currentPageUrl );

        return '<a href="' . $logoutUrl . '">Log Out</a>';
    }
}