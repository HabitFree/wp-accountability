<?php
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestManagePartnersShortcode extends HfTestCase {
    public function testManagePartnersShortcodeClassExists() {
        $this->assertTrue(class_exists('HfManagePartnersShortcode'));
    }
}
