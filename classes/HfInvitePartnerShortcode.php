<?php

class HfInvitePartnerShortcode implements Hf_iShortcode {
    private $AssetLocator;
    private $MarkupGenerator;

    function __construct( Hf_iAssetLocator $AssetLocator, Hf_iMarkupGenerator $MarkupGenerator ) {
        $this->AssetLocator = $AssetLocator;
        $this->MarkupGenerator = $MarkupGenerator;
    }

    public function getOutput() {
        $currentUrl = $this->AssetLocator->getCurrentPageUrl();
        $form       = $this->generateForm( $currentUrl );

        return $this->emailError() . $form->getHtml();
    }

    private function generateForm( $currentUrl ) {
        $form = new HfGenericForm( $currentUrl );

        $form->addTextBox( 'email', 'Email', '', true );
        $form->addSubmitButton( 'submit', 'Invite' );

        return $form;
    }

    private function emailError() {
        if ( $this->isFormSubmitted() and $this->isEmailInvalid() ) {
            return $this->MarkupGenerator->makeError('Please enter a valid email address.');
        }
    }

    private function isFormSubmitted() {
        return isset( $_POST['submit'] );
    }

    private function isEmailInvalid() {
        return !filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL );
    }
}