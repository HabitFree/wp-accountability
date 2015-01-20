<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfLoginForm extends HfForm {
    public function getOutput() {
        $this->makeForm();
        if (empty($_POST['username'])) {
            array_unshift($this->elements, "<p class='error'>Please enter your username.</p>");
        }
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
} 