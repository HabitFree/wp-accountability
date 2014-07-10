<?php

class HfAuthenticateShortcode implements Hf_iShortcode {
    private $MarkupGenerator;
    private $AssetLocator;
    private $Cms;
    private $UserManager;

    private $username;
    private $email;

    private $loginMessages;
    private $registrationMessages;
    private $output;

    private $isLoginSuccessful = false;
    private $isRegistrationSuccessful = false;

    function __construct(
        Hf_iMarkupGenerator $MarkupGenerator,
        Hf_iAssetLocator $AssetLocator,
        Hf_iContentManagementSystem $ContentManagementSystem,
        Hf_iUserManager $UserManager
    ) {
        $this->MarkupGenerator = $MarkupGenerator;
        $this->AssetLocator    = $AssetLocator;
        $this->Cms             = $ContentManagementSystem;
        $this->UserManager     = $UserManager;
    }

    public function getOutput() {
        $this->recallPostData();
        $this->validateForms();
        $this->processSubmissions();
        $this->makeAuthenticationForm();
        $this->makeInviteResponseForm();

        return $this->output;
    }

    private function recallPostData() {
        if ( $this->isRegistering() or $this->isLoggingIn() ) {
            $this->username = $_POST['username'];
        }

        if ( $this->isRegistering() ) {
            $this->email = $_POST['email'];
        }
    }

    private function validateForms() {
        $this->validateLoginForm();
        $this->validateRegistrationForm();
    }

    private function processSubmissions() {
        $this->processLoginRequest();
        $this->processRegistrationRequest();
        $this->processInviteFormSubmission();
    }

    private function makeAuthenticationForm() {
        if ( !$this->UserManager->isUserLoggedIn() ) {
            $this->informInvitedUser();
            $activeTabNumber = $this->determineActiveTab();

            $tabbedForms = $this->MarkupGenerator->generateTabs( array(
                'Log In'   => $this->loginMessages . $this->generateLoginForm(),
                'Register' => $this->registrationMessages . $this->generateRegistrationForm()
            ), $activeTabNumber );

            $this->output .= $tabbedForms;
        }
    }

    private function makeInviteResponseForm() {
        if ( $this->isInvite() and $this->UserManager->isUserLoggedIn() and !$this->isInviteFormSubmitted() ) {
            $currentUrl = $this->AssetLocator->getCurrentPageUrl();

            $Form = new HfGenericForm( $currentUrl );
            $Form->addInfoMessage( "Looks like you're responding to an invite. What would you like to do?" );
            $Form->addSubmitButton( 'accept', 'Accept invitation' );
            $Form->addSubmitButton( 'ignore', 'Ignore invitation' );

            $this->output .= $Form->getHtml();
        }
    }

    private function isRegistering() {
        return isset( $_POST['register'] );
    }

    private function isLoggingIn() {
        return isset( $_POST['login'] );
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

    private function processLoginRequest() {
        if ( $this->isLoggingIn() and $this->isLoginFormValid() ) {
            $this->attemptLogin();

            if ( $this->isLoginSuccessful ) {
                $this->processInvite();
                $this->loginMessages .= $this->MarkupGenerator->makeSuccessMessage( 'Welcome back!' );
                $this->redirectUser();
            } else {
                $errorMessageText = 'That username and password combination is incorrect.';
                $this->loginMessages .= $this->MarkupGenerator->makeErrorMessage( $errorMessageText );
            }
        }
    }

    private function processRegistrationRequest() {
        if ( $this->isRegistering() and $this->isRegistrationFormValid() ) {
            $this->attemptRegistration();

            if ( $this->isRegistrationSuccessful ) {
                $this->attemptLogin();
                $this->processInvite();
                $this->registrationMessages .= $this->MarkupGenerator->makeSuccessMessage( 'Welcome to HabitFree!' );
                $this->redirectUser();
            } else {
                $errorMessageText = "We're very sorry, but something seems to have gone wrong with your registration.";
                $this->registrationMessages .= $this->MarkupGenerator->makeErrorMessage( $errorMessageText );
            }
        }
    }

    private function processInviteFormSubmission() {
        if ( $this->isInvite() and $this->isInviteAccepted() ) {
            $this->processInvite();
            $this->output .= $this->MarkupGenerator->makeSuccessMessage( 'Invitation processed successfully.' );
        } elseif ( $this->isInviteIgnored() ) {
            $this->output .= $this->MarkupGenerator->makeSuccessMessage( 'Invitation ignored successfully.' );
        }
    }

    private function informInvitedUser() {
        if ( $this->isInvite() ) {
            $infoMessageText = "Looks like you're responding to an invitation. Feel free to either register or log into an existing account—either way we'll automatically set up accountability between you and the user who invited you.";
            $this->output .= $this->MarkupGenerator->makeInfoMessage( $infoMessageText );
        }
    }

    private function determineActiveTab() {
        if ( $this->isRegistering() ) {
            return 2;
        } elseif ( $this->isLoggingIn() ) {
            return 1;
        } elseif ( !empty( $_GET['tab'] ) ) {
            return $_GET['tab'];
        } else {
            return 1;
        }
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

        $Form->addInfoMessage( $usernameChoiceMessage );
        $Form->addTextBox( 'username', 'Username', $this->username, true );
        $Form->addTextBox( 'email', 'Email', $this->email, true );
        $Form->addPasswordBox( 'password', 'Password', true );
        $Form->addPasswordBox( 'passwordConfirmation', 'Confirm Password', true );
        $Form->addSubmitButton( 'register', 'Register' );

        return $Form->getHtml();
    }

    private function isInvite() {
        return !empty( $_GET['n'] );
    }

    private function isInviteFormSubmitted() {
        return $this->isInviteAccepted() or $this->isInviteIgnored();
    }

    private function missingUsernameError() {
        $errorMessage = $this->MarkupGenerator->makeErrorMessage( 'Please enter your username.' );

        return ( $this->isUsernameMissing() ) ? $errorMessage : '';
    }

    private function missingPasswordError() {
        $errorMessage = $this->MarkupGenerator->makeErrorMessage( 'Please enter your password.' );

        return ( $this->isPasswordMissing() ) ? $errorMessage : '';
    }

    private function invalidEmailError() {
        $errorMessage = $this->MarkupGenerator->makeErrorMessage( 'Please enter a valid email address.' );

        return ( $this->isEmailMalformed() ) ? $errorMessage : '';
    }

    private function emailTakenError() {
        $errorMessage = $this->MarkupGenerator->makeErrorMessage( 'That email is already taken. Did you mean to log in?' );

        return ( $this->isEmailTaken() ) ? $errorMessage : '';
    }

    private function passwordMatchError() {
        $errorMessage = $this->MarkupGenerator->makeErrorMessage( 'Please make sure your passwords match.' );

        return ( $this->isPasswordMismatch() ) ? $errorMessage : '';
    }

    private function isLoginFormValid() {
        return empty( $this->loginMessages );
    }

    private function attemptLogin() {
        $success = $this->Cms->authenticateUser( $_POST['username'], $_POST['password'] );
        if ( $success ) {
            $this->isLoginSuccessful = true;
        }
    }

    private function processInvite() {
        if ( $this->isInvite() ) {
            $user         = $this->Cms->currentUser();
            $inviteeEmail = $this->Cms->getUserEmail( $user->ID );
            $this->UserManager->processInvite( $inviteeEmail, $_GET['n'] );
        }
    }

    private function redirectUser() {
        $url             = $this->AssetLocator->getHomePageUrl();
        $redirectMessage = $this->makeRedirectMessage( $url );

        $this->registrationMessages .= $redirectMessage;
        $this->loginMessages .= $redirectMessage;

        $this->output .= '<script>setTimeout(function(){window.location.replace("' . $url . '")},5000);</script>';
    }

    private function isRegistrationFormValid() {
        return empty( $this->registrationMessages );
    }

    private function attemptRegistration() {
        $success = $this->Cms->createUser( $_POST['username'], $_POST['password'], $_POST['email'] );
        if ( $success ) {
            $this->isRegistrationSuccessful = true;
        }
    }

    private function isInviteAccepted() {
        return isset( $_POST['accept'] );
    }

    private function isInviteIgnored() {
        return isset( $_POST['ignore'] );
    }

    private function isUsernameMissing() {
        return empty( $_POST['username'] );
    }

    private function isPasswordMissing() {
        return empty( $_POST['password'] );
    }

    private function isEmailMalformed() {
        return !filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL );
    }

    private function isEmailTaken() {
        return $this->Cms->isEmailTaken( $_POST['email'] );
    }

    private function isPasswordMismatch() {
        return $_POST['password'] !== $_POST['passwordConfirmation'];
    }

    private function makeRedirectMessage( $url ) {
        $infoMessageText = 'Redirecting... <a href="' . $url . '">Click here</a> if you are not automatically redirected. <a href="' . $url . '">Onward!</a>';

        return $this->MarkupGenerator->makeInfoMessage( $infoMessageText );
    }
} 