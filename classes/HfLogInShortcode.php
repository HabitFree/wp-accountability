<?php

class HfLogInShortcode implements Hf_iShortcode {
    private $PageLocator;
    private $CodeLibrary;
    private $Cms;
    private $UserManager;

    function __construct( Hf_iAssetLocator $PageLocator, Hf_iCodeLibrary $CodeLibrary, Hf_iContentManagementSystem $ContentManagementSystem, Hf_iUserManager $UserManager ) {
        $this->PageLocator = $PageLocator;
        $this->CodeLibrary = $CodeLibrary;
        $this->Cms         = $ContentManagementSystem;
        $this->UserManager = $UserManager;
    }

    public function getOutput() {
        $html = '';

        if ( $this->isLogInSuccessful() ) {
            return $this->successMessage();
        } elseif ( $this->isLogInFormSubmitted() ) {
            $html .= $this->errorMessage();
        }

        $html .= $this->makeLogInForm()->getHtml();

        return $html;
    }

    private function isLogInSuccessful() {
        $logInSuccess = $this->attemptLogIn();

        return $this->isLogInFormSubmitted()
        and $this->isSubmittedFormValid()
        and $logInSuccess;
    }

    private function isSubmittedFormValid() {
        return !$this->CodeLibrary->isPostEmpty( 'username' )
        and !$this->CodeLibrary->isPostEmpty( 'password' );
    }

    private function isLogInFormSubmitted() {
        return !$this->CodeLibrary->isPostEmpty( 'submit' );
    }

    private function makeLogInForm() {
        $Form = new HfGenericForm( $this->PageLocator->getCurrentPageUrl() );

        $Form->addTextBox( 'username', 'Username', '', true );
        $Form->addPasswordBox( 'password', 'Password', true );
        $Form->addSubmitButton( 'submit', 'Log In' );

        return $Form;
    }

    private function successMessage() {
        return '<p class="success">You have been successfully logged in.</p><p><a href="/">Onward!</a></p>';
    }

    private function errorMessage() {
        return '<p class="fail">Please provide a valid username and password combination.</p>';
    }

    private function attemptLogIn() {
        $username = $this->CodeLibrary->getPost( 'username' );
        $password = $this->CodeLibrary->getPost( 'password' );

        $result = $this->Cms->authenticateUser( $username, $password );

        if ( $this->isInvite() and !$this->Cms->isError( $result ) ) {
            $this->processInvite( $result );
        }

        return !$this->Cms->isError( $result );
    }

    private function isInvite() {
        return !$this->CodeLibrary->isUrlParameterEmpty( 'n' );
    }

    private function processInvite( $invitee ) {
        $nonce     = $this->CodeLibrary->getUrlParameter( 'n' );
        $inviteeID = $invitee->ID;
        $this->UserManager->processInvite( $inviteeID, $nonce );
    }
} 