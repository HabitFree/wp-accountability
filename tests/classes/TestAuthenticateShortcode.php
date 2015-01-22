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
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $isStringThere = ( strstr( $result, '[su_tabs active="1"]' ) != false );
        $this->assertTrue( $isStringThere );
    }

    public function testAuthenticateShortcodeGeneratesLogInTab() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $this->assertTrue( strstr( $result, '[su_tab title="Log In"]' ) != false );
    }

    public function testAuthenticateShortcodeGeneratesRegisterTab() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $result = $AuthenticateShortcode->getOutput();

        $this->assertTrue( strstr( $result, '[su_tab title="Register"]' ) != false );
    }

    public function testAuthenticateShortcodeIncludesLogInForm() {
        $this->setReturnValue($this->MockLoginForm,'getOutput','LoginForm');
        $auth = $this->makeExpressiveAuthenticateShortcode();
        $result = $auth->getOutput();
        $this->assertContains('LoginForm',$result);
    }

    public function testAuthenticateShortcodeIncludesRegistrationForm() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $AssetLocator          = $this->Factory->makeAssetLocator();

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

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $html                  = $AuthenticateShortcode->getOutput();

        $this->assertContains( $_POST['username'], $html );
    }

    public function testAuthenticateShortcodeRemembersEmailOnPost() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['email']                = '';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';
        $_POST['email']                = 'charlie@peanuts.net';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $html                  = $AuthenticateShortcode->getOutput();

        $this->assertEquals( 1, substr_count( $html, $_POST['email'] ) );
    }

    public function testAuthenticateShortcodeChecksNewPasswordsMatch() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['email']                = '';
        $_POST['password']             = 'duck';
        $_POST['passwordConfirmation'] = 'goat';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please make sure your passwords match.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodePassesMatchingPasswords() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['email']                = '';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'horse';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please make sure your passwords match.</p>";

        $this->assertTrue( !$this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresUsernameEntry() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['email']                = '';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter your username.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresUsernameEntryAndChecksPasswords() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['email']                = '';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'chimpanzee';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $usernameNeedle        = "<p class='error'>Please enter your username.</p>";
        $passwordNeedle        = "<p class='error'>Please make sure your passwords match.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $usernameNeedle ) );
        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $passwordNeedle ) );
    }

    public function testAuthenticateShortcodeRequiresEmailAddressInput() {
        $_POST['register']             = '';
        $_POST['username']             = 'OldMcDonald';
        $_POST['email']                = '';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'chimpanzee';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter a valid email address.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresValidEmailAddress() {
        $_POST['register']             = '';
        $_POST['username']             = 'OldMcDonald';
        $_POST['email']                = 'jack.com';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'chimpanzee';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter a valid email address.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeAcceptsValidEmailAddress() {
        $_POST['register']             = '';
        $_POST['username']             = 'OldMcDonald';
        $_POST['email']                = 'me@my.com';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'chimpanzee';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter a valid email address.</p>";

        $this->assertTrue( !$this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresPasswordEntry() {
        $this->setEmptyRegistrationPost();

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter your password.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeSwitchesToRegisterTabForRegisteringUsers() {
        $this->setEmptyRegistrationPost();

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = 'active="2"';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodePlacesErrorsWithinRegistrationTab() {
        $this->setEmptyRegistrationPost();

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "[su_tab title=\"Register\"]<p class='error'>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeChecksIfEmailIsAvailable() {
        $_POST['register']             = '';
        $_POST['username']             = 'turtle';
        $_POST['email']                = 'taken@taken.com';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>That email is already taken. Did you mean to log in?</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresLogInUsername() {
        $this->setEmptyLoginPost();

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>Please enter your username.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDoesNotAttemptLogInWhenFormFailsToValidate() {
        $this->setLoginPost();
        $_POST['password'] = '';

        $this->expectNever( $this->mockCms, 'authenticateUser' );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->mockCms,
            $this->Factory->makeUserManager(),
            $this->MockLoginForm,
            $this->MockRegistrationForm,
            $this->MockInviteResponseForm
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeAttemptsRegistration() {
        $this->setRegistrationPost();

        $this->expectOnce( $this->mockCms, 'createUser', array('Joe', 'bo', 'joe@wallysworld.com') );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->mockCms,
            $this->Factory->makeUserManager(),
            $this->MockLoginForm,
            $this->MockRegistrationForm,
            $this->MockInviteResponseForm
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysRegistrationSuccessMessage() {
        $this->setRegistrationPost();

        $this->setReturnValue( $this->mockCms, 'createUser', true );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->mockCms,
            $this->Factory->makeUserManager(),
            $this->MockLoginForm,
            $this->MockRegistrationForm,
            $this->MockInviteResponseForm
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = "<p class='success'>Welcome to HabitFree!</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeGeneratesRedirectScriptOnRegistration() {
        $this->setRegistrationPost();

        $this->setReturnValue( $this->mockCms, 'createUser', 5 );

        $this->expectOnce( $this->mockMarkupGenerator, 'makeRedirectScript' );
        $this->MockedAuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeRegistrationProcessInvite() {
        $this->setRegistrationPost();

        $_GET['n']                     = 555;

        $mockUser     = new stdClass();
        $mockUser->ID = 7;

        $this->setReturnValue( $this->mockCms, 'createUser', 7 );
        $this->setReturnValue( $this->mockCms, 'currentUser', $mockUser );
        $this->expectOnce( $this->MockUserManager, 'processInvite', array(7, 555) );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->mockCms,
            $this->MockUserManager,
            $this->MockLoginForm,
            $this->MockRegistrationForm,
            $this->MockInviteResponseForm
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysRegistrationErrorMessage() {
        $this->setRegistrationPost();

        $this->setReturnValue( $this->mockCms, 'isError', True );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->mockCms,
            $this->Factory->makeUserManager(),
            $this->MockLoginForm,
            $this->MockRegistrationForm,
            $this->MockInviteResponseForm
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = "<p class='error'>We're very sorry, but something seems to have gone wrong with your registration.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDisplaysInvitationInfo() {
        $_GET['n'] = 555;

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class=\"info\">Looks like you're responding to an invitation. Feel free to either register or log into an existing account—either way we'll automatically set up accountability between you and the user who invited you.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeChecksUrlGetForActiveTab() {
        $_GET['n']   = 555;
        $_GET['tab'] = 2;

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '[su_tabs active="2"]';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRegistrationAdvisesUserOnUsernameChoice() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="info"><strong>Important:</strong> HabitFree is a support community. For this reason, please choose a non-personally-identifiable username.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testMakeAuthenticateShortcode() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $this->assertTrue( is_a( $AuthenticateShortcode, 'HfAuthenticateShortcode' ) );
    }

    public function testAuthenticationShortcodeDoesntDisplayAuthenticiationFormWhenLoggedIn() {
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
        $this->expectNever( $this->mockMarkupGenerator, 'generateTabs' );

        $this->MockedAuthenticateShortcode->getOutput();
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
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
    }

    private function makeExpressiveAuthenticateShortcode() {
        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->mockAssetLocator,
            $this->mockCms,
            $this->MockUserManager,
            $this->MockLoginForm,
            $this->MockRegistrationForm,
            $this->MockInviteResponseForm
        );

        return $AuthenticateShortcode;
    }

    public function testAuthenticateShortcodeProcessesInviteWhenLoggedInUserAccepts() {
        $_GET['n']       = 555;
        $_POST['accept'] = '';
        $this->setLoggedInUser();

        $this->expectOnce( $this->MockUserManager, 'processInvite' );

        $this->MockedAuthenticateShortcode->getOutput();
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

        $this->setReturnValue( $this->MockUserManager, 'getCurrentUserLogin', 'Joe');

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $haystack = $AuthenticateShortcode->getOutput();
        $needle = '<form';

        $this->assertDoesntContain($needle, $haystack);
    }

    public function testAuthenticateShortcodeDoesntRedirectOnFailedLogin() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $this->setReturnValue( $this->mockCms, 'authenticateUser', false);

        $this->expectNever($this->mockMarkupGenerator, 'makeRedirectScript');

        $this->MockedAuthenticateShortcode->attemptLogin();
    }

    public function testRegistrationTestForErrors() {
        $this->setRegistrationPost();

        $this->expectOnce($this->mockCms, 'isError');
        $this->MockedAuthenticateShortcode->getOutput();
    }

    private function setRegistrationPost()
    {
        $_POST['register'] = '';
        $_POST['username'] = 'Joe';
        $_POST['email'] = 'joe@wallysworld.com';
        $_POST['password'] = 'bo';
        $_POST['passwordConfirmation'] = 'bo';
    }

    public function testRegistrationChecksCreateUserResponseForErrors() {
        $this->setRegistrationPost();
        $this->setReturnValue($this->mockCms, 'createUser', 'duck');
        $this->expectOnce($this->mockCms, 'isError', array('duck'));
        $this->MockedAuthenticateShortcode->getOutput();
    }

    public function testRegistrationRespectsErrors() {
        $this->setRegistrationPost();
        $this->setReturnValue($this->mockCms, 'isError', True);
        $this->expectNever($this->mockMarkupGenerator, 'makeSuccessMessage');
        $this->MockedAuthenticateShortcode->getOutput();
    }

    public function testRegistrationRespectsSuccess() {
        $this->setRegistrationPost();
        $this->setReturnValue($this->mockCms, 'isError', False);
        $this->expectOnce($this->mockMarkupGenerator, 'makeSuccessMessage');
        $this->MockedAuthenticateShortcode->getOutput();
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
        $_POST['email'] = '';
        $_POST['password'] = '';
        $_POST['passwordConfirmation'] = '';
    }

    public function testAttemptLoginCreatesRefreshScript() {
        $this->setLoginPost();
        $this->setReturnValue($this->mockCms, 'authenticateUser', True);
        $this->expectOnce($this->mockMarkupGenerator, 'makeRefreshScript');
        $this->MockedAuthenticateShortcode->attemptLogin();
    }

    public function testUsesLoginForm() {
        $this->expectOnce($this->MockLoginForm, 'getOutput');
        $this->MockedAuthenticateShortcode->getOutput();
    }

    public function testUsesRegistrationForm() {
        $this->expectOnce($this->MockRegistrationForm, 'getOutput');
        $this->MockedAuthenticateShortcode->getOutput();
    }

    public function testUsesRegistrationFormOutput() {
        $this->setReturnValue($this->MockRegistrationForm, 'getOutput', 'reg form');
        $shortcode = $this->makeExpressiveAuthenticateShortcode();
        $output = $shortcode->getOutput();
        $this->assertContains('reg form', $output);
    }

    public function testGetsInviteResponseFormHtml() {
        $_GET['n'] = '555';
        $this->setReturnValue($this->MockUserManager, 'isUserLoggedIn', True);
        $this->expectOnce($this->MockInviteResponseForm, 'getOutput');
        $this->MockedAuthenticateShortcode->getOutput();
    }

    public function testUsesInviteResponseFormOutput() {
        $_GET['n'] = '555';
        $this->setReturnValue($this->MockUserManager, 'isUserLoggedIn', True);
        $this->setReturnValue($this->MockInviteResponseForm, 'getOutput', 'formOutput');
        $result = $this->MockedAuthenticateShortcode->getOutput();
        $this->assertContains('formOutput', $result);
    }

    public function testAttemptLoginChecksIfError() {
        $this->setLoginPost();
        $this->setReturnValue($this->mockCms, 'authenticateUser','result');
        $this->expectOnce($this->mockCms, 'isError', array('result'));
        $this->MockedAuthenticateShortcode->attemptLogin();
    }
}
