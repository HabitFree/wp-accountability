<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfLoginForm extends HfForm {
    public function getOutput() {
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
        $this->validateUsername();
        $this->validatePassword();
    }

    private function validateUsername()
    {
        if (empty($_POST['username'])) {
            $error = $this->markupGenerator->makeErrorMessage('Please enter your username.');
            array_unshift($this->elements, $error);
        }
    }

    private function validatePassword()
    {
        if (empty($_POST['password'])) {
            $error = $this->markupGenerator->makeErrorMessage('Please enter your password.');
            array_unshift($this->elements, $error);
        }
    }
} 