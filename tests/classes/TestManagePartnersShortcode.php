<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestManagePartnersShortcode extends HfTestCase {
    public function testManagePartnersShortcodeClassExists() {
        $this->assertTrue(class_exists('HfManagePartnersShortcode'));
    }

    public function testManagePartnersShortcodeImplementsShortcodeInterface() {
        $ManagePartnersShortcode = $this->Factory->makeManagePartnersShortcode();
        $this->assertTrue($this->classImplementsInterface($ManagePartnersShortcode, 'Hf_iShortcode'));
    }

    public function testManagePartnersShortcodeRegistered() {
        $this->assertTrue(shortcode_exists('hfManagePartners'));
    }
}
