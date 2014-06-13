<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestAuthenticateShortcode extends HfTestCase {
    // Helper Functions

    // Tests

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
        $Cms                   = new HfWordPressInterface();
        $HtmlGenerator         = new HfHtmlGenerator( $Cms );
        $UrlLocator            = $this->myMakeMock( 'HfUrlFinder' );
        $UserManager           = $this->Factory->makeUserManager();
        $currentUrl            = 'mysite.com';
        $AuthenticateShortcode = new HfAuthenticateShortcode( $HtmlGenerator, $UrlLocator, $Cms, $UserManager );

        $this->mySetReturnValue( $UrlLocator, 'getCurrentPageUrl', $currentUrl );
        $result = $AuthenticateShortcode->getOutput();

        $this->assertEquals( 2, substr_count( $result, $currentUrl ) );
    }

    public function testAuthenticateShortcodeRemembersUsernameOnPost() {
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = 'CharlieBrown';
        $_POST['password'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $html                  = $AuthenticateShortcode->getOutput();

        $this->assertEquals( 2, substr_count( $html, $_POST['username'] ) );
    }

    public function testAuthenticateShortcodeRemembersEmailOnPost() {
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST                         = array();
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
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = '';
        $_POST['password'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="error">Please enter your username.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresPassword() {
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = '';
        $_POST['password'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="error">Please enter your password.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeAttemptsLogIn() {
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->myExpectOnce( $ContentManagementSystem, 'authenticateUser', array('Joe', 'bo') );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysLogInFailureError() {
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->mySetReturnValue( $ContentManagementSystem, 'authenticateUser', false );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="error">That username and password combination is incorrect.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDisplaysLogInFailureErrorWithinTab() {
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->mySetReturnValue( $ContentManagementSystem, 'authenticateUser', false );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '[su_tab title="Log In"]<p class="error">That username and password combination is incorrect.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDisplaysLogInSuccessMessage() {
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->mySetReturnValue( $ContentManagementSystem, 'authenticateUser', true );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="success">Welcome back!</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeGeneratesRedirectScript() {
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->mySetReturnValue( $ContentManagementSystem, 'authenticateUser', true );

        $homeUrl = $AssetLocator->getHomePageUrl();

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<script>setTimeout(function(){window.location.replace("' . $homeUrl . '")},5000);</script>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDoesNotAttemptLogInWhenFormFailsToValidate() {
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = '';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->myExpectNever( $ContentManagementSystem, 'authenticateUser' );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeAttemptsRegistration() {
        $_POST                         = array();
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->myExpectOnce( $ContentManagementSystem, 'createUser', array('Joe', 'bo', 'joe@wallysworld.com') );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysRegistrationSuccessMessage() {
        $_POST                         = array();
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->mySetReturnValue( $ContentManagementSystem, 'createUser', true );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="success">Welcome to HabitFree!</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeGeneratesRedirectScriptOnRegistration() {
        $_POST                         = array();
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->mySetReturnValue( $ContentManagementSystem, 'createUser', true );

        $homeUrl = $AssetLocator->getHomePageUrl();

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<script>setTimeout(function(){window.location.replace("' . $homeUrl . '")},5000);</script>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRegistrationProcessInvite() {
        $_POST                         = array();
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';
        $_GET['n']                     = 555;

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->myMakeMock( 'HfUserManager' );

        $mockUser     = new stdClass();
        $mockUser->ID = 7;

        $this->mySetReturnValue( $ContentManagementSystem, 'createUser', true );
        $this->mySetReturnValue( $ContentManagementSystem, 'currentUser', $mockUser );
        $this->mySetReturnValue( $ContentManagementSystem, 'getUserEmail', 'joe@wallysworld.com' );
        $this->myExpectOnce( $UserManager, 'processInvite', array('joe@wallysworld.com', 555) );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeLoginProcessInvite() {
        $_POST             = array();
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';
        $_GET['n']         = 555;

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->myMakeMock( 'HfUserManager' );

        $mockUser     = new stdClass();
        $mockUser->ID = 7;

        $this->mySetReturnValue( $ContentManagementSystem, 'authenticateUser', true );
        $this->mySetReturnValue( $ContentManagementSystem, 'currentUser', $mockUser );
        $this->mySetReturnValue( $ContentManagementSystem, 'getUserEmail', 'joe@wallysworld.com' );
        $this->myExpectOnce( $UserManager, 'processInvite', array('joe@wallysworld.com', 555) );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );

        $AuthenticateShortcode->getOutput();
    }

    public function testAuthenticateShortcodeDisplaysRegistrationErrorMessage() {
        $_POST                         = array();
        $_POST['register']             = '';
        $_POST['username']             = 'Joe';
        $_POST['email']                = 'joe@wallysworld.com';
        $_POST['password']             = 'bo';
        $_POST['passwordConfirmation'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->mySetReturnValue( $ContentManagementSystem, 'createUser', false );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='error'>We're very sorry, but something seems to have gone wrong with your registration.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeDisplaysInvitationInfo() {
        $_POST     = array();
        $_GET      = array();
        $_GET['n'] = 555;

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = "<p class='info'>Looks like you're responding to an invitation. Feel free to either register or log into an existing account—either way we'll automatically set up accountability between you and the user who invited you.</p>";

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeChecksUrlGetForActiveTab() {
        $_POST       = array();
        $_GET        = array();
        $_GET['n']   = 555;
        $_GET['tab'] = 2;

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '[su_tabs active="2"]';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRegistrationAdvisesUserOnUsernameChoice() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack = $AuthenticateShortcode->getOutput();
        $needle = '<p class="info"><strong>Important:</strong> HabitFree is a support community. For this reason, please choose a non-personally-identifiable username.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeLoginDisplaysRedirectMessage() {
        $_POST             = array();
        $_GET = array();
        $_POST['login']    = '';
        $_POST['username'] = 'Joe';
        $_POST['password'] = 'bo';

        $DisplayCodeGenerator    = $this->Factory->makeMarkupGenerator();
        $AssetLocator            = $this->Factory->makeAssetLocator();
        $ContentManagementSystem = $this->myMakeMock( 'HfWordPressInterface' );
        $UserManager             = $this->Factory->makeUserManager();

        $this->mySetReturnValue( $ContentManagementSystem, 'authenticateUser', true );

        $AuthenticateShortcode = new HfAuthenticateShortcode( $DisplayCodeGenerator, $AssetLocator, $ContentManagementSystem, $UserManager );

        $haystack = $AuthenticateShortcode->getOutput();

        $openingTagPosition = stripos($haystack, '[su_tab title="Log In"]');
        $closingTagPosition = stripos($haystack, '[/su_tab]');

        $substring = substr($haystack, $openingTagPosition, $closingTagPosition);

        $needle = '<p class="info">Redirecting...';

        $this->assertTrue( $this->haystackContainsNeedle( $substring, $needle ) );
    }
}
