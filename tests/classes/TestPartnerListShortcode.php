<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestPartnerListShortcode extends HfTestCase {
    public function testPartnerListShortcodeImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( $this->mockedPartnerListShortcode, 'Hf_iShortcode' ) );
    }

    public function testPartnerListShortcodeGetsCurrentUser() {
        $this->setDefaultReturnValues();

        $this->expectAtLeastOnce( $this->mockUserManager, 'getCurrentUserId' );
        $this->mockedPartnerListShortcode->getOutput();
    }

    private function setDefaultReturnValues() {
        $MockPartner                = new stdClass();
        $MockPartner->user_nicename = 'ludwig';
        $MockPartner->ID            = 7;
        $this->setReturnValue( $this->mockUserManager, 'getPartners', array($MockPartner) );
    }

    public function testPartnerListShortcodeUsesCurrentUserIdToGetPartners() {
        $this->setDefaultReturnValues();

        $this->setReturnValue( $this->mockUserManager, 'getCurrentUserId', 'duck' );
        $this->expectOnce( $this->mockUserManager, 'getPartners', array('duck') );
        $this->mockedPartnerListShortcode->getOutput();
    }

    public function testPartnerListShortcodeOutputsPartnersName() {
        $this->setDefaultReturnValues();
        $PartnerListShortcode = $this->makeExpressivePartnerListShortcode();

        $haystack = $PartnerListShortcode->getOutput();
        $needle   = 'ludwig';

        $this->assertContains( $needle, $haystack );
    }

    private function makeExpressivePartnerListShortcode() {
        $PartnerListShortcode = new HfPartnerListShortcode(
            $this->mockUserManager,
            $this->factory->makeMarkupGenerator(),
            $this->mockAssetLocator
        );

        return $PartnerListShortcode;
    }

    public function testPartnerListShortcodeGeneratesList() {
        $this->setReturnValue( $this->mockUserManager, 'getPartners', array() );
        $this->expectOnce( $this->mockMarkupGenerator, 'makeList' );
        $this->mockedPartnerListShortcode->getOutput();
    }

    public function testShortcodeExists() {
        $this->assertTrue( shortcode_exists( 'hfPartnerList' ) );
    }

    public function testShortcodeListIncludesWordUnpartner() {
        $this->setDefaultReturnValues();
        $PartnerListShortcode = $this->makeExpressivePartnerListShortcode();

        $haystack = $PartnerListShortcode->getOutput();
        $needle   = 'unpartner';

        $this->assertContains( $needle, $haystack );
    }

    public function testShortcodeGetsCurrentPageUrl() {
        $this->setDefaultReturnValues();
        $this->expectOnce( $this->mockAssetLocator, 'getCurrentPageUrl' );
        $this->mockedPartnerListShortcode->getOutput();
    }

    public function testShortcodeMakesForm() {
        $this->setDefaultReturnValues();
        $this->expectOnce( $this->mockMarkupGenerator, 'makeForm' );
        $this->mockedPartnerListShortcode->getOutput();
    }

    public function testShortcodeReturnsForm() {
        $this->setDefaultReturnValues();
        $this->setReturnValue( $this->mockMarkupGenerator, 'makeForm', 'duck' );
        $actual   = $this->mockedPartnerListShortcode->getOutput();
        $expected = 'duck';
        $this->assertEquals( $expected, $actual );
    }

    public function testShortcodeMakesUnpartnerSubmitButton() {
        $this->setDefaultReturnValues();
        $PartnerListShortcode = $this->makeExpressivePartnerListShortcode();

        $needle1  = '<input type="button" name="7" value="unpartner" onclick="if (confirm(';
        $needle2  = ')) { submitValue(7);}" />';
        $haystack = $PartnerListShortcode->getOutput();
        $this->assertContains( $needle1, $haystack );
        $this->assertContains( $needle2, $haystack );
    }

    public function testShortcodeUsesUsernameInConfirmationDialog() {
        $this->setDefaultReturnValues();
        $PartnerListShortcode = $this->makeExpressivePartnerListShortcode();

        $needle   = "confirm('Are you sure you want to stop partnering with ludwig?')";
        $haystack = $PartnerListShortcode->getOutput();
        $this->assertContains( $needle, $haystack );
    }

    public function testShortcodeDeletesRelationshipOnSubmission() {
        $_POST['userId'] = '7';
        $this->setDefaultReturnValues();
        $this->setReturnValue( $this->mockUserManager, 'getCurrentUserId', 1 );
        $this->expectOnce( $this->mockUserManager, 'deleteRelationship', array(1, '7') );
        $this->mockedPartnerListShortcode->getOutput();
    }

    public function testShortcodeCreatesHiddenFieldForUserId() {
        $this->setDefaultReturnValues();
        $PartnerListShortcode = $this->makeExpressivePartnerListShortcode();

        $needle   = '<input type="hidden" name="userId" />';
        $haystack = $PartnerListShortcode->getOutput();
        $this->assertContains( $needle, $haystack );
    }

    public function testShortcodeCreatesClickHandlerJavascript() {
        $this->setDefaultReturnValues();
        $PartnerListShortcode = $this->makeExpressivePartnerListShortcode();
        $needle               =
            '<script>
                function submitValue (n) {
                    var f = document.forms.partnerlist;
                    f.userId.value = n;
                    f.submit();
                }
            </script>';
        $haystack             = $PartnerListShortcode->getOutput();
        $this->assertContains( $needle, $haystack );
    }
}
