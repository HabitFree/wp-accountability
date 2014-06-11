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
        $AssetLocator          = $this->Factory->makeUrlFinder();

        $result = $AuthenticateShortcode->getOutput();
        $url    = $AssetLocator->getCurrentPageUrl();

        $logInHtml = '<form action="' . $url . '" method="post"><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><input type="submit" name="login" value="Log In" /></p></form>';

        $this->assertTrue( strstr( $result, $logInHtml ) != false );
    }

    public function testAuthenticateShortcodeIncludesRegistrationForm() {
        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $AssetLocator          = $this->Factory->makeUrlFinder();

        $result = $AuthenticateShortcode->getOutput();
        $url    = $AssetLocator->getCurrentPageUrl();

        $expectedHtml = '<form action="' . $url . '" method="post"><p><label for="username"><span class="required">*</span> Username: <input type="text" name="username" value="" required /></label></p><p><label for="email"><span class="required">*</span> Email: <input type="text" name="email" value="" required /></label></p><p><label for="password"><span class="required">*</span> Password: <input type="password" name="password" required /></label></p><p><label for="passwordConfirmation"><span class="required">*</span> Confirm Password: <input type="password" name="passwordConfirmation" required /></label></p><p><input type="submit" name="register" value="Register" /></p></form>';

        $this->assertTrue( $this->haystackContainsNeedle( $result, $expectedHtml ) );
    }

    public function testAuthenticateShortcodeUsesCurrentUrl() {
        $Cms                   = new HfWordPressInterface();
        $HtmlGenerator         = new HfHtmlGenerator( $Cms );
        $UrlLocator            = $this->myMakeMock( 'HfUrlFinder' );
        $currentUrl            = 'mysite.com';
        $AuthenticateShortcode = new HfAuthenticateShortcode( $HtmlGenerator, $UrlLocator );

        $this->mySetReturnValue( $UrlLocator, 'getCurrentPageUrl', $currentUrl );
        $result = $AuthenticateShortcode->getOutput();

        $this->assertEquals( 2, substr_count( $result, $currentUrl ) );
    }

    public function testAuthenticateShortcodeRemembersUsernameOnPost() {
        $_POST['login']    = '';
        $_POST['username'] = 'CharlieBrown';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $html                  = $AuthenticateShortcode->getOutput();

        $this->assertEquals( 2, substr_count( $html, $_POST['username'] ) );
    }

    public function testAuthenticateShortcodeRemembersEmailOnPost() {
        $_POST['register']             = '';
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';
        $_POST['email']                = 'charlie@peanuts.net';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $html                  = $AuthenticateShortcode->getOutput();

        $this->assertEquals( 1, substr_count( $html, $_POST['email'] ) );
    }

    public function testAuthenticateShortcodeChecksNewPasswordsMatch() {
        $_POST['register']             = '';
        $_POST['password']             = 'duck';
        $_POST['passwordConfirmation'] = 'goat';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="error">Please make sure your passwords match.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodePassesMatchingPasswords() {
        $_POST['register']             = '';
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
        $_POST['password']             = '';
        $_POST['passwordConfirmation'] = '';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $needle                = '<p class="error">Please enter a username.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    public function testAuthenticateShortcodeRequiresUsernameEntryAndChecksPasswords() {
        $_POST['register']             = '';
        $_POST['username']             = '';
        $_POST['password']             = 'horse';
        $_POST['passwordConfirmation'] = 'chimpanzee';

        $AuthenticateShortcode = $this->Factory->makeAuthenticateShortcode();
        $haystack              = $AuthenticateShortcode->getOutput();
        $usernameNeedle        = '<p class="error">Please enter a username.</p>';
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
        $needle                = '<p class="error">Please enter a password.</p>';

        $this->assertTrue( $this->haystackContainsNeedle( $haystack, $needle ) );
    }
}
