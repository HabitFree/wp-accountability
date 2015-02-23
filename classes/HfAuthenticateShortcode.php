<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class HfAuthenticateShortcode implements Hf_iShortcode {
    private $markupGenerator;
    private $assetLocator;
    private $cms;
    private $userManager;

    private $loginForm;

    private $registrationMessages;
    private $output;

    private $isLoginSuccessful = false;
    private $isRegistrationSuccessful = false;

    function __construct(
        Hf_iMarkupGenerator $markupGenerator,
        Hf_iAssetLocator $assetLocator,
        Hf_iCms $contentManagementSystem,
        Hf_iUserManager $userManager,
        $loginForm,
        $registrationForm,
        $inviteResponseForm
    ) {
        $this->markupGenerator   = $markupGenerator;
        $this->assetLocator      = $assetLocator;
        $this->cms               = $contentManagementSystem;
        $this->userManager       = $userManager;
        $this->loginForm         = $loginForm;
        $this->RegistrationForm  = $registrationForm;
        $this->InviteResponeForm = $inviteResponseForm;
    }

    public function getOutput() {
        $this->validateForms();
        $this->processSubmissions();

        $this->makeAuthenticationForm();
        $this->makeInviteResponseForm();

        $this->displayLoginAndRegistrationSuccessMessages();

        return $this->output;
    }

    private function validateForms() {
        $this->validateRegistrationForm();
    }

    private function processSubmissions() {
        $this->processRegistrationRequest();
        $this->processInviteFormSubmission();
    }

    private function makeAuthenticationForm() {
        if ( !$this->userManager->isUserLoggedIn() and !$this->isLoginSuccessful and !$this->isRegistrationSuccessful ) {
            $this->informInvitedUser();
            $activeTabNumber = $this->determineActiveTab();

            $tabbedForms = $this->markupGenerator->generateTabs( array(
                'Log In'   => $this->loginForm->getOutput(),
                'Register' => $this->registrationMessages . $this->RegistrationForm->getOutput()
            ), $activeTabNumber );

            $this->output .= $tabbedForms;
        }
    }

    private function makeInviteResponseForm() {
        if ( $this->isInvite() and $this->userManager->isUserLoggedIn() and !$this->isInviteFormSubmitted() ) {
            $inviteResponseForm = $this->InviteResponeForm->getOutput();
            $this->output .= $inviteResponseForm;
        }
    }

    private function displayLoginAndRegistrationSuccessMessages() {
        if ( $this->isLoginSuccessful or $this->isRegistrationSuccessful ) {
            $this->output = $this->registrationMessages . $this->output;
        }
    }

    private function isRegistering() {
        return isset( $_POST['register'] );
    }

    private function isLoggingIn() {
        return isset( $_POST['login'] );
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

    private function processRegistrationRequest() {
        if ( $this->isRegistering() and $this->isRegistrationFormValid() ) {
            $this->attemptRegistration();
        }
    }

    private function processInviteFormSubmission() {
        if ( $this->isInvite() and $this->isInviteAccepted() ) {
            $userId = $this->userManager->getCurrentUserId();
            $this->processInvite($userId);
            $this->output .= $this->markupGenerator->makeSuccessMessage( 'Invitation processed successfully.' );
        } elseif ( $this->isInviteIgnored() ) {
            $this->output .= $this->markupGenerator->makeSuccessMessage( 'Invitation ignored successfully.' );
        }
    }

    private function informInvitedUser() {
        if ( $this->isInvite() ) {
            $infoMessageText = "Looks like you're responding to an invitation. Feel free to either register or log into an existing account—either way we'll automatically set up accountability between you and the user who invited you.";
            $this->output .= $this->markupGenerator->makeInfoMessage( $infoMessageText );
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

    private function isInvite() {
        return !empty( $_GET['n'] );
    }

    private function isInviteFormSubmitted() {
        return $this->isInviteAccepted() or $this->isInviteIgnored();
    }

    private function missingUsernameError() {
        $errorMessage = $this->markupGenerator->makeErrorMessage( 'Please enter your username.' );

        return ( $this->isUsernameMissing() ) ? $errorMessage : '';
    }

    private function missingPasswordError() {
        $errorMessage = $this->markupGenerator->makeErrorMessage( 'Please enter your password.' );

        return ( $this->isPasswordMissing() ) ? $errorMessage : '';
    }

    private function invalidEmailError() {
        $errorMessage = $this->markupGenerator->makeErrorMessage( 'Please enter a valid email address.' );

        return ( $this->isEmailMalformed() ) ? $errorMessage : '';
    }

    private function emailTakenError() {
        $errorMessage = $this->markupGenerator->makeErrorMessage( 'That email is already taken. Did you mean to log in?' );

        return ( $this->isEmailTaken() ) ? $errorMessage : '';
    }

    private function passwordMatchError() {
        $errorMessage = $this->markupGenerator->makeErrorMessage( 'Please make sure your passwords match.' );

        return ( $this->isPasswordMismatch() ) ? $errorMessage : '';
    }

    public function attemptLogin() {
        if ($this->isLoggingIn()) {
            $userOrError = $this->cms->authenticateUser($_POST['username'], $_POST['password']);
            if (!$this->cms->isError($userOrError)) {
                $this->refreshPage();
            }
        }
    }

    private function processInvite($userId) {
        if ( $this->isInvite() ) {
            $this->userManager->processInvite( $userId, $_GET['n'] );
        }
    }

    private function redirectUserHome() {
        $url = $this->assetLocator->getHomePageUrl();
        $this->output .= $this->makeRedirectMessage( $url );
        $this->output .= $this->markupGenerator->makeRedirectScript($url);
    }

    private function isRegistrationFormValid() {
        return empty( $this->registrationMessages );
    }

    private function attemptRegistration() {
        $userIdOrError = $this->cms->createUser( $_POST['username'], $_POST['password'], $_POST['hfEmail'] );
        if ( !$this->cms->isError($userIdOrError) ) {
            $this->isRegistrationSuccessful = true;
            $this->attemptLogin();
            $this->processInvite($userIdOrError);
            $this->enqueueRegistrationSuccessMessage();
            $this->redirectUserHome();
        } else {
            $this->enqueueRegistrationErrorMessage();
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
        return !filter_var( $_POST['hfEmail'], FILTER_VALIDATE_EMAIL );
    }

    private function isEmailTaken() {
        return $this->cms->isEmailTaken( $_POST['hfEmail'] );
    }

    private function isPasswordMismatch() {
        return $_POST['password'] !== $_POST['passwordConfirmation'];
    }

    private function makeRedirectMessage( $url ) {
        $infoMessageText = 'Redirecting... <a href="' . $url . '">Click here</a> if you are not automatically redirected. <a href="' . $url . '">Onward!</a>';

        return $this->markupGenerator->makeInfoMessage( $infoMessageText );
    }

    private function enqueueRegistrationErrorMessage()
    {
        $errorMessageText = "We're very sorry, but something seems to have gone wrong with your registration.";
        $this->registrationMessages .= $this->markupGenerator->makeErrorMessage($errorMessageText);
    }

    private function enqueueRegistrationSuccessMessage()
    {
        $this->registrationMessages .= $this->markupGenerator->makeSuccessMessage('Welcome to HabitFree!');
    }

    private function refreshPage()
    {
        print $this->markupGenerator->makeRefreshScript();
    }
} 