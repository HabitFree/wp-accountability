<?php

class HfPartnerListShortcode implements Hf_iShortcode {
    private $UserManager;
    private $MarkupGenerator;
    private $AssetLocator;

    function __construct( Hf_iUserManager $UserManager, Hf_iMarkupGenerator $MarkupGenerator, Hf_iAssetLocator $AssetLocator ) {
        $this->UserManager     = $UserManager;
        $this->MarkupGenerator = $MarkupGenerator;
        $this->AssetLocator    = $AssetLocator;
    }

    public function getOutput() {
        $Partners   = $this->getPartners();
        $listItems  = $this->makeListItems( $Partners );
        $currentUrl = $this->AssetLocator->getCurrentPageUrl();
        $list       = $this->MarkupGenerator->makeList( $listItems );

        return $this->MarkupGenerator->makeForm( $currentUrl, $list, 'partnerlist' );
    }

    private function getPartners() {
        $userId   = $this->UserManager->getCurrentUserId();
        $Partners = $this->UserManager->getPartners( $userId );

        return $Partners;
    }

    private function makeListItems( $Partners ) {
        $listItems = array();
        foreach ( $Partners as $Partner ) {
            $listItems[] = $Partner->user_nicename . ' — ' . $this->makeUnpartnerButton( $Partner->ID );
        }

        return $listItems;
    }

    private function makeUnpartnerButton( $partnerId ) {
        return $this->MarkupGenerator->makeButton($partnerId, 'unpartner', "if (confirm('Sure?')) { document.partnerlist.submit();}");
    }
}