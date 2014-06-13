<?php

class HfAuthenticateShortcode implements Hf_iShortcode {
    private $DisplayCodeGenerator;
    private $AssetLocator;
    private $Cms;
    private $UserManager;

    private $username;
    private $email;

    private $loginMessages;
    private $registrationMessages;
    private $additionalHtml;

    private $isLoginSuccessful = false;
    private $isRegistrationSuccessful = false;

    function __construct( Hf_iMarkupGenerator $DisplayCodeGenerator, Hf_iAssetLocator $AssetLocator, Hf_iContentManagementSystem $ContentManagementSystem, Hf_iUserManager $UserManager ) {
        $this->DisplayCodeGenerator = $DisplayCodeGenerator;
        $this->AssetLocator         = $AssetLocator;
        $this->Cms                  = $ContentManagementSystem;
        $this->UserManager          = $UserManager;
    }

    public function getOutput() {
        $this->recallPostData();

        $this->informInvitedUser();

        $this->validateLoginForm();
        $this->validateRegistrationForm();

        $this->processLoginRequest();
        $this->processRegistrationRequest();

        return $this->additionalHtml . $this->getTabs();
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
            'Log In'   => $this->loginMessages . $this->generateLoginForm(),
            'Register' => $this->registrationMessages . $this->generateRegistrationForm()
        ), $activeTabNumber );

        return $tabs;
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

        $usernameChoiceMessage =
            '<strong>Important:</strong> HabitFree is a support community. For this reason, please choose a non-personally-identifiable username.';

        $Form->addInfoMessage($usernameChoiceMessage);
        $Form->addTextBox( 'username', 'Username', $this->username, true );
        $Form->addTextBox( 'email', 'Email', $this->email, true );
        $Form->addPasswordBox( 'password', 'Password', true );
        $Form->addPasswordBox( 'passwordConfirmation', 'Confirm Password', true );
        $Form->addSubmitButton( 'register', 'Register' );

        return $Form->getHtml();
    }

    private function validateLoginForm() {
        if ( $this->isLoggingIn() ) {
            $this->loginMessages .=
                $this->missingUsernameError() .
                $this->missingPasswordError();
        }
    }

    private function validateRegistrationForm() {
        if ( $this->isRegistering() ) {
            $this->registrationMessages .=
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
            return '<p class="error">Please enter your username.</p>';
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
            return '<p class="error">Please enter your password.</p>';
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
        } elseif ($this->isLoggingIn()) {
            return 1;
        } elseif (!empty($_GET['tab'])) {
            return $_GET['tab'];
        } else {
            return 1;
        }
    }

    private function processLoginRequest() {
        if ( $this->isLoggingIn() and $this->isLoginFormValid() ) {
            $this->attemptLogin();

            if ( $this->isLoginSuccessful ) {
                $this->processInvite();
                $this->loginMessages .=
                    '<p class="success">Welcome back!</p>';
                $this->redirectUser();
            } else {
                $this->loginMessages .=
                    '<p class="error">That username and password combination is incorrect.</p>';
            }
        }
    }

    private function attemptLogin() {
        $success = $this->Cms->authenticateUser( $_POST['username'], $_POST['password'] );
        if ( $success ) {
            $this->isLoginSuccessful = true;
        }
    }

    private function processRegistrationRequest() {
        if ( $this->isRegistering() and $this->isRegistrationFormValid() ) {
            $this->attemptRegistration();

            if ( $this->isRegistrationSuccessful ) {
                $this->attemptLogin();
                $this->processInvite();
                $this->registrationMessages .=
                    '<p class="success">Welcome to HabitFree!</p>';
                $this->redirectUser();
            } else {
                $this->registrationMessages .=
                    "<p class='error'>We're very sorry, but something seems to have gone wrong with your registration.</p>";
            }
        }
    }

    private function attemptRegistration() {
        $success = $this->Cms->createUser( $_POST['username'], $_POST['password'], $_POST['email'] );
        if ( $success ) {
            $this->isRegistrationSuccessful = true;
        }
    }

    private function redirectUser() {
        $url = $this->AssetLocator->getHomePageUrl();
        $this->registrationMessages .=
            '<p class="info">Redirecting... <a href="' . $url . '">Click here</a> if you are not automatically redirected. <a href="' . $url . '">Onward!</a></p>';
        $this->additionalHtml .=
            '<script>setTimeout(function(){window.location.replace("' . $url . '")},5000);</script>';
    }

    private function processInvite() {
        if ( $this->isInvite() ) {
            $user         = $this->Cms->currentUser();
            $inviteeEmail = $this->Cms->getUserEmail( $user->ID );
            $this->UserManager->processInvite( $inviteeEmail, $_GET['n'] );
        }
    }

    private function isRegistrationFormValid() {
        return empty( $this->registrationMessages );
    }

    private function isLoginFormValid() {
        return empty( $this->loginMessages );
    }

    private function informInvitedUser() {
        if ( $this->isInvite() ) {
            $this->additionalHtml .=
                "<p class='info'>Looks like you're responding to an invitation. Feel free to either register or log into an existing account—either way we'll automatically set up accountability between you and the user who invited you.</p>";
        }
    }

    private function isInvite() {
        return !empty( $_GET['n'] );
    }
} 