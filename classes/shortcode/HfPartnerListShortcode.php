<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfPartnerListShortcode implements Hf_iShortcode {
    private $userManager;
    private $markupGenerator;
    private $assetLocator;

    function __construct( Hf_iUserManager $userManager, Hf_iMarkupGenerator $markupGenerator, Hf_iAssetLocator $assetLocator ) {
        $this->userManager     = $userManager;
        $this->markupGenerator = $markupGenerator;
        $this->assetLocator    = $assetLocator;
    }

    public function getOutput() {
        $this->processSubmittedForm();

        $Partners      = $this->getPartners();
        $listItems     = $this->makeListItems( $Partners );
        $currentUrl    = $this->assetLocator->getCurrentPageUrl();
        $list          = $this->markupGenerator->listMarkup( $listItems );
        $hiddenField   = $this->markupGenerator->hiddenField( 'userId' );
        $submitHandler =
            '<script>
                function submitValue (n) {
                    var f = document.forms.partnerlist;
                    f.userId.value = n;
                    f.submit();
                }
            </script>';

        return $this->markupGenerator->form( $currentUrl, $submitHandler . $hiddenField . $list, 'partnerlist' );
    }

    private function processSubmittedForm() {
        if ( $this->isFormSubmitted() ) {
            $currentUserId = $this->userManager->getCurrentUserId();
            foreach ( $_POST as $partnerId ) {
                $this->userManager->deleteRelationship( $currentUserId, $partnerId );
            }
        }
    }

    private function getPartners() {
        $userId   = $this->userManager->getCurrentUserId();
        $Partners = $this->userManager->getPartners( $userId );

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
        $username = $Partner->user_nicename;
        $confirmationMessage = "\"Are you sure you want to stop partnering with $username?\"";
        $partnerId = $Partner->ID;
        $onclick = "if (confirm($confirmationMessage)) { submitValue($partnerId);}";
        return
            $this->markupGenerator->buttonInput(
                $Partner->ID,
                'unpartner',
                $onclick
            );
    }
}