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
        $UserManager = $this->myMakeMock('HfUserManager');
        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Admin');

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<p>Welcome back, Admin';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function testUserButtonsShortcodeWelcomesDifferentUser() {
        $UserManager = $this->myMakeMock('HfUserManager');
        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '<p>Welcome back, Rodney';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }

    public function IGNOREtestUserButtonsShortcodeDisplaysLogOutLink() {
        $UserManager = $this->myMakeMock('HfUserManager');
        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Rodney');

        $UserButtonsShortcode = new HfUserButtonsShortcode($UserManager);

        $haystack = $UserButtonsShortcode->getOutput();
        $needle = '';

        $this->assertTrue($this->haystackContainsNeedle($haystack, $needle));
    }
}
