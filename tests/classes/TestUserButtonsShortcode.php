<?php
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestUserButtonsShortcode extends HfTestCase {
    // Helper Functions
    
    // Tests
    
    public function testUserSettingsShortcodeExists() {
        $this->assertTrue(class_exists('HfUserButtonsShortcode'));
    }

    public function testUserButtonsShortcodeImplementsShortcodeInterface() {
        $this->assertTrue($this->classImplementsInterface('HfUserButtonsShortcode', 'Hf_iShortcode'));
    }

    public function testUserButtonsShortcodeExists() {
        $this->assertTrue( shortcode_exists( 'hfUserButtons' ) );
    }

    public function testUserButtonsShortcodeWelcomesUser() {
//        $credentials                  = array();
//        $credentials['user_login']    = $username;
//        $credentials['user_password'] = $password;
//
//        return !$this->isError(wp_signon( $credentials ));

        $AssetLocator = $this->Factory->makeAssetLocator();
        $UserManager = $this->myMakeMock('HfUserManager');
        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Admin');

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<p>Welcome back, Admin';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeWelcomesDifferentUser() {
        $AssetLocator = $this->Factory->makeAssetLocator();
        $UserManager = $this->myMakeMock('HfUserManager');
        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<p>Welcome back, Rodney';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeDisplaysLogOutLink() {
        $AssetLocator = $this->myMakeMock('HfUrlFinder');
        $UserManager = $this->myMakeMock('HfUserManager');

        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->mySetReturnValue($AssetLocator, 'getLogoutUrl', 'google.com');
        $this->mySetReturnValue($AssetLocator, 'getCurrentPageUrl', 'bing.com');
        $this->myExpectAtLeastOnce($AssetLocator, 'getLogoutUrl', array('bing.com'));
        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', true);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<a href="google.com">Log Out</a>';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeDoesntDisplayLogoutLinkWhenNotLoggedIn() {
        $UserManager = $this->myMakeMock('HfUserManager');
        $AssetLocator = $this->Factory->makeAssetLocator();
        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator);

        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', false);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = 'Log Out';

        $this->assertFalse($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeIncludesClosingParagraphTag() {
        $UserButtonsShortcode = $this->Factory->makeUserButtonsShortcode();

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '</p>';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

//    public function testUserButtonsShortcodeDisplaysLogInLink() {
//        $AssetLocator = $this->myMakeMock('HfUrlFinder');
//        $UserManager = $this->myMakeMock('HfUserManager');
//
//        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
//        $this->mySetReturnValue($AssetLocator, 'getLoginUrl', 'google.com');
//        $this->mySetReturnValue($AssetLocator, 'getCurrentPageUrl', 'bing.com');
//        $this->myExpectAtLeastOnce($AssetLocator, 'getLoginUrl', array('bing.com'));
//        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', false);
//
//        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator);
//
//        $haystack = $UserButtonsShortcode->getOutput();
//        $needle = '<a href="google.com">Log Out</a>';
//
//        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
//    }
}
