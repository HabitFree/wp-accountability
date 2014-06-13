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

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<a href="google.com">Log Out</a>';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }
}
