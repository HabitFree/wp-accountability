<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestPartnerListShortcode extends HfTestCase {
    public function testPartnerListShortcodeImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( $this->PartnerListShortcodeWithMockedDependencies, 'Hf_iShortcode' ) );
    }

    public function testPartnerListShortcodeGetsCurrentUser() {
        $this->setDefaultReturnValues();

        $this->expectOnce( $this->MockUserManager, 'getCurrentUserId' );
        $this->PartnerListShortcodeWithMockedDependencies->getOutput();
    }

    private function setDefaultReturnValues() {
        $MockPartner                = new stdClass();
        $MockPartner->user_nicename = 'ludwig';
        $this->setReturnValue( $this->MockUserManager, 'getPartners', array($MockPartner) );
    }

    public function testPartnerListShortcodeUsesCurrentUserIdToGetPartners() {
        $this->setDefaultReturnValues();

        $this->setReturnValue( $this->MockUserManager, 'getCurrentUserId', 'duck' );
        $this->expectOnce( $this->MockUserManager, 'getPartners', array('duck') );
        $this->PartnerListShortcodeWithMockedDependencies->getOutput();
    }

    public function testPartnerListShortcodeOutputsPartnersName() {
        $this->setDefaultReturnValues();

        $PartnerListShortcode = new HfPartnerListShortcode(
            $this->MockUserManager,
            $this->Factory->makeMarkupGenerator()
        );

        $haystack = $PartnerListShortcode->getOutput();
        $needle   = 'ludwig';

        $this->assertContains( $needle, $haystack );
    }

    public function testPartnerListShortcodeGeneratesList() {
        $this->setReturnValue( $this->MockUserManager, 'getPartners', array() );
        $this->expectOnce( $this->MockMarkupGenerator, 'makeList' );
        $this->PartnerListShortcodeWithMockedDependencies->getOutput();
    }

    public function testPartnerListShortcodeReturnsMarkupList() {
        $this->setDefaultReturnValues();
        $this->setReturnValue( $this->MockMarkupGenerator, 'makeList', 'duck' );
        $actual = $this->PartnerListShortcodeWithMockedDependencies->getOutput();
        $this->assertEquals( 'duck', $actual );
    }

    public function testShortcodeExists() {
        $this->assertTrue(shortcode_exists('hfPartnerList'));
    }

    public function testShortcodeListIncludesWordUnpartner() {
        $this->setDefaultReturnValues();

        $PartnerListShortcode = new HfPartnerListShortcode(
            $this->MockUserManager,
            $this->Factory->makeMarkupGenerator()
        );

        $haystack = $PartnerListShortcode->getOutput();
        $needle   = 'unpartner';

        $this->assertContains( $needle, $haystack );
    }
}
