<?php

class HfAuthenticateShortcode implements Hf_iShortcode {
    private $DisplayCodeGenerator;
    private $AssetLocator;

    private $username;
    private $email;

    private $isRegistering;
    private $isLoggingIn;

    function __construct( Hf_iDisplayCodeGenerator $DisplayCodeGenerator, Hf_iAssetLocator $AssetLocator ) {
        $this->DisplayCodeGenerator = $DisplayCodeGenerator;
        $this->AssetLocator = $AssetLocator;

        $this->determineUserIntent();
        $this->recallPostData();
    }

    public function getOutput() {
        return $this->getErrors() . $this->getTabs();
    }

    private function getErrors() {
        if ( $this->isRegistering ) {
            return $this->getRegistrationErrors();
        }
    }

    private function generateLogInForm() {
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
        if ( $this->isRegistering or $this->isLoggingIn ) {
            $this->username = $_POST['username'];
        }

        if ( $this->isRegistering ) {
            $this->email = $_POST['email'];
        }
    }

    private function getTabs() {
        $activeTabNumber = 1;

        $tabs = $this->DisplayCodeGenerator->generateTabs( array(
            'Log In'   => $this->generateLogInForm(),
            'Register' => $this->generateRegistrationForm()
        ), $activeTabNumber );

        return $tabs;
    }

    private function determineUserIntent() {
        $this->isRegistering = isset( $_POST['register'] );
        $this->isLoggingIn   = isset( $_POST['login'] );
    }

    private function getRegistrationErrors() {
        return $this->missingUsernameError() .
        $this->invalidEmailError() .
        $this->missingPasswordError() .
        $this->passwordMatchError();
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

    private function missingPasswordError() {
        if ( empty( $_POST['password'] ) ) {
            return '<p class="error">Please enter a password.</p>';
        }
    }
} 