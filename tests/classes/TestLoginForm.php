<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestLoginForm extends HfTestCase {
    public function testLoginFormExtendsForm() {
        $this->assertInstanceOf('HfForm',$this->MockedLoginForm);
    }

    public function testAddsUsernameField() {
        $haystack = $this->MockedLoginForm->getOutput();
        $needle = '<p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p>';
        $this->assertContains($needle,$haystack);
    }

    public function testAddsPasswordField() {
        $haystack = $this->MockedLoginForm->getOutput();
        $needle = '<p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p>';
        $this->assertContains($needle, $haystack);
    }

    public function testAddsSubmitButton() {
        $haystack = $this->MockedLoginForm->getOutput();
        $needle = '<p><input type="submit" name="login" value="Log In" /></p>';
        $this->assertContains($needle, $haystack);
    }

    public function testPicksUpExistingUsername() {
        $_POST['username'] = 'duck';
        $haystack = $this->MockedLoginForm->getOutput();
        $needle = '<p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="duck" required /></label></p>';
        $this->assertContains($needle,$haystack);
    }

    private function setEmptyLoginPost()
    {
        $_POST['login'] = '';
        $_POST['username'] = '';
        $_POST['password'] = '';
    }

    public function testRequiresLogInUsername() {
        $this->setEmptyLoginPost();

        $haystack              = $this->MockedLoginForm->getOutput();
        $needle                = "<p class='error'>Please enter your username.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testMakesMissingUsernameMessage() {
        $this->setEmptyLoginPost();
        $this->expectOnce($this->MockHtmlGenerator, 'makeErrorMessage', array('Please enter your username.'));
        $this->MockedLoginForm->getOutput();
    }
}