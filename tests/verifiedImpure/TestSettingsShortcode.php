<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestSettingsShortcode extends HfTestCase {
    // Helper Functions
    
    // Tests

    public function testSettingsShortcodeOutputsAnything() {
        $SettingsShortcode = $this->factory->makeSettingsShortcode();
        $output            = $SettingsShortcode->getOutput();

        $this->assertTrue( strlen( $output ) > 0 );
    }

    public function testSettingsShortcodeClassExists() {
        $this->assertTrue( class_exists( 'HfSettingsShortcode' ) );
    }
}
