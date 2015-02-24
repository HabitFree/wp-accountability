<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestAuthenticateShortcode extends HfTestCase {
    public function testRegistrationShortcodeExists() {
        $this->assertTrue( shortcode_exists( 'hfAuthenticate' ) );
    }

    public function testAuthenticateShortcodeClassExists() {
        $this->assertTrue( class_exists( 'HfAuthenticateShortcode' ) );
    }

    public function testAuthenticateShortcodeClassImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( 'HfAuthenticateShortcode', 'Hf_iShortcode' ) );
    }

    public function testAuthenticateShortcodeGeneratesTabs() {
        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $isStringThere = ( strstr( $result, '[su_tabs active="1"]' ) != false );
        $this->assertTrue( $isStringThere );
    }

    public function testAuthenticateShortcodeGeneratesLogInTab() {
        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $this->assertTrue( strstr( $result, '[su_tab title="Log In"]' ) != false );
    }

    public function testAuthenticateShortcodeGeneratesRegisterTab() {
        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $this->assertTrue( strstr( $result, '[su_tab title="Register"]' ) != false );
    }

    public function testAuthenticateShortcodeIncludesLogInForm() {
        $this->setReturnValue($this->mockLoginForm,'getOutput','LoginForm');
        $auth = $this->makeExpressiveAuthenticateShortcode();
        $result = $auth->getOutput();
        $this->assertContains('LoginForm',$result);
    }

    public function testAuthenticateShortcodeIncludesRegistrationForm() {
        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $AssetLocator          = $this->factory->makeAssetLocator();

        $result = $AuthenticateShortcode->getOutput();
        $url    = $AssetLocator->getCurrentPageUrl();

        $formOpener = '<form action="' . $url . '" method="post">';
        $usernameChoiceMessage = '<p class="info"><strong>Important:</strong> '
                . 'HabitFree is a support community. For this reason, please '
                . 'choose a non-personally-identifiable username.</p>';
        $usernameField = '<p><label for="username"><span class="required">*'
                . '</span> Username: <input type="text" name="username" '
                . 'value="" required /></label></p>';
        
        
        $this->assertContains($formOpener, $result);
        $this->assertContains($usernameChoiceMessage, $result);
        $this->assertContains($usernameField, $result);
    }

    public function testAuthenticateShortcodeRemembersUsernameOnPost() {
        $_POST['login']    = '';
        $_POST['username'] = 'CharlieBrown';
        $_POST['password'] = '';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $html                  = $AuthenticateShortcode->getOutput();

        $this->assertContains( $_POST['username'], $html );
    }

    public function testAuthenticateShortcodeRemembersEmailOnPost() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['hfEmail']                = '';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';
        $_POST['hfEmail']                = 'charlie@peanuts.net';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $html                  = $AuthenticateShortcode->getOutput();

        $this->assertEquals( 1, substr_count( $html, $_POST['hfEmail'] ) );
    }

    public function testAuthenticateShortcodeChecksNewPasswordsMatch() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['hfEmail']                = '';
        $_POST['password']             = 'duck';
        $_POST['passwordConfirmation'] = 'goat';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please make sure your passwords match.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodePassesMatchingPasswords() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['hfEmail']                = '';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'horse';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please make sure your passwords match.</p>";

        $this->assertTrue( !$this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresUsernameEntry() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['hfEmail']                = '';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter your username.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresUsernameEntryAndChecksPasswords() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['hfEmail']                = '';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'chimpanzee';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $usernameNeedle        = "<p class='error'>Please enter your username.</p>";
        $passwordNeedle        = "<p class='error'>Please make sure your passwords match.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $usernameNeedle ) );
        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $passwordNeedle ) );
    }

    public function testAuthenticateShortcodeRequiresEmailAddressInput() {
        $_POST['register']             = '';
        $_POST['username']             = 'OldMcDonald';
        $_POST['hfEmail']                = '';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'chimpanzee';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter a valid email address.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresValidEmailAddress() {
        $_POST['register']             = '';
        $_POST['username']             = 'OldMcDonald';
        $_POST['hfEmail']                = 'jack.com';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'chimpanzee';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter a valid email address.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeAcceptsValidEmailAddress() {
        $_POST['register']             = '';
        $_POST['username']             = 'OldMcDonald';
        $_POST['hfEmail']                = 'me@my.com';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'chimpanzee';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter a valid email address.</p>";

        $this->assertTrue( !$this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresPasswordEntry() {
        $this->setEmptyRegistrationPost();

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter your password.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeSwitchesToRegisterTabForRegisteringUsers() {
        $this->setEmptyRegistrationPost();

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = 'active="2"';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodePlacesErrorsWithinRegistrationTab() {
        $this->setEmptyRegistrationPost();

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "[su_tab title=\"Register\"]<p class='error'>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeChecksIfEmailIsAvailable() {
        $_POST['register']             = '';
        $_POST['username']             = 'turtle';
        $_POST['hfEmail']                = 'taken@taken.com';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>That email is already taken. Did you mean to log in?</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresLogInUsername() {
        $this->setEmptyLoginPost();

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter your username.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDoesNotAttemptLogInWhenFormFailsToValidate() {
        $this->setLoginPost();
        $_POST['password'] = '';

        $this->expectNever( $this->mockCms, 'authenticateUser' );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->factory->makeMarkupGenerator(),
            $this->factory->makeAssetLocator(),
            $this->mockCms,
            $this->factory->makeUserManager(),
            $this->mockLoginForm,
            $this->mockRegistrationForm,
            $this->mockInviteResponseForm
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeAttemptsRegistration() {
        $this->setRegistrationPost();

        $this->expectOnce( $this->mockCms, 'createUser', array('Joe', 'bo', 'joe@wallysworld.com') );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->factory->makeMarkupGenerator(),
            $this->factory->makeAssetLocator(),
            $this->mockCms,
            $this->factory->makeUserManager(),
            $this->mockLoginForm,
            $this->mockRegistrationForm,
            $this->mockInviteResponseForm
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysRegistrationSuccessMessage() {
        $this->setRegistrationPost();

        $this->setReturnValue( $this->mockCms, 'createUser', true );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->factory->makeMarkupGenerator(),
            $this->factory->makeAssetLocator(),
            $this->mockCms,
            $this->factory->makeUserManager(),
            $this->mockLoginForm,
            $this->mockRegistrationForm,
            $this->mockInviteResponseForm
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = "<p class='success'>Welcome to HabitFree!</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRegistrationProcessInvite() {
        $this->setRegistrationPost();

        $_GET['n']                     = 555;

        $mockUser     = new stdClass();
        $mockUser->ID = 7;

        $this->setReturnValue( $this->mockCms, 'createUser', 7 );
        $this->setReturnValue( $this->mockCms, 'currentUser', $mockUser );
        $this->expectOnce( $this->mockUserManager, 'processInvite', array(7, 555) );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->factory->makeMarkupGenerator(),
            $this->factory->makeAssetLocator(),
            $this->mockCms,
            $this->mockUserManager,
            $this->mockLoginForm,
            $this->mockRegistrationForm,
            $this->mockInviteResponseForm
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysRegistrationErrorMessage() {
        $this->setRegistrationPost();

        $this->setReturnValue( $this->mockCms, 'isError', True );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->factory->makeMarkupGenerator(),
            $this->factory->makeAssetLocator(),
            $this->mockCms,
            $this->factory->makeUserManager(),
            $this->mockLoginForm,
            $this->mockRegistrationForm,
            $this->mockInviteResponseForm
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = "<p class='error'>We're very sorry, but something seems to have gone wrong with your registration.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDisplaysInvitationInfo() {
        $_GET['n'] = 555;

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class=\"info\">Looks like you're responding to an invitation. Feel free to either register or log into an existing account—either way we'll automatically set up accountability between you and the user who invited you.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeChecksUrlGetForActiveTab() {
        $_GET['n']   = 555;
        $_GET['tab'] = 2;

        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '[su_tabs active="2"]';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRegistrationAdvisesUserOnUsernameChoice() {
        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="info"><strong>Important:</strong> HabitFree is a support community. For this reason, please choose a non-personally-identifiable username.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testMakeAuthenticateShortcode() {
        $AuthenticateShortcode = $this->factory->makeAuthenticateShortcode();

        $this->assertTrue( is_a( $AuthenticateShortcode, 'HfAuthenticateShortcode' ) );
    }

    public function testAuthenticationShortcodeDoesntDisplayAuthenticiationFormWhenLoggedIn() {
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->expectNever( $this->mockMarkupGenerator, 'generateTabs' );

        $this->mockedAuthenticateShortcode->getOutput();
    }

    public function testAuthenticationShortcodeDoesntMentionRegisteringWhenUserLoggedInAndInvited() {
        $_GET['n'] = 555;
        $this->setLoggedInUser();

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $needle   = 'register';
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertFalse( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    private function setLoggedInUser() {
        $mockUser     = new stdClass();
        $mockUser->ID = 7;

        $this->setReturnValue( $this->mockCms, 'currentUser', $mockUser );
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
    }

    private function makeExpressiveAuthenticateShortcode() {
        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->factory->makeMarkupGenerator(),
            $this->mockAssetLocator,
            $this->mockCms,
            $this->mockUserManager,
            $this->mockLoginForm,
            $this->mockRegistrationForm,
            $this->mockInviteResponseForm
        );

        return $AuthenticateShortcode;
    }

    public function testAuthenticateShortcodeProcessesInviteWhenLoggedInUserAccepts() {
        $_GET['n']       = 555;
        $_POST['accept'] = '';
        $this->setLoggedInUser();

        $this->expectOnce( $this->mockUserManager, 'processInvite' );

        $this->mockedAuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDoesntDisplayInviteMessageOnAcceptance() {
        $_GET['n']       = 555;
        $_POST['accept'] = '';
        $this->setLoggedInUser();

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $needle   = '<p class="info">Looks like you\'re responding to an invite. What would you like to do?</p>';
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertDoesntContain( $needle, $haystack );
    }

    public function testAuthenticateShortcodeDoesntDisplayInviteMessageOnIgnore() {
        $_GET['n']       = 555;
        $_POST['ignore'] = '';
        $this->setLoggedInUser();

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $needle   = '<p class="info">Looks like you\'re responding to an invite. What would you like to do?</p>';
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertDoesntContain( $needle, $haystack );
    }

    public function testAuthenticateShortcodeDisplaysAcceptanceSuccessMessage() {
        $_GET['n']       = 555;
        $_POST['accept'] = '';
        $this->setLoggedInUser();

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $needle   = "<p class='success'>Invitation processed successfully.</p>";
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertContains( $needle, $haystack );
    }

    public function testAuthenticateShortcodeDisplaysIgnoreSuccessMessage() {
        $_GET['n']       = 555;
        $_POST['ignore'] = '';
        $this->setLoggedInUser();

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $needle   = "<p class='success'>Invitation ignored successfully.</p>";
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertContains( $needle, $haystack );
    }

    public function testAuthenticateShortcodeDoesntDisplayLoginFormOnSuccessfulLogin() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $this->setReturnValue( $this->mockUserManager, 'getCurrentUserLogin', 'Joe');

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $haystack = $AuthenticateShortcode->getOutput();
        $needle = '<form';

        $this->assertDoesntContain($needle, $haystack);
    }

    public function testRegistrationTestForErrors() {
        $this->setRegistrationPost();

        $this->expectAtLeastOnce($this->mockCms, 'isError');
        $this->mockedAuthenticateShortcode->getOutput();
    }

    private function setRegistrationPost()
    {
        $_POST['register'] = '';
        $_POST['username'] = 'Joe';
        $_POST['hfEmail'] = 'joe@wallysworld.com';
        $_POST['password'] = 'bo';
        $_POST['passwordConfirmation'] = 'bo';
    }

    public function testRegistrationChecksCreateUserResponseForErrors() {
        $this->setRegistrationPost();
        $this->setReturnValue($this->mockCms, 'createUser', 'duck');
        $this->expectAt($this->mockCms, 'isError', 2, array('duck'));
        $this->mockedAuthenticateShortcode->getOutput();
    }

    public function testRegistrationRespectsErrors() {
        $this->setRegistrationPost();
        $this->setReturnValue($this->mockCms, 'isError', True);
        $this->expectNever($this->mockMarkupGenerator, 'makeSuccessMessage');
        $this->mockedAuthenticateShortcode->getOutput();
    }

    public function testRegistrationRespectsSuccess() {
        $this->setRegistrationPost();
        $this->setReturnValue($this->mockCms, 'isError', False);
        $this->expectOnce($this->mockMarkupGenerator, 'makeSuccessMessage');
        $this->mockedAuthenticateShortcode->getOutput();
    }

    private function setLoginPost()
    {
        $_POST['login'] = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';
    }

    private function setEmptyLoginPost()
    {
        $_POST['login'] = '';
        $_POST['username'] = '';
        $_POST['password'] = '';
    }

    private function setEmptyRegistrationPost()
    {
        $_POST['register'] = '';
        $_POST['username'] = '';
        $_POST['hfEmail'] = '';
        $_POST['password'] = '';
        $_POST['passwordConfirmation'] = '';
    }

    public function testUsesLoginForm() {
        $this->expectOnce($this->mockLoginForm, 'getOutput');
        $this->mockedAuthenticateShortcode->getOutput();
    }

    public function testUsesRegistrationForm() {
        $this->expectOnce($this->mockRegistrationForm, 'getOutput');
        $this->mockedAuthenticateShortcode->getOutput();
    }

    public function testUsesRegistrationFormOutput() {
        $this->setReturnValue($this->mockRegistrationForm, 'getOutput', 'reg form');
        $shortcode = $this->makeExpressiveAuthenticateShortcode();
        $output = $shortcode->getOutput();
        $this->assertContains('reg form', $output);
    }

    public function testGetsInviteResponseFormHtml() {
        $_GET['n'] = '555';
        $this->setReturnValue($this->mockUserManager, 'isUserLoggedIn', True);
        $this->expectOnce($this->mockInviteResponseForm, 'getOutput');
        $this->mockedAuthenticateShortcode->getOutput();
    }

    public function testUsesInviteResponseFormOutput() {
        $_GET['n'] = '555';
        $this->setReturnValue($this->mockUserManager, 'isUserLoggedIn', True);
        $this->setReturnValue($this->mockInviteResponseForm, 'getOutput', 'formOutput');
        $result = $this->mockedAuthenticateShortcode->getOutput();
        $this->assertContains('formOutput', $result);
    }

    public function testGetsLoginFormOnSuccessfulRegistration() {
        $this->setRegistrationPost();
        $this->setReturnValue($this->mockCms, 'isError', False);
        $this->expectOnce($this->mockLoginForm, 'getOutput');
        $this->mockedAuthenticateShortcode->getOutput();
    }

    public function testOutputsLoginFormOnSuccessfulRegistration() {
        $this->setRegistrationPost();
        $this->setReturnValue($this->mockCms, 'isError', False);
        $this->setReturnValue($this->mockLoginForm, 'getOutput', 'loginForm');
        $result = $this->mockedAuthenticateShortcode->getOutput();
        $this->assertContains('loginForm', $result);
    }
}
