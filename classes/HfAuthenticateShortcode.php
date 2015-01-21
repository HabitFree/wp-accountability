<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class HfAuthenticateShortcode implements Hf_iShortcode {
    private $MarkupGenerator;
    private $AssetLocator;
    private $Cms;
    private $UserManager;

    private $LoginForm;

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
        Hf_iCms $ContentManagementSystem,
        Hf_iUserManager $UserManager,
        $LoginForm,
        $RegistrationForm,
        $InviteResponseForm
    ) {
        $this->MarkupGenerator   = $MarkupGenerator;
        $this->AssetLocator      = $AssetLocator;
        $this->Cms               = $ContentManagementSystem;
        $this->UserManager       = $UserManager;
        $this->LoginForm         = $LoginForm;
        $this->RegistrationForm  = $RegistrationForm;
        $this->InviteResponeForm = $InviteResponseForm;
    }

    public function getOutput() {
        $this->recallPostData();
        $this->validateForms();
        $this->processSubmissions();

        $this->makeAuthenticationForm();
        $this->makeInviteResponseForm();

        $this->displayLoginAndRegistrationSuccessMessages();

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
        $this->validateRegistrationForm();
    }

    private function processSubmissions() {
        $this->postProcessLogin();
        $this->processRegistrationRequest();
        $this->processInviteFormSubmission();
    }

    private function makeAuthenticationForm() {
        if ( !$this->UserManager->isUserLoggedIn() and !$this->isLoginSuccessful and !$this->isRegistrationSuccessful ) {
            $this->informInvitedUser();
            $activeTabNumber = $this->determineActiveTab();

            $tabbedForms = $this->MarkupGenerator->generateTabs( array(
                'Log In'   => $this->loginMessages . $this->LoginForm->getOutput(),
                'Register' => $this->registrationMessages . $this->RegistrationForm->getOutput()
            ), $activeTabNumber );

            $this->output .= $tabbedForms;
        }
    }

    private function makeInviteResponseForm() {
        if ( $this->isInvite() and $this->UserManager->isUserLoggedIn() and !$this->isInviteFormSubmitted() ) {
            $inviteResponseForm = $this->InviteResponeForm->getOutput();
            $this->output .= $inviteResponseForm;
        }
    }

    private function displayLoginAndRegistrationSuccessMessages() {
        if ( $this->isLoginSuccessful or $this->isRegistrationSuccessful ) {
            $this->output = $this->loginMessages . $this->registrationMessages . $this->output;
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

    private function postProcessLogin() {
        if ( $this->isLoggingIn() and $this->isLoginFormValid() ) {
            $this->determineLoginSuccess();

            if ( $this->isLoginSuccessful ) {
                $userId = $this->UserManager->getCurrentUserId();
                $this->processInvite($userId);
                $this->loginMessages .= $this->MarkupGenerator->makeSuccessMessage( 'Welcome back!' );
                $this->redirectUserHome();
            } else {
                $errorMessageText = 'That username and password combination is incorrect.';
                $this->loginMessages .= $this->MarkupGenerator->makeErrorMessage( $errorMessageText );
            }
        }
    }

    private function processRegistrationRequest() {
        if ( $this->isRegistering() and $this->isRegistrationFormValid() ) {
            $this->attemptRegistration();
        }
    }

    private function processInviteFormSubmission() {
        if ( $this->isInvite() and $this->isInviteAccepted() ) {
            $userId = $this->UserManager->getCurrentUserId();
            $this->processInvite($userId);
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

    public function attemptLogin() {
        if ($this->isLoggingIn()) {
            $success = $this->Cms->authenticateUser($_POST['username'], $_POST['password']);
            if ($success) {
                $this->refreshPage();
            }
        }
    }

    private function processInvite($userId) {
        if ( $this->isInvite() ) {
            $inviteeEmail = $this->Cms->getUserEmail( $userId );
            $this->UserManager->processInvite( $inviteeEmail, $_GET['n'] );
        }
    }

    private function redirectUserHome() {
        $url = $this->AssetLocator->getHomePageUrl();
        $this->output .= $this->makeRedirectMessage( $url );
        $this->output .= $this->MarkupGenerator->makeRedirectScript($url);
    }

    private function isRegistrationFormValid() {
        return empty( $this->registrationMessages );
    }

    private function attemptRegistration() {
        $userIdOrError = $this->Cms->createUser( $_POST['username'], $_POST['password'], $_POST['email'] );
        if ( !$this->Cms->isError($userIdOrError) ) {
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

    private function determineLoginSuccess() {
        $currentUserUsername = $this->UserManager->getCurrentUserLogin();
        $success = ( $currentUserUsername === $_POST['username'] );
        if ( $success ) {
            $this->isLoginSuccessful = true;
        }
    }

    private function enqueueRegistrationErrorMessage()
    {
        $errorMessageText = "We're very sorry, but something seems to have gone wrong with your registration.";
        $this->registrationMessages .= $this->MarkupGenerator->makeErrorMessage($errorMessageText);
    }

    private function enqueueRegistrationSuccessMessage()
    {
        $this->registrationMessages .= $this->MarkupGenerator->makeSuccessMessage('Welcome to HabitFree!');
    }

    private function refreshPage()
    {
        print $this->MarkupGenerator->makeRefreshScript();
    }
} 