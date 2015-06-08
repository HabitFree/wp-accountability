<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestRegistrationForm extends HfTestCase {
    public function testExtendsForm() {
        $this->assertInstanceOf('HfForm', $this->mockedRegistrationForm);
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
        $haystack = $this->mockedRegistrationForm->getOutput();
        $this->assertContains($needle, $haystack);
    }

    public function testAddsEmailField() {
        $needle = '<p><label for="hfEmail"><span class="required">*</span> Email: <input type="text" name="hfEmail" value="" required /></label></p>';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }

    public function testPrefillsEmailFieldWithPostData() {
        $_POST['hfEmail'] = 'myEmail';
        $needle = '<p><label for="hfEmail"><span class="required">*</span> Email: <input type="text" name="hfEmail" value="myEmail" required /></label></p>';
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

    public function testMakesCheckbox() {
        $properties = array(
            'type' => 'checkbox',
            'name' => 'accountability',
            'value' => 'yes',
            'checked' => 'checked'
        );
        $this->expectOnce($this->mockMarkupGenerator,'input',array($properties));
        $this->mockedRegistrationForm->getOutput();
    }

    public function testWrapsCheckboxWithLabel() {
        $this->setReturnValue($this->mockMarkupGenerator,'input','checkbox');
        $content = 'checkbox Remind me to check in once in a while. <em>(Recommended)</em>';
        $properties = array();
        $this->expectOnce($this->mockMarkupGenerator,'label',array($content,$properties));
        $this->mockedRegistrationForm->getOutput();
    }

    public function testWrapsCheckboxWithParagraph() {
        $this->setReturnValue($this->mockMarkupGenerator,'label','labeledCheckbox');
        $this->expectOnce($this->mockMarkupGenerator,'paragraph',array('labeledCheckbox'));
        $this->mockedRegistrationForm->getOutput();
    }

    public function testOutputsCheckbox() {
        $this->setReturnValue($this->mockMarkupGenerator,'paragraph','checkbox');
        $needle = 'checkbox';
        $this->assertRegistrationFormOutputContainsNeedle($needle);
    }
} 