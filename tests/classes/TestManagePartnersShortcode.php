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
        $this->assertTrue( $this->classImplementsInterface( $this->MockedManagePartnersShortcode, 'Hf_iShortcode' ) );
    }

    public function testManagePartnersShortcodeRegistered() {
        $this->assertTrue( shortcode_exists( 'hfManagePartners' ) );
    }

    public function testManagePartnersShortcodeRequiresLogin() {
        $this->expectOnce( $this->MockSecurity, 'requireLogin' );
        $this->MockedManagePartnersShortcode->getOutput();
    }

    public function testManagePartnersShortcodeDoesntRequireLoginWhenUserLoggedIn() {
        $this->setReturnValue($this->MockUserManager, 'isUserLoggedIn', true);
        $this->expectNever($this->MockSecurity, 'requireLogin');
        $this->MockedManagePartnersShortcode->getOutput();
    }

    public function testManagePartnerShortcodeReturnsLoginForm() {
        $this->setReturnValue($this->MockSecurity, 'requireLogin', 'duck');
        $result = $this->MockedManagePartnersShortcode->getOutput();
        $this->assertEquals('duck', $result);
    }

    public function testShortcodeQueriesPartnerList() {
        $this->expectOnce($this->MockPartnerListShortcode, 'getOutput');
        $this->setReturnValue($this->MockUserManager, 'isUserLoggedIn', true);
        $this->MockedManagePartnersShortcode->getOutput();
    }

    public function testShortcodeQueriesInvitePartnerForm() {
        $this->expectOnce($this->MockInvitePartnerShortcode, 'getOutput');
        $this->setReturnValue($this->MockUserManager, 'isUserLoggedIn', true);
        $this->MockedManagePartnersShortcode->getOutput();
    }

    public function testShortcodeReturnsPartnerList() {
        $this->setReturnValue($this->MockUserManager, 'isUserLoggedIn', true);
        $this->setReturnValue($this->MockPartnerListShortcode, 'getOutput', 'duck');
        $result = $this->MockedManagePartnersShortcode->getOutput();
        $this->assertContains('duck', $result);
    }

    public function testShortcodeReturnsInviteForm() {
        $this->setReturnValue($this->MockUserManager, 'isUserLoggedIn', true);
        $this->setReturnValue($this->MockInvitePartnerShortcode, 'getOutput', 'goose');
        $result = $this->MockedManagePartnersShortcode->getOutput();
        $this->assertContains('goose', $result);
    }
}
