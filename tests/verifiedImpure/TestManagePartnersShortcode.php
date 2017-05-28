<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestManagePartnersShortcode extends HfTestCase {
    public function testManagePartnersShortcodeClassExists() {
        $this->assertTrue( class_exists( 'HfManagePartnersShortcode' ) );
    }

    public function testManagePartnersShortcodeImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( $this->mockedManagePartnersShortcode, 'Hf_iShortcode' ) );
    }

    public function testManagePartnersShortcodeRegistered() {
        $this->assertTrue( shortcode_exists( 'hfManagePartners' ) );
    }

    public function testManagePartnersShortcodeRequiresLogin() {
        $this->expectOnce( $this->mockSecurity, 'requireLogin' );
        $this->mockedManagePartnersShortcode->getOutput();
    }

    public function testManagePartnersShortcodeDoesntRequireLoginWhenUserLoggedIn() {
        $this->setReturnValue($this->mockUserManager, 'isUserLoggedIn', true);
        $this->expectNever($this->mockSecurity, 'requireLogin');
        $this->mockedManagePartnersShortcode->getOutput();
    }

    public function testManagePartnerShortcodeReturnsLoginForm() {
        $this->setReturnValue($this->mockSecurity, 'requireLogin', 'duck');
        $result = $this->mockedManagePartnersShortcode->getOutput();
        $this->assertEquals('duck', $result);
    }

    public function testShortcodeQueriesPartnerList() {
        $this->expectOnce($this->mockPartnerListShortcode, 'getOutput');
        $this->setReturnValue($this->mockUserManager, 'isUserLoggedIn', true);
        $this->mockedManagePartnersShortcode->getOutput();
    }

    public function testShortcodeQueriesInvitePartnerForm() {
        $this->expectOnce($this->mockInvitePartnerShortcode, 'getOutput');
        $this->setReturnValue($this->mockUserManager, 'isUserLoggedIn', true);
        $this->mockedManagePartnersShortcode->getOutput();
    }

    public function testShortcodeReturnsPartnerList() {
        $this->setReturnValue($this->mockUserManager, 'isUserLoggedIn', true);
        $this->setReturnValue($this->mockPartnerListShortcode, 'getOutput', 'duck');
        $result = $this->mockedManagePartnersShortcode->getOutput();
        $this->assertContains('duck', $result);
    }

    public function testShortcodeReturnsInviteForm() {
        $this->setReturnValue($this->mockUserManager, 'isUserLoggedIn', true);
        $this->setReturnValue($this->mockInvitePartnerShortcode, 'getOutput', 'goose');
        $result = $this->mockedManagePartnersShortcode->getOutput();
        $this->assertContains('goose', $result);
    }
}
