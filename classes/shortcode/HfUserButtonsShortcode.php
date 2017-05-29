<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfUserButtonsShortcode implements Hf_iShortcode {
    private $userManager;
    private $assetLocator;
    private $markupGenerator;

    function __construct( Hf_iUserManager $userManager, Hf_iAssetLocator $assetLocator, Hf_iMarkupGenerator $markupGenerator ) {
        $this->userManager     = $userManager;
        $this->assetLocator    = $assetLocator;
        $this->markupGenerator = $markupGenerator;
    }

    public function getOutput() {
        $content = ( $this->userManager->isUserLoggedIn() ) ? $this->userLinks() : $this->visitorLinks();

        return $this->markupGenerator->paragraph( $content );
    }

    private function userLinks() {
        return $this->welcomeMessage() . ' | ' . $this->logoutLink() . ' | ' . $this->settingsLink();
    }

    private function visitorLinks() {
        return $this->loginLink() . ' | ' . $this->registerLink();
    }

    private function welcomeMessage() {
        return ( $this->userManager->isUserLoggedIn() ) ? 'Welcome back, ' . $this->userManager->getCurrentUserLogin() : '';
    }

    private function logoutLink() {
        $currentPageUrl = $this->assetLocator->getCurrentPageUrl();
        $logoutUrl      = $this->assetLocator->getLogoutUrl( $currentPageUrl );

        return $this->markupGenerator->linkMarkup( $logoutUrl, 'Log Out' );
    }

    private function loginLink() {
        $loginUrl = $this->assetLocator->getLoginUrl();

        return $this->markupGenerator->linkMarkup( $loginUrl, 'Log In' );
    }

    private function settingsLink() {
        $settingsUrl = $this->assetLocator->getPageUrlByTitle( 'Settings' );

        return $this->markupGenerator->linkMarkup( $settingsUrl, 'Settings' );

    }

    private function registerLink() {
        $registerUrl = $this->assetLocator->getPageUrlByTitle( 'Authenticate' );

        return $this->markupGenerator->linkMarkup( $registerUrl, 'Register' );

    }
}