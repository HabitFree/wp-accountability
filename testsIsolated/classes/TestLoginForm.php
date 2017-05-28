<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestLoginForm extends HfTestCase {
    public function testLoginFormExtendsForm() {
        $this->assertInstanceOf('HfForm',$this->mockedLoginForm);
    }

    public function testAddsUsernameField() {
        $haystack = $this->mockedLoginForm->getOutput();
        $needle = '<p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p>';
        $this->assertContains($needle,$haystack);
    }

    public function testAddsPasswordField() {
        $haystack = $this->mockedLoginForm->getOutput();
        $needle = '<p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p>';
        $this->assertContains($needle, $haystack);
    }

    public function testAddsSubmitButton() {
        $haystack = $this->mockedLoginForm->getOutput();
        $needle = '<p><input type="submit" name="login" value="Log In" /></p>';
        $this->assertContains($needle, $haystack);
    }

    public function testPicksUpExistingUsername() {
        $_POST['username'] = 'duck';
        $haystack = $this->mockedLoginForm->getOutput();
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

        $this->setReturnValue($this->mockMarkupGenerator,'errorMessage','errorMessage');
        $haystack              = $this->mockedLoginForm->getOutput();

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, 'errorMessage' ) );
    }

    public function testMakesMissingUsernameMessage() {
        $this->setEmptyLoginPost();
        $this->expectAt($this->mockMarkupGenerator, 'errorMessage', 1, array('Please enter your username.'));
        $this->mockedLoginForm->getOutput();
    }

    public function testMakesMissingPasswordMessage() {
        $this->setEmptyLoginPost();
        $this->expectAt($this->mockMarkupGenerator, 'errorMessage', 2, array('Please enter your password.'));
        $this->mockedLoginForm->getOutput();
    }

    public function testRequiresPassword() {
        $this->setEmptyLoginPost();

        $this->setReturnValues($this->mockMarkupGenerator,'errorMessage',array('usernameError','passError'));
        $haystack              = $this->mockedLoginForm->getOutput();

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, 'passError' ) );
    }

    public function testAttemptsLogin() {
        $this->setupValidLogin();
        $this->expectOnce($this->mockCms, 'authenticateUser', array('user','pass'));
        $this->mockedLoginForm->attemptLogin();
    }

    public function testMakesRedirectScriptOnSuccessfulLogin() {
        $this->setupValidLogin();
        $this->setReturnValue($this->mockCms, 'authenticateUser', true);
        $this->setReturnValue($this->mockAssetLocator, 'getHomePageUrl', 'currentUrl');
        $this->expectOnce($this->mockMarkupGenerator, 'redirectScript', array('currentUrl'));
        $this->mockedLoginForm->attemptLogin();
    }

    private function setupValidLogin()
    {
        $this->setValidLoginPost();
        $this->setReturnValue($this->mockCms,'isNonceValid',true);
    }

    public function testMakesLoginFailureError() {
        $this->setupValidLogin();
        $this->expectOnce($this->mockMarkupGenerator,'errorMessage',array('That username and password combination is incorrect.'));
        $this->mockedLoginForm->getOutput();
    }

    public function testDoesntMakeLoginErrorWhenNoPost() {
        $this->expectNever($this->mockMarkupGenerator,'errorMessage');
        $this->mockedLoginForm->getOutput();
    }

    public function testOutputsLoginErrorMessage() {
        $this->setupValidLogin();
        $this->setReturnValue($this->mockMarkupGenerator, 'errorMessage','loginFailure');
        $haystack = $this->mockedLoginForm->getOutput();
        $needle = 'loginFailure';
        $this->assertContains($needle,$haystack);
    }

    public function testAttemptLoginChecksIfError() {
        $this->setupValidLogin();
        $this->setReturnValue($this->mockCms, 'authenticateUser','result');
        $this->expectOnce($this->mockCms, 'isError', array('result'));
        $this->mockedLoginForm->attemptLogin();
    }

    public function testProcessesInvite() {
        $this->setupValidLogin();
        $_GET['n'] = '555';
        $mockUser = new stdClass();
        $mockUser->ID = 7;
        $this->setReturnValue($this->mockCms, 'isError', false);
        $this->setReturnValue($this->mockCms, 'authenticateUser',$mockUser);
        $this->expectOnce($this->mockUserManager,'processInvite',array(7,'555'));
        $this->mockedLoginForm->attemptLogin();
    }

    public function testDoesntValidateUnsubmittedForm() {
        $this->expectNever($this->mockMarkupGenerator, 'errorMessage', array('Please enter your username.'));
        $this->mockedLoginForm->getOutput();
    }

    public function testGetsNonceField() {
        $this->expectOnce($this->mockCms,'getNonceField',array('hfAttemptLogin'));
        $this->mockedLoginForm->getOutput();
    }

    public function testOutputsNonceField() {
        $this->setReturnValue($this->mockCms,'getNonceField','nonceField');
        $haystack = $this->mockedLoginForm->getOutput();
        $this->assertContains('nonceField',$haystack);
    }

    public function testChecksNonce() {
        $this->setupValidLogin();
        $this->expectOnce($this->mockCms,'isNonceValid',array(0101,'hfAttemptLogin'));
        $this->mockedLoginForm->attemptLogin();
    }

    public function testDoesntLoginIfNonceInvalid() {
        $this->setValidLoginPost();
        $this->setReturnValue($this->mockCms,'isNonceValid',false);
        $this->expectNever($this->mockCms,'authenticateUser');
        $this->mockedLoginForm->attemptLogin();
    }

    private function setValidLoginPost()
    {
        $_POST['login'] = '';
        $_POST['username'] = 'user';
        $_POST['password'] = 'pass';
        $_POST['_wpnonce'] = 0101;
    }
}