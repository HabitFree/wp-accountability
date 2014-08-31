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
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $AssetLocator          = $this->Factory->makeAssetLocator();

        $result = $AuthenticateShortcode->getOutput();
        $url    = $AssetLocator->getCurrentPageUrl();

        $logInHtml = '<form action="' . $url . '" method="post"><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><input type="submit" name="login" value="Log In" /></p></form>';

        $this->assertTrue( strstr( $result, $logInHtml ) != false );
    }

    public function testAuthenticateShortcodeIncludesRegistrationForm() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $AssetLocator          = $this->Factory->makeAssetLocator();

        $result = $AuthenticateShortcode->getOutput();
        $url    = $AssetLocator->getCurrentPageUrl();

        $expectedHtml = '<form action="' . $url . '" method="post"><p class="info"><strong>Important:</strong> HabitFree is a support community. For this reason, please choose a non-personally-identifiable username.</p><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="email"><span class="required">*</span> Email: <input type="text" name="email" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><label for="passwordConfirmation"><span class="required">*</span> Confirm Password: <input type="password" name="passwordConfirmation" required /></label></p><p><input type="submit" name="register" value="Register" /></p></form>';

        $this->assertTrue( $this->haystackContainsNeedle( $result, $expectedHtml ) );
    }

    public function testAuthenticateShortcodeUsesCurrentUrl() {
        $currentUrl = 'mysite.com';

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->MockAssetLocator,
            $this->Factory->makeCms(),
            $this->Factory->makeUserManager()
        );

        $this->setReturnValue( $this->MockAssetLocator, 'getCurrentPageUrl', $currentUrl );
        $result = $AuthenticateShortcode->getOutput();

        $this->assertEquals( 2, substr_count( $result, $currentUrl ) );
    }

    public function testAuthenticateShortcodeRemembersUsernameOnPost() {
        $_POST['login']    = '';
        $_POST['username'] = 'CharlieBrown';
        $_POST['password'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $html                  = $AuthenticateShortcode->getOutput();

        $this->assertEquals( 2, substr_count( $html, $_POST['username'] ) );
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
        $needle                = '<p class="error">Please make sure your passwords match.</p>';

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
        $needle                = '<p class="error">Please make sure your passwords match.</p>';

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
        $needle                = '<p class="error">Please enter your username.</p>';

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
        $usernameNeedle        = '<p class="error">Please enter your username.</p>';
        $passwordNeedle        = '<p class="error">Please make sure your passwords match.</p>';

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
        $needle                = '<p class="error">Please enter a valid email address.</p>';

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
        $needle                = '<p class="error">Please enter a valid email address.</p>';

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
        $needle                = '<p class="error">Please enter a valid email address.</p>';

        $this->assertTrue( !$this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresPasswordEntry() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['email']                = '';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="error">Please enter your password.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeSwitchesToRegisterTabForRegisteringUsers() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['email']                = '';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = 'active="2"';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodePlacesErrorsWithinRegistrationTab() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['email']                = '';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '[su_tab title="Register"]<p class="error">';

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
        $needle                = '<p class="error">That email is already taken. Did you mean to log in?</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresLogInUsername() {
        $_POST['login']    = '';
        $_POST['username'] = '';
        $_POST['password'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="error">Please enter your username.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresPassword() {
        $_POST['login']    = '';
        $_POST['username'] = '';
        $_POST['password'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="error">Please enter your password.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeAttemptsLogIn() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $this->expectOnce( $this->MockCms, 'authenticateUser', array('Joe', 'bo') );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysLogInFailureError() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $this->setReturnValue( $this->MockCms, 'authenticateUser', false );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = '<p class="error">That username and password combination is incorrect.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDisplaysLogInFailureErrorWithinTab() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $this->setReturnValue( $this->MockCms, 'authenticateUser', false );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = '[su_tab title="Log In"]<p class="error">That username and password combination is incorrect.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDisplaysLogInSuccessMessage() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $this->setReturnValue( $this->MockCms, 'authenticateUser', true );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = '<p class="success">Welcome back!</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeGeneratesRedirectScript() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $AssetLocator = $this->Factory->makeAssetLocator();
        $this->setReturnValue( $this->MockCms, 'authenticateUser', true );

        $homeUrl = $AssetLocator->getHomePageUrl();

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = '<script>setTimeout(function(){window.location.replace("' . $homeUrl . '")},5000);</script>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDoesNotAttemptLogInWhenFormFailsToValidate() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = '';

        $this->expectNever( $this->MockCms, 'authenticateUser' );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeAttemptsRegistration() {
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';

        $this->expectOnce( $this->MockCms, 'createUser', array('Joe', 'bo', 'joe@wallysworld.com') );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysRegistrationSuccessMessage() {
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';

        $this->setReturnValue( $this->MockCms, 'createUser', true );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = '<p class="success">Welcome to HabitFree!</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeGeneratesRedirectScriptOnRegistration() {
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';

        $this->setReturnValue( $this->MockCms, 'createUser', true );

        $AssetLocator = $this->Factory->makeAssetLocator();
        $homeUrl      = $AssetLocator->getHomePageUrl();

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = '<script>setTimeout(function(){window.location.replace("' . $homeUrl . '")},5000);</script>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRegistrationProcessInvite() {
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';
        $_GET['n']                     = 555;

        $mockUser     = new stdClass();
        $mockUser->ID = 7;

        $this->setReturnValue( $this->MockCms, 'createUser', true );
        $this->setReturnValue( $this->MockCms, 'currentUser', $mockUser );
        $this->setReturnValue( $this->MockCms, 'getUserEmail', 'joe@wallysworld.com' );
        $this->expectOnce( $this->MockUserManager, 'processInvite', array('joe@wallysworld.com', 555) );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->MockUserManager
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeLoginProcessInvite() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';
        $_GET['n']         = 555;

        $mockUser     = new stdClass();
        $mockUser->ID = 7;

        $this->setReturnValue( $this->MockCms, 'authenticateUser', true );
        $this->setReturnValue( $this->MockCms, 'currentUser', $mockUser );
        $this->setReturnValue( $this->MockCms, 'getUserEmail', 'joe@wallysworld.com' );
        $this->expectOnce( $this->MockUserManager, 'processInvite', array('joe@wallysworld.com', 555) );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->MockUserManager
        );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysRegistrationErrorMessage() {
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';

        $this->setReturnValue( $this->MockCms, 'createUser', false );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle   = "<p class=\"error\">We're very sorry, but something seems to have gone wrong with your registration.</p>";

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

    public function testAuthenticateShortcodeLoginDisplaysRedirectMessage() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $this->setReturnValue( $this->MockCms, 'authenticateUser', true );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $haystack = $AuthenticateShortcode->getOutput();

        $needle = '<p class="info">Redirecting...';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testMakeAuthenticateShortcode() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();

        $this->assertTrue( is_a( $AuthenticateShortcode, 'HfAuthenticateShortcode' ) );
    }

    public function testAuthenticationShortcodeDoesntDisplayAuthenticiationFormWhenLoggedIn() {
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
        $this->expectNever( $this->MockMarkupGenerator, 'generateTabs' );

        $this->AuthenticateShortcodeWithMockedDependencies->getOutput();
    }

    public function testAuthenticationShortcodeCreatesWhenUserLoggedInAndInvited() {
        $_GET['n'] = 555;
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue( $this->MockAssetLocator, 'getCurrentPageUrl', 'here.there' );

        $needle   = '<form action="here.there" method="post">';
        $haystack = $this->AuthenticateShortcodeWithMockedDependencies->getOutput();

        $this->assertContains( $needle, $haystack );
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

        $this->setReturnValue( $this->MockCms, 'currentUser', $mockUser );
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
    }

    private function makeExpressiveAuthenticateShortcode() {
        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->MockAssetLocator,
            $this->MockCms,
            $this->MockUserManager
        );

        return $AuthenticateShortcode;
    }

    public function testAuthenticateShortcodeHasAcceptButton() {
        $_GET['n'] = 555;
        $this->setLoggedInUser();

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $needle   = '<input type="submit" name="accept" value="Accept invitation" />';
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertContains( $needle, $haystack );
    }

    public function testAuthenticateShortcodeHasIgnoreButton() {
        $_GET['n'] = 555;
        $this->setLoggedInUser();

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $needle   = '<input type="submit" name="ignore" value="Ignore invitation" />';
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertContains( $needle, $haystack );
    }

    public function testAuthenticateShortcodeDisplaysInviteMessageWhenUserLoggedIn() {
        $_GET['n'] = 555;
        $this->setLoggedInUser();

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $needle   = '<p class="info">Looks like you\'re responding to an invite. What would you like to do?</p>';
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertContains( $needle, $haystack );
    }

    public function testAuthenticateShortcodeProcessesInviteWhenLoggedInUserAccepts() {
        $_GET['n']       = 555;
        $_POST['accept'] = '';
        $this->setLoggedInUser();

        $this->expectOnce( $this->MockUserManager, 'processInvite' );

        $this->AuthenticateShortcodeWithMockedDependencies->getOutput();
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

        $needle   = '<p class="success">Invitation processed successfully.</p>';
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertContains( $needle, $haystack );
    }

    public function testAuthenticateShortcodeDisplaysIgnoreSuccessMessage() {
        $_GET['n']       = 555;
        $_POST['ignore'] = '';
        $this->setLoggedInUser();

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $needle   = '<p class="success">Invitation ignored successfully.</p>';
        $haystack = $AuthenticateShortcode->getOutput();

        $this->assertContains( $needle, $haystack );
    }

    public function testAuthenticateShortcodeDoesntDisplayLoginFormOnSuccessfulLogin() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $this->setReturnValue( $this->MockCms, 'authenticateUser', true );

        $AuthenticateShortcode = $this->makeExpressiveAuthenticateShortcode();

        $haystack = $AuthenticateShortcode->getOutput();
        $needle = '<form';

        $this->assertDoesntContain($needle, $haystack);
    }

    public function testAuthenticateShortcodeLoginDisplaysOnlyOneRedirectMessage() {
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $this->setReturnValue( $this->MockCms, 'authenticateUser', true );

        $AuthenticateShortcode = new HfAuthenticateShortcode(
            $this->Factory->makeMarkupGenerator(),
            $this->Factory->makeAssetLocator(),
            $this->MockCms,
            $this->Factory->makeUserManager()
        );

        $haystack = $AuthenticateShortcode->getOutput();
        $needle = '<p class="info">Redirecting...';

        $this->assertEquals( 1, substr_count($haystack, $needle) );
    }

    public function testAuthenticateShortcodeUsesHookToLogin() {
        $Cms = $this->Factory->makeCms();
        $hookRegistered = has_action( 'after_setup_theme', array($Cms, 'authenticateUser') );
        $this->assertTrue($hookRegistered !== False);
    }
}
