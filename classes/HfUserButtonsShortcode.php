<?php

class HfUserButtonsShortcode implements Hf_iShortcode {
    private $UserManager;
    private $AssetLocator;
    private $MarkupGenerator;

    function __construct( Hf_iUserManager $UserManager, Hf_iAssetLocator $AssetLocator, Hf_iMarkupGenerator $MarkupGenerator ) {
        $this->UserManager     = $UserManager;
        $this->AssetLocator    = $AssetLocator;
        $this->MarkupGenerator = $MarkupGenerator;
    }

    public function getOutput() {
        $sep  = ' | ';
        $html = $this->welcomeMessage() . $sep . $this->logInOrOutLink() . $sep . $this->settingsLink();

        return $this->MarkupGenerator->makeParagraph( $html );
    }

    private function welcomeMessage() {
        return ( $this->UserManager->isUserLoggedIn() ) ? 'Welcome back, ' . $this->UserManager->getCurrentUserLogin() : '';
    }

    private function logInOrOutLink() {
        return ( $this->UserManager->isUserLoggedIn() ) ? $this->logoutLink() : $this->loginLink();
    }

    private function logoutLink() {
        $currentPageUrl = $this->AssetLocator->getCurrentPageUrl();
        $logoutUrl      = $this->AssetLocator->getLogoutUrl( $currentPageUrl );

        return $this->MarkupGenerator->makeLink( $logoutUrl, 'Log Out' );
    }

    private function loginLink() {
        $loginUrl = $this->AssetLocator->getLoginUrl();

        return $this->MarkupGenerator->makeLink( $loginUrl, 'Log In' );
    }

    private function settingsLink() {
        $settingsUrl = $this->AssetLocator->getPageUrlByTitle( 'Settings' );

        return $this->MarkupGenerator->makeLink( $settingsUrl, 'Settings' );
    }
}