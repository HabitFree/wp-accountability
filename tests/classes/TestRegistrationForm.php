<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestRegistrationForm extends HfTestCase {
    public function testExtendsForm() {
        $this->assertInstanceOf('HfForm', $this->MockedRegistrationForm);
    }

    public function testAddsUsernameChoiceMessage() {
        $needle = '<p class="info"><strong>Important:</strong> HabitFree is a '
            . 'support community. For this reason, please choose a '
            . 'non-personally-identifiable username.</p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }

    public function testAddsUsernameField() {
        $needle = '<p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }

    public function testPrefillsUsernameFieldWithPostData() {
        $_POST['username'] = 'jo';
        $needle = '<p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="jo" required /></label></p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }

    private function assertRegistrationFormOutputContainsNeedle($needle)
    {
        $haystack = $this->MockedRegistrationForm->getHtml();
        $this->assertContains($needle, $haystack);
    }

    public function testAddsEmailField() {
        $needle = '<p><label for="email"><span class="required">*</span> Email: <input type="text" name="email" value="" required /></label></p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }

    public function testPrefillsEmailFieldWithPostData() {
        $_POST['email'] = 'myEmail';
        $needle = '<p><label for="email"><span class="required">*</span> Email: <input type="text" name="email" value="myEmail" required /></label></p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }

    public function testAddsPasswordChoiceMessage() {
        $needle = '<p class="info"><strong>Important:</strong> Please '
            . 'choose a secure password. The most secure passwords are randomly generated. '
            . '<a href="https://lastpass.com/generate" target="_blank">You can get a randomly generated password here.</a></p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }

    public function testAddsPasswordBox() {
        $needle = '<p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }

    public function testAddsPasswordConfirmationBox() {
        $needle = '<p><label for="passwordConfirmation"><span class="required">*</span> Confirm Password: <input type="password" name="passwordConfirmation" required /></label></p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }

    public function testAddsSubmitButton() {
        $needle = '<p><input type="submit" name="register" value="Register" /></p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }
} 