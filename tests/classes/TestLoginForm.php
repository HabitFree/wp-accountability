<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestLoginForm extends HfTestCase {
    public function testLoginFormExtendsForm() {
        $this->assertInstanceOf('HfForm',$this->MockedLoginForm);
    }

    public function testAddsUsernameField() {
        $haystack = $this->MockedLoginForm->getHtml();
        $needle = '<p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p>';
        $this->assertContains($needle,$haystack);
    }

    public function testAddsPasswordField() {
        $haystack = $this->MockedLoginForm->getHtml();
        $needle = '<p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p>';
        $this->assertContains($needle, $haystack);
    }

    public function testAddsSubmitButton() {
        $haystack = $this->MockedLoginForm->getHtml();
        $needle = '<p><input type="submit" name="login" value="Log In" /></p>';
        $this->assertContains($needle, $haystack);
    }

    public function testPicksUpExistingUsername() {
        $_POST['username'] = 'duck';
        $haystack = $this->MockedLoginForm->getHtml();
        $needle = '<p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="duck" required /></label></p>';
        $this->assertContains($needle,$haystack);
    }
}