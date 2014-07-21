<?php
if ( ! defined( 'ABSPATH' ) ) exit;
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
        $content = ( $this->UserManager->isUserLoggedIn() ) ? $this->userLinks() : $this->visitorLinks();

        return $this->MarkupGenerator->makeParagraph( $content );
    }

    private function userLinks() {
        return $this->welcomeMessage() . ' | ' . $this->logoutLink() . ' | ' . $this->settingsLink();
    }

    private function visitorLinks() {
        return $this->loginLink() . ' | ' . $this->registerLink();
    }

    private function welcomeMessage() {
        return ( $this->UserManager->isUserLoggedIn() ) ? 'Welcome back, ' . $this->UserManager->getCurrentUserLogin() : '';
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

    private function registerLink() {
        $registerUrl = $this->AssetLocator->getPageUrlByTitle( 'Authenticate' );

        return $this->MarkupGenerator->makeLink( $registerUrl, 'Register' );

    }
}