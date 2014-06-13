<?php

class HfUserButtonsShortcode implements Hf_iShortcode {
    private $UserManager;
    private $AssetLocator;

    function __construct( Hf_iUserManager $UserManager, Hf_iAssetLocator $AssetLocator ) {
        $this->UserManager  = $UserManager;
        $this->AssetLocator = $AssetLocator;
    }

    public function getOutput() {
        $html = $this->welcomeMessage();

        $html .= ' | ' . $this->logInOrOutLink( $html );

        return $this->wrapWithParagraphTags( $html );
    }

    private function welcomeMessage() {
        return ($this->UserManager->isUserLoggedIn()) ? 'Welcome back, ' . $this->UserManager->getCurrentUserLogin() : '';
    }

    private function wrapWithParagraphTags( $html ) {
        return '<p>' . $html . '</p>';
    }

    private function logInOrOutLink() {
        return ( $this->UserManager->isUserLoggedIn() ) ? $this->logoutLink() : $this->loginLink();
    }

    private function logoutLink() {
        $currentPageUrl = $this->AssetLocator->getCurrentPageUrl();
        $logoutUrl      = $this->AssetLocator->getLogoutUrl( $currentPageUrl );

        return '<a href="' . $logoutUrl . '">Log Out</a>';
    }

    private function loginLink() {
        $loginUrl = $this->AssetLocator->getLoginUrl();

        return '<a href="' . $loginUrl . '">Log In</a>';
    }
}