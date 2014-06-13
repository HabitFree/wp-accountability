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

        if($this->UserManager->isUserLoggedIn()) {
            $html .= ' | ' . $this->logoutLink();
        }

        return $this->wrapWithParagraphTags( $html );
    }

    private function welcomeMessage() {
        return 'Welcome back, ' . $this->UserManager->getCurrentUserLogin();
    }

    private function logoutLink() {
        $currentPageUrl = $this->AssetLocator->getCurrentPageUrl();
        $logoutUrl = $this->AssetLocator->getLogoutUrl( $currentPageUrl );

        return '<a href="' . $logoutUrl . '">Log Out</a>';
    }

    private function wrapWithParagraphTags( $html ) {
        return '<p>' . $html . '</p>';
    }
}