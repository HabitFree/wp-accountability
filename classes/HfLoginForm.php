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
        $this->elements = array();
        $this->elements[] = '<form action="'.$actionUrl.'" method="post">';

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
            $error = $this->markupGenerator->makeErrorMessage('Please enter your username.');
            $this->enqueError($error);
        }
    }

    private function validatePassword()
    {
        if (empty($_POST['password'])) {
            $error = $this->markupGenerator->makeErrorMessage('Please enter your password.');
            $this->enqueError($error);
        }
    }

    private function makeLoginFailureError()
    {
        if ($this->isLoggingIn()) {
            $error = $this->markupGenerator->makeErrorMessage('That username and password combination is incorrect.');
            $this->enqueError($error);
        }
    }

    private function enqueError($error)
    {
        array_unshift($this->elements, $error);
    }

    //=====

    public function attemptLogin()
    {
        if ($this->isLoggingIn()) {
            $userOrError = $this->cms->authenticateUser($_POST['username'], $_POST['password']);

            if ($this->isLoginSuccessful($userOrError)) {
                if ($this->isInvite()) {
                    $this->userManager->processInvite($userOrError->ID, $_GET['n']);
                }
                $this->redirectUser();
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
        print $this->markupGenerator->makeRedirectScript($homeUrl);
    }
} 