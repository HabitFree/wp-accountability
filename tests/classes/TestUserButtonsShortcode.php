<?php
if ( ! defined( 'ABSPATH' ) ) exit;
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
        $AssetLocator = $this->makeMock('HfUrlFinder');
        $UserManager = $this->makeMock('HfUserManager');
        $MarkupGenerator = $this->factory->makeMarkupGenerator();

        $this->setReturnValue($UserManager, 'getCurrentUserLogin', 'Admin');
        $this->setReturnValue($UserManager, 'isUserLoggedIn', true);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<p>Welcome back, Admin';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeWelcomesDifferentUser() {
        $AssetLocator = $this->makeMock('HfUrlFinder');
        $UserManager = $this->makeMock('HfUserManager');
        $MarkupGenerator = $this->factory->makeMarkupGenerator();

        $this->setReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->setReturnValue($UserManager, 'isUserLoggedIn', true);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<p>Welcome back, Rodney';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeDisplaysLogOutLink() {
        $AssetLocator = $this->makeMock('HfUrlFinder');
        $UserManager = $this->makeMock('HfUserManager');
        $MarkupGenerator = $this->factory->makeMarkupGenerator();

        $this->setReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->setReturnValue($AssetLocator, 'getLogoutUrl', 'google.com');
        $this->setReturnValue($AssetLocator, 'getCurrentPageUrl', 'bing.com');
        $this->expectAtLeastOnce($AssetLocator, 'getLogoutUrl', array('bing.com'));
        $this->setReturnValue($UserManager, 'isUserLoggedIn', true);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = "<a href='google.com'>Log Out</a>";

        $this->assertContains($needle,$haystack);
    }

    public function testUserButtonsShortcodeDoesntDisplayLogoutLinkWhenNotLoggedIn() {
        $UserManager = $this->makeMock('HfUserManager');
        $AssetLocator = $this->makeMock('HfUrlFinder');
        $MarkupGenerator = $this->factory->makeMarkupGenerator();

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $this->setReturnValue($UserManager, 'isUserLoggedIn', false);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = 'Log Out';

        $this->assertFalse($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeIncludesClosingParagraphTag() {
        $UserManager = $this->factory->makeUserManager();
        $AssetLocator = $this->makeMock('HfUrlFinder');
        $MarkupGenerator = $this->factory->makeMarkupGenerator();

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '</p>';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeDisplaysLogInLink() {
        $AssetLocator = $this->makeMock('HfUrlFinder');
        $UserManager = $this->makeMock('HfUserManager');
        $MarkupGenerator = $this->factory->makeMarkupGenerator();

        $this->setReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->setReturnValue($AssetLocator, 'getLoginUrl', 'google.com');
        $this->expectAtLeastOnce($AssetLocator, 'getLoginUrl');
        $this->setReturnValue($UserManager, 'isUserLoggedIn', false);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<a href=\'google.com\'>Log In</a>';

        $this->assertContains($needle,$haystack);
    }

    public function testUserButtonsShortcodeDisplaysSettingsLink() {
        $AssetLocator = $this->makeMock('HfUrlFinder');
        $UserManager = $this->makeMock('HfUserManager');
        $MarkupGenerator = $this->factory->makeMarkupGenerator();

        $this->setReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->setReturnValue($AssetLocator, 'getPageUrlByTitle', 'yahoo.com');
        $this->expectOnce($AssetLocator, 'getPageUrlByTitle', array('Settings'));
        $this->setReturnValue($UserManager, 'isUserLoggedIn', true);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<a href=\'yahoo.com\'>Settings</a>';

        $this->assertContains($needle,$haystack);
    }

    public function testUserButtonsShortcodeDisplaysRegisterLink() {
        $AssetLocator = $this->makeMock('HfUrlFinder');
        $UserManager = $this->makeMock('HfUserManager');
        $MarkupGenerator = $this->factory->makeMarkupGenerator();

        $this->setReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');
        $this->setReturnValue($AssetLocator, 'getPageUrlByTitle', 'nathanarthur.com');
        $this->expectOnce($AssetLocator, 'getPageUrlByTitle', array('Authenticate'));
        $this->setReturnValue($UserManager, 'isUserLoggedIn', false);

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager, $AssetLocator, $MarkupGenerator);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<a href=\'nathanarthur.com\'>Register</a>';

        $this->assertContains($needle,$haystack);
    }
}
