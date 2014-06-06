<?php

class HfRegisterShortcode implements hf_iShortcode {
    private $UrlFinder;
    private $Database;
    private $CodeLibrary;
    private $Cms;
    private $UserManager;

    function __construct($UrlFinder, $Database, $CodeLibrary, $ContentManagementSystem, $UserManager) {
        $this->UrlFinder = $UrlFinder;
        $this->Database     = $Database;
        $this->CodeLibrary  = $CodeLibrary;
        $this->Cms          = $ContentManagementSystem;
        $this->UserManager  = $UserManager;
    }

    public function getOutput() {
        $html       = '';
        $currentUrl = $this->UrlFinder->getCurrentPageUrl();
        $Form       = new HfGenericForm($currentUrl);

        $username   = '';
        $email      = '';

        if ( !$this->CodeLibrary->isPostEmpty('submit') ) {
            $username   = $this->CodeLibrary->getPost('username');
            $password   = $this->CodeLibrary->getPost('password');
            $email      = $this->CodeLibrary->getPost('email');

            if ( $this->isRegistrationFormValid() ) {
                $this->registerNewUser($username, $password, $email);
                return '<p class="success">You have been successfully registered. Welcome to HabitFree!</p>
                        <p><a href="/">Onward</a></p>';
            }

            $html       .= $this->getRegistrationFormErrors();
        }

        $Form->addTextBox('username', 'Username', $username, true);
        $Form->addTextBox('email', 'Email', $email, true);
        $Form->addPasswordBox('password', 'Password', true);
        $Form->addPasswordBox('passwordConfirmation', 'Confirm Password', true);
        $Form->addSubmitButton('submit', 'Register');

        $html .= $Form->getHtml();

        return $html;
    }

    private function isRegistrationFormValid() {
        return !$this->CodeLibrary->isPostEmpty('username')
            and !$this->isUsernameAlreadyTaken()
            and !$this->CodeLibrary->isPostEmpty('email')
            and $this->emailAvailable()
            and !$this->isEmailInvalid()
            and !$this->CodeLibrary->isPostEmpty('password')
            and !$this->CodeLibrary->isPostEmpty('passwordConfirmation')
            and $this->doPasswordsMatch();
    }

    private function getRegistrationFormErrors() {
        $html = '';

        if ( $this->CodeLibrary->isPostEmpty('username') ) {
            $html .= '<p class="fail">Please provide a username.</p>';
        }

        if ( $this->isUsernameAlreadyTaken() ) {
            $html .= '<p class="fail">Sorry, that username is already taken. Please select another.</p>';
        }

        if ( $this->CodeLibrary->isPostEmpty('email') or $this->isEmailInvalid() ) {
            $html .= '<p class="fail">Please provide a valid email address.</p>';
        }

        if ( !$this->emailAvailable() ) {
            $html .= "<p class='fail'>Oops. That email is already in use.</p>";
        }

        if ( $this->CodeLibrary->isPostEmpty('password') ) {
            $html .= '<p class="fail">Please provide a password.</p>';
        }

        if ( $this->CodeLibrary->isPostEmpty('passwordConfirmation') ) {
            $html .= '<p class="fail">Please confirm your password.</p>';
        }

        if ( !$this->doPasswordsMatch() ) {
            $html .= "<p class='fail'>Those passwords don't match. Please retype and confirm your desired password.</p>";
        }

        return $html;
    }

    private function registerNewUser($username, $password, $email) {
        $userID = $this->Cms->createUser( $username, $password, $email );
        $this->UserManager->processNewUser($userID);

        if ($this->isInvited()) {
            $nonce = $this->CodeLibrary->getGet('n');
            $this->UserManager->processInvite($userID, $nonce);
        }
    }

    private function isInvited() {
        return !$this->CodeLibrary->isUrlParameterEmpty('n');
    }

    private function isUsernameAlreadyTaken() {
        return username_exists($this->CodeLibrary->getPost('username'));
    }

    private function isEmailInvalid() {
        return !filter_var($this->CodeLibrary->getPost('email'), FILTER_VALIDATE_EMAIL);
    }

    private function doPasswordsMatch() {
        return $this->CodeLibrary->getPost('password') === $this->CodeLibrary->getPost('passwordConfirmation');
    }

    private function emailAvailable() {
        return !$this->Cms->isEmailTaken($this->CodeLibrary->getPost('email'));
    }
} 