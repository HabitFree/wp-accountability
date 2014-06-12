<?php

class HfAuthenticateShortcode implements Hf_iShortcode {
    private $DisplayCodeGenerator;
    private $AssetLocator;
    private $Cms;

    private $username;
    private $email;

    private $loginErrors;
    private $registrationErrors;

    function __construct( Hf_iDisplayCodeGenerator $DisplayCodeGenerator, Hf_iAssetLocator $AssetLocator, Hf_iContentManagementSystem $ContentManagementSystem ) {
        $this->DisplayCodeGenerator = $DisplayCodeGenerator;
        $this->AssetLocator         = $AssetLocator;
        $this->Cms                  = $ContentManagementSystem;
    }

    public function getOutput() {
        $this->recallPostData();

        $this->validateLoginForm();
        $this->validateRegistrationForm();

        $this->attemptLogin();

        return $this->getTabs();
    }

    private function generateLoginForm() {
        $Form = new HfGenericForm( $this->AssetLocator->getCurrentPageUrl() );

        $Form->addTextBox( 'username', 'Username', $this->username, true );
        $Form->addPasswordBox( 'password', 'Password', true );
        $Form->addSubmitButton( 'login', 'Log In' );

        return $Form->getHtml();
    }

    private function generateRegistrationForm() {
        $Form = new HfGenericForm( $this->AssetLocator->getCurrentPageUrl() );

        $Form->addTextBox( 'username', 'Username', $this->username, true );
        $Form->addTextBox( 'email', 'Email', $this->email, true );
        $Form->addPasswordBox( 'password', 'Password', true );
        $Form->addPasswordBox( 'passwordConfirmation', 'Confirm Password', true );
        $Form->addSubmitButton( 'register', 'Register' );

        return $Form->getHtml();
    }

    private function recallPostData() {
        if ( $this->isRegistering() or $this->isLoggingIn() ) {
            $this->username = $_POST['username'];
        }

        if ( $this->isRegistering() ) {
            $this->email = $_POST['email'];
        }
    }

    private function getTabs() {
        $activeTabNumber = $this->determineActiveTab();

        $tabs = $this->DisplayCodeGenerator->generateTabs( array(
            'Log In'   => $this->loginErrors . $this->generateLoginForm(),
            'Register' => $this->registrationErrors . $this->generateRegistrationForm()
        ), $activeTabNumber );

        return $tabs;
    }

    private function validateLoginForm() {
        if ( $this->isLoggingIn() ) {
            $this->loginErrors .=
                $this->missingUsernameError() .
                $this->missingPasswordError();
        }
    }

    private function validateRegistrationForm() {
        if ( $this->isRegistering() ) {
            $this->registrationErrors .=
                $this->missingUsernameError() .
                $this->invalidEmailError() .
                $this->emailTakenError() .
                $this->missingPasswordError() .
                $this->passwordMatchError();
        }
    }

    private function passwordMatchError() {
        if ( $_POST['password'] !== $_POST['passwordConfirmation'] ) {
            return '<p class="error">Please make sure your passwords match.</p>';
        }
    }

    private function missingUsernameError() {
        if ( empty( $_POST['username'] ) ) {
            return '<p class="error">Please enter a username.</p>';
        }
    }

    private function invalidEmailError() {
        if ( !filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL ) ) {
            return '<p class="error">Please enter a valid email address.</p>';
        }
    }

    private function emailTakenError() {
        if ( $this->Cms->isEmailTaken( $_POST['email'] ) ) {
            return '<p class="error">That email is already taken. Did you mean to log in?</p>';
        }
    }

    private function missingPasswordError() {
        if ( empty( $_POST['password'] ) ) {
            return '<p class="error">Please enter a password.</p>';
        }
    }

    private function isRegistering() {
        return isset( $_POST['register'] );
    }

    private function isLoggingIn() {
        return isset( $_POST['login'] );
    }

    private function determineActiveTab() {
        if ( $this->isRegistering() ) {
            return 2;
        } else {
            return 1;
        }
    }

    private function attemptLogin() {
        if ( $this->isLoggingIn() ) {
            if ( !$this->Cms->authenticateUser( $_POST['username'], $_POST['password'] ) ) {
                $this->loginErrors .= '<p class="error">That username and password combination is incorrect.</p>';
            }
        }
    }
} 