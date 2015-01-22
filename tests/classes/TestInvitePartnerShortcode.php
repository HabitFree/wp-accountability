<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestInvitePartnerShortcode extends HfTestCase {
    // Helper Functions

    // Tests


    public function testInvitePartnerShortcodeImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( $this->mockedInvitePartnerShortcode, 'Hf_iShortcode' ) );
    }

    public function testInvitePartnerShortcodeCreatesForm() {
        $actual = $this->mockedInvitePartnerShortcode->getOutput();
        $this->assertContains('<form', $actual);
    }

    public function testInvitePartnerShortcodeCallsGetCurrentUrl() {
        $this->expectOnce($this->mockAssetLocator, 'getCurrentPageUrl');
        $this->mockedInvitePartnerShortcode->getOutput();
    }

    public function testInvitePartnerShortcodeUsesCurrentUrlAsFormAction() {
        $this->setReturnValue($this->mockAssetLocator, 'getCurrentPageUrl', 'test.com');
        $output = $this->mockedInvitePartnerShortcode->getOutput();
        $this->assertContains('<form action="test.com"', $output);
    }

    public function testInvitePartnerShortcodeIncludesEmailField() {
        $output = $this->mockedInvitePartnerShortcode->getOutput();
        $this->assertContains('<label for="email"><span class="required">*</span> Email: <input type="text" name="email" value="" required /></label>', $output);
    }

    public function testInvitePartnerShortcodeIncludesSubmitButton() {
        $output = $this->mockedInvitePartnerShortcode->getOutput();
        $this->assertContains('<input type="submit" name="submit" value="Invite" />', $output);
    }

    public function testInviteShortcodeErrsOnEmptyEmail() {
        $_POST['submit'] = '';
        $_POST['email'] = '';
        $InviteShortcode = new HfInvitePartnerShortcode(
            $this->mockAssetLocator,
            $this->factory->makeMarkupGenerator(),
            $this->mockUserManager
        );
        $output = $InviteShortcode->getOutput();
        $this->assertContains("<p class='error'>Please enter a valid email address.</p>", $output);
    }

    public function testInviteShortcodeDoesNotErrOnValidEmail() {
        $_POST['submit'] = '';
        $_POST['email'] = 'narthur.a@gmail.com';
        $InviteShortcode = new HfInvitePartnerShortcode(
            $this->mockAssetLocator,
            $this->factory->makeMarkupGenerator(),
            $this->mockUserManager
        );
        $output = $InviteShortcode->getOutput();
        $this->assertFalse($this->haystackContainsNeedle($output, "<p class='error'>Please enter a valid email address.</p>"));
    }

    public function testInviteShortcodeDoesNotDisplayEmailErrorWhenFormNotSubmitted() {
        $output = $this->factory->makeInvitePartnerShortcode()->getOutput();
        $this->assertFalse($this->haystackContainsNeedle($output, "<p class='error'>Please enter a valid email address.</p>"));
    }

    public function testInviteShortcodeErrsOnInvalidEmail() {
        $_POST['submit'] = '';
        $_POST['email'] = 'fakeItTilYouMakeIt';
        $InviteShortcode = new HfInvitePartnerShortcode(
            $this->mockAssetLocator,
            $this->factory->makeMarkupGenerator(),
            $this->mockUserManager
        );
        $output = $InviteShortcode->getOutput();
        $this->assertContains("<p class='error'>Please enter a valid email address.</p>", $output);
    }

    public function testInvitePartnerShortcodeExists() {
        $this->assertTrue( shortcode_exists( 'hfInvitePartner' ) );
    }

    public function testInvitePartnerShortcodeWarnsUserOfPrivacy() {
        $haystack = $this->factory->makeInvitePartnerShortcode()->getOutput();
        $needle = '<p class="info"><strong>Note:</strong> By inviting someone to become a partner you grant them access to all your goals and progress history.</p>';
        $this->assertContains($needle, $haystack);
    }

    public function testInvitePartnerShortcodeSendsInvitation() {
        $_POST['submit'] = '';
        $_POST['email'] = 'test@test.com';

        $this->expectOnce($this->mockUserManager, 'sendInvitation', array(1, 'test@test.com'));
        $this->setReturnValue($this->mockUserManager, 'getCurrentUserId', 1);

        $this->mockedInvitePartnerShortcode->getOutput();
    }

    public function testInvitePartnerShortcodeDisplaysSuccessMessage() {
        $_POST['submit'] = '';
        $_POST['email'] = 'test@test.com';

        $InviteShortcode = new HfInvitePartnerShortcode(
            $this->mockAssetLocator,
            $this->factory->makeMarkupGenerator(),
            $this->mockUserManager
        );

        $output = $InviteShortcode->getOutput();

        $this->assertContains('<p class="success">test@test.com has been successfully invited to partner with you.</p>', $output);
    }

    public function testInvitePartnerShortcodeDoesNotInviteUserWhenEmailMalformed() {
        $_POST['submit'] = '';
        $_POST['email'] = 'fakeItTilYouMakeIt';

        $this->expectNever($this->mockUserManager, 'sendInvitation');

        $InviteShortcode = new HfInvitePartnerShortcode(
            $this->mockAssetLocator,
            $this->factory->makeMarkupGenerator(),
            $this->mockUserManager
        );

        $InviteShortcode->getOutput();
    }

    public function testInvitePartnerShortcodeIncludesHeader() {
        $InviteShortcode = new HfInvitePartnerShortcode(
            $this->mockAssetLocator,
            $this->factory->makeMarkupGenerator(),
            $this->mockUserManager
        );

        $result = $InviteShortcode->getOutput();

        $this->assertContains('<h2>Invite Partner</h2>', $result);
    }
}
