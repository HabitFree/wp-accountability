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
        $AssetLocator = $this->myMakeMock('HfUrlFinder');
        $UserManager = $this->myMakeMock('HfUserManager');
        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Admin');
        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', true);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<p>Welcome back, Admin';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeWelcomesDifferentUser() {
        $AssetLocator = $this->myMakeMock('HfUrlFinder');
        $UserManager = $this->myMakeMock('HfUserManager');
        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', true);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<p>Welcome back, Rodney';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeDisplaysLogOutLink() {
        $AssetLocator = $this->myMakeMock('HfUrlFinder');
        $UserManager = $this->myMakeMock('HfUserManager');
        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->mySetReturnValue($AssetLocator, 'getLogoutUrl', 'google.com');
        $this->mySetReturnValue($AssetLocator, 'getCurrentPageUrl', 'bing.com');
        $this->myExpectAtLeastOnce($AssetLocator, 'getLogoutUrl', array('bing.com'));
        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', true);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<a href="google.com">Log Out</a>';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeDoesntDisplayLogoutLinkWhenNotLoggedIn() {
        $UserManager = $this->myMakeMock('HfUserManager');
        $AssetLocator = $this->myMakeMock('HfUrlFinder');
        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', false);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = 'Log Out';

        $this->assertFalse($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeIncludesClosingParagraphTag() {
        $UserManager = $this->Factory->makeUserManager();
        $AssetLocator = $this->myMakeMock('HfUrlFinder');
        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '</p>';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeDisplaysLogInLink() {
        $AssetLocator = $this->myMakeMock('HfUrlFinder');
        $UserManager = $this->myMakeMock('HfUserManager');
        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->mySetReturnValue($AssetLocator, 'getLoginUrl', 'google.com');
        $this->myExpectAtLeastOnce($AssetLocator, 'getLoginUrl');
        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', false);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<a href="google.com">Log In</a>';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeDisplaysSettingsLink() {
        $AssetLocator = $this->myMakeMock('HfUrlFinder');
        $UserManager = $this->myMakeMock('HfUserManager');
        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->mySetReturnValue($AssetLocator, 'getPageUrlByTitle', 'yahoo.com');
        $this->myExpectAtLeastOnce($AssetLocator, 'getPageUrlByTitle', array('Settings'));
        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', true);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<a href="yahoo.com">Settings</a>';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }
}
