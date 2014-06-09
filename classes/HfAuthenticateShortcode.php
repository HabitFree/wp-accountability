<?php

class HfAuthenticateShortcode implements Hf_iShortcode {
    private $DisplayCodeGenerator;

    function __construct( Hf_iDisplayCodeGenerator $DisplayCodeGenerator ) {
        $this->DisplayCodeGenerator = $DisplayCodeGenerator;
    }

    public function getOutput() {
        $tabs = array(
            'Log In'   => $this->generateLogInForm(),
            'Register' => ''
        );

        return $this->DisplayCodeGenerator->generateTabs( $tabs, 1 );
    }

    private function generateLogInForm() {
        $LogInForm = new HfGenericForm( 'anothertest.com' );

        $LogInForm->addTextBox( 'username', 'Username', '', true );
        $LogInForm->addPasswordBox( 'password', 'Password', true );
        $LogInForm->addSubmitButton( 'login', 'Log In' );

        $logInHtml = $LogInForm->getHtml();

        return $logInHtml;
    }
} 