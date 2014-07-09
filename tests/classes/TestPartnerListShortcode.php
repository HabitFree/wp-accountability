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
        $MockPartner->ID            = 7;
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
            $this->Factory->makeMarkupGenerator(),
            $this->MockAssetLocator
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

    public function testShortcodeExists() {
        $this->assertTrue( shortcode_exists( 'hfPartnerList' ) );
    }

    public function testShortcodeListIncludesWordUnpartner() {
        $this->setDefaultReturnValues();

        $PartnerListShortcode = new HfPartnerListShortcode(
            $this->MockUserManager,
            $this->Factory->makeMarkupGenerator(),
            $this->MockAssetLocator
        );

        $haystack = $PartnerListShortcode->getOutput();
        $needle   = 'unpartner';

        $this->assertContains( $needle, $haystack );
    }

    public function testShortcodeGetsCurrentPageUrl() {
        $this->setDefaultReturnValues();
        $this->expectOnce( $this->MockAssetLocator, 'getCurrentPageUrl' );
        $this->PartnerListShortcodeWithMockedDependencies->getOutput();
    }

    public function testShortcodeMakesForm() {
        $this->setDefaultReturnValues();
        $this->expectOnce( $this->MockMarkupGenerator, 'makeForm' );
        $this->PartnerListShortcodeWithMockedDependencies->getOutput();
    }

    public function testShortcodeUsesCurrentPageUrlAndListWhenCallingForm() {
        $this->setDefaultReturnValues();
        $this->setReturnValue( $this->MockAssetLocator, 'getCurrentPageUrl', 'pond.net' );
        $this->setReturnValue( $this->MockMarkupGenerator, 'makeList', 'duck' );
        $this->expectOnce( $this->MockMarkupGenerator, 'makeForm', array('pond.net', 'duck', 'partnerlist') );
        $this->PartnerListShortcodeWithMockedDependencies->getOutput();
    }

    public function testShortcodeReturnsForm() {
        $this->setDefaultReturnValues();
        $this->setReturnValue( $this->MockMarkupGenerator, 'makeForm', 'duck' );
        $actual   = $this->PartnerListShortcodeWithMockedDependencies->getOutput();
        $expected = 'duck';
        $this->assertEquals( $expected, $actual );
    }

    public function testShortcodeMakesUnpartnerSubmitButton() {
        $this->setDefaultReturnValues();

        $PartnerListShortcode = new HfPartnerListShortcode(
            $this->MockUserManager,
            $this->Factory->makeMarkupGenerator(),
            $this->MockAssetLocator
        );

        $needle   = '<input type="button" name="7" value="unpartner" onclick="if (confirm(\'Sure?\')) { document.partnerlist.submit();}" />';
        $haystack = $PartnerListShortcode->getOutput();
        $this->assertContains($needle, $haystack);
    }
}
