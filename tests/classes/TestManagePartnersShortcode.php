<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestManagePartnersShortcode extends HfTestCase {
    public function testManagePartnersShortcodeClassExists() {
        $this->assertTrue( class_exists( 'HfManagePartnersShortcode' ) );
    }

    public function testManagePartnersShortcodeImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( $this->ManagePartnersShortcodeWithMockedDependencies, 'Hf_iShortcode' ) );
    }

    public function testManagePartnersShortcodeRegistered() {
        $this->assertTrue( shortcode_exists( 'hfManagePartners' ) );
    }

    public function testManagePartnersShortcodeRequiresLogin() {
        $this->expectOnce( $this->MockSecurity, 'requireLogin' );
        $this->ManagePartnersShortcodeWithMockedDependencies->getOutput();
    }

    public function testManagePartnersShortcodeDoesntRequireLoginWhenUserLoggedIn() {
        $this->setReturnValue($this->MockUserManager, 'isUserLoggedIn', true);
        $this->expectNever($this->MockSecurity, 'requireLogin');
        $this->ManagePartnersShortcodeWithMockedDependencies->getOutput();
    }
}
