<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestPartnerListShortcode extends HfTestCase {
    public function testPartnerListShortcodeImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( $this->MockedPartnerListShortcode, 'Hf_iShortcode' ) );
    }

    public function testPartnerListShortcodeGetsCurrentUser() {
        $this->setDefaultReturnValues();

        $this->expectAtLeastOnce( $this->MockUserManager, 'getCurrentUserId' );
        $this->MockedPartnerListShortcode->getOutput();
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
        $this->MockedPartnerListShortcode->getOutput();
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
            $this->MockUserManager,
            $this->Factory->makeMarkupGenerator(),
            $this->MockUrlFinder
        );

        return $PartnerListShortcode;
    }

    public function testPartnerListShortcodeGeneratesList() {
        $this->setReturnValue( $this->MockUserManager, 'getPartners', array() );
        $this->expectOnce( $this->MockHtmlGenerator, 'makeList' );
        $this->MockedPartnerListShortcode->getOutput();
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
        $this->expectOnce( $this->MockUrlFinder, 'getCurrentPageUrl' );
        $this->MockedPartnerListShortcode->getOutput();
    }

    public function testShortcodeMakesForm() {
        $this->setDefaultReturnValues();
        $this->expectOnce( $this->MockHtmlGenerator, 'makeForm' );
        $this->MockedPartnerListShortcode->getOutput();
    }

    public function testShortcodeReturnsForm() {
        $this->setDefaultReturnValues();
        $this->setReturnValue( $this->MockHtmlGenerator, 'makeForm', 'duck' );
        $actual   = $this->MockedPartnerListShortcode->getOutput();
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
        $this->setReturnValue( $this->MockUserManager, 'getCurrentUserId', 1 );
        $this->expectOnce( $this->MockUserManager, 'deleteRelationship', array(1, '7') );
        $this->MockedPartnerListShortcode->getOutput();
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
