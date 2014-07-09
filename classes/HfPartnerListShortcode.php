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
        $this->processSubmittedForm();

        $Partners      = $this->getPartners();
        $listItems     = $this->makeListItems( $Partners );
        $currentUrl    = $this->AssetLocator->getCurrentPageUrl();
        $list          = $this->MarkupGenerator->makeList( $listItems );
        $hiddenField   = $this->MarkupGenerator->makeHiddenField( 'userId' );
        $submitHandler =
            '<script>
                function submitValue (n) {
                    var f = document.forms.partnerlist;
                    f.userId.value = n;
                    f.submit();
                }
            </script>';

        return $this->MarkupGenerator->makeForm( $currentUrl, $submitHandler . $hiddenField . $list, 'partnerlist' );
    }

    private function processSubmittedForm() {
        if ( $this->isFormSubmitted() ) {
            $currentUserId = $this->UserManager->getCurrentUserId();
            foreach ( $_POST as $partnerId ) {
                $this->UserManager->deleteRelationship( $currentUserId, $partnerId );
            }
        }
    }

    private function getPartners() {
        $userId   = $this->UserManager->getCurrentUserId();
        $Partners = $this->UserManager->getPartners( $userId );

        return $Partners;
    }

    private function makeListItems( $Partners ) {
        $listItems = array();
        foreach ( $Partners as $Partner ) {
            $listItems[] = $Partner->user_nicename . ' — ' . $this->makeUnpartnerButton( $Partner );
        }

        return $listItems;
    }

    private function isFormSubmitted() {
        return !empty( $_POST );
    }

    private function makeUnpartnerButton( $Partner ) {
        return
            $this->MarkupGenerator->makeButton(
                $Partner->ID,
                'unpartner', "if (confirm('Are you sure you want to stop partnering with " . $Partner->user_nicename . "?')) { submitValue(" . $Partner->ID . ");}"
            );
    }
}