<?php

class HfPartnerListShortcode implements Hf_iShortcode {
    private $UserManager;
    private $MarkupGenerator;

    function __construct( Hf_iUserManager $UserManager, Hf_iMarkupGenerator $MarkupGenerator ) {
        $this->UserManager     = $UserManager;
        $this->MarkupGenerator = $MarkupGenerator;
    }

    public function getOutput() {
        $Partners  = $this->getPartners();
        $listItems = $this->makeListItems( $Partners );

        return $this->MarkupGenerator->makeList( $listItems );
    }

    private function getPartners() {
        $userId   = $this->UserManager->getCurrentUserId();
        $Partners = $this->UserManager->getPartners( $userId );

        return $Partners;
    }

    private function makeListItems( $Partners ) {
        $listItems = array();
        foreach ( $Partners as $Partner ) {
            $listItems[] = $Partner->user_nicename . ' — unpartner';
        }

        return $listItems;
    }
}