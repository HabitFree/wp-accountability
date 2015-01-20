<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HfRegistrationForm extends HfForm {
    public function getOutput() {
        $this->makeForm();

        $html = '';
        foreach ($this->elements as $element) {
            $html .= $element;
        }
        $html .= '</form>';
        return $html;
    }

    private function makeForm()
    {
        $this->addUsernameChoiceMessage();
        $this->addUsernameField();
        $this->addEmailField();
        $this->addPasswordFields();
        $this->addSubmitButton( 'register', 'Register' );
    }

    public function addUsernameChoiceMessage()
    {
        $usernameChoiceMessage = '<strong>Important:</strong> HabitFree is a '
            . 'support community. For this reason, please choose a '
            . 'non-personally-identifiable username.';
        $this->addInfoMessage($usernameChoiceMessage);
    }

    private function addUsernameField()
    {
        $username = $this->getPostOrEmptyString('username');
        $this->addTextBox('username', 'Username', $username, true);
    }

    private function addEmailField()
    {
        $email = $this->getPostOrEmptyString('email');
        $this->addTextBox('email', 'Email', $email, true);
    }

    private function getPostOrEmptyString($index)
    {
        return (isset($_POST[$index]) ? $_POST[$index] : '');
    }

    private function addPasswordChoiceMessage()
    {
        $passwordChoiceMessage = '<strong>Important:</strong> Please '
            . 'choose a secure password. The most secure passwords are randomly generated. '
            . '<a href="https://lastpass.com/generate" target="_blank">You can get a randomly generated password here.</a>';
        $this->addInfoMessage($passwordChoiceMessage);
    }

    private function addPasswordFields()
    {
        $this->addPasswordChoiceMessage();
        $this->addPasswordBox('password', 'Password', true);
        $this->addPasswordBox('passwordConfirmation', 'Confirm Password', true);
    }
} 