<?php

class HfInvitePartnerShortcode implements Hf_iShortcode {
    private $AssetLocator;

    function __construct( Hf_iAssetLocator $AssetLocator ) {
        $this->AssetLocator = $AssetLocator;
    }

    public function getOutput() {
        $currentUrl = $this->AssetLocator->getCurrentPageUrl();
        $form       = $this->generateForm( $currentUrl );

        return $form->getHtml();
    }

    private function generateForm( $currentUrl ) {
        $form = new HfGenericForm( $currentUrl );

        $form->addTextBox( 'email', 'Email', '', true );
        $form->addSubmitButton( 'submit', 'Invite' );

        return $form;
    }
} 