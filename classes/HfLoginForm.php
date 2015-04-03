<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfLoginForm extends HfForm {
    private $cms;

    public function __construct(
        $actionUrl,
        Hf_iMarkupGenerator $markupGenerator,
        Hf_iCms $cms,
        Hf_iAssetLocator $assetLocator,
        Hf_iUserManager $userManager
    ) {
        $this->initializeElements($actionUrl);

        $this->markupGenerator = $markupGenerator;
        $this->cms = $cms;
        $this->assetLocator = $assetLocator;
        $this->userManager = $userManager;
    }

    public function getOutput() {
        $this->makeLoginFailureError();
        $this->makeForm();
        $this->validateForm();
        $html = $this->getElementsAsString();
        return $html;
    }

    private function makeForm()
    {
        $this->addUsernameField();
        $this->addPasswordBox('password', 'Password', true);
        $this->addSubmitButton('login', 'Log In');
        $this->addNonceField();
    }

    private function addUsernameField()
    {
        $username = (isset($_POST['username']) ? $_POST['username'] : '');
        $this->addTextBox('username', 'Username', $username, true);
    }

    private function getElementsAsString()
    {
        $html = '';
        foreach ($this->elements as $element) {
            $html .= $element;
        }
        $html .= '</form>';
        return $html;
    }

    private function validateForm()
    {
        if ($this->isLoggingIn()) {
            $this->validateUsername();
            $this->validatePassword();
        }
    }

    private function validateUsername()
    {
        if (empty($_POST['username'])) {
            $error = $this->markupGenerator->errorMessage('Please enter your username.');
            $this->enqueueError($error);
        }
    }

    private function validatePassword()
    {
        if (empty($_POST['password'])) {
            $error = $this->markupGenerator->errorMessage('Please enter your password.');
            $this->enqueueError($error);
        }
    }

    private function makeLoginFailureError()
    {
        if ($this->isLoggingIn()) {
            $error = $this->markupGenerator->errorMessage('That username and password combination is incorrect.');
            $this->enqueueError($error);
        }
    }

    private function enqueueError($error)
    {
        array_unshift($this->elements, $error);
    }

    //=====

    public function attemptLogin()
    {
        if ($this->isOkToAttemptLogin()) {
            $userOrError = $this->cms->authenticateUser($_POST['username'], $_POST['password']);
            if ($this->isLoginSuccessful($userOrError)) {
                $this->processLoginSuccess($userOrError);
            }
        }
    }

    private function isLoggingIn()
    {
        return isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password']);
    }

    private function isLoginSuccessful($userOrError)
    {
        return !$this->cms->isError($userOrError);
    }

    private function isInvite()
    {
        return isset($_GET['n']);
    }

    private function redirectUser()
    {
        $homeUrl = $this->assetLocator->getHomePageUrl();
        print $this->markupGenerator->redirectScript($homeUrl);
    }

    private function processLoginSuccess($userOrError)
    {
        if ($this->isInvite()) {
            $this->userManager->processInvite($userOrError->ID, $_GET['n']);
        }
        $this->redirectUser();
    }

    private function addNonceField()
    {
        $this->elements[] = $this->cms->getNonceField('hfAttemptLogin');
    }

    private function isOkToAttemptLogin()
    {
        return $this->isLoggingIn() && $this->cms->isNonceValid($_POST['_wpnonce'], 'hfAttemptLogin');
    }
} 