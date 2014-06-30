<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestInvitePartnerShortcode extends HfTestCase {
    // Helper Functions

    // Tests


    public function testInvitePartnerShortcodeImplementsShortcodeInterface() {
        $this->assertTrue( $this->classImplementsInterface( $this->InvitePartnerShortcodeWithMockedDependencies, 'Hf_iShortcode' ) );
    }

    public function testInvitePartnerShortcodeCreatesForm() {
        $actual = $this->InvitePartnerShortcodeWithMockedDependencies->getOutput();
        $this->assertContains('<form', $actual);
    }

    public function testInvitePartnerShortcodeCallsGetCurrentUrl() {
        $this->expectOnce($this->MockAssetLocator, 'getCurrentPageUrl');
        $this->InvitePartnerShortcodeWithMockedDependencies->getOutput();
    }

    public function testInvitePartnerShortcodeUsesCurrentUrlAsFormAction() {
        $this->setReturnValue($this->MockAssetLocator, 'getCurrentPageUrl', 'test.com');
        $output = $this->InvitePartnerShortcodeWithMockedDependencies->getOutput();
        $this->assertContains('<form action="test.com"', $output);
    }

    public function testInvitePartnerShortcodeIncludesEmailField() {
        $output = $this->InvitePartnerShortcodeWithMockedDependencies->getOutput();
        $this->assertContains('<label for="email"><span class="required">*</span> Email: <input type="text" name="email" value="" required /></label>', $output);
    }

    public function testInvitePartnerShortcodeIncludesSubmitButton() {
        $output = $this->InvitePartnerShortcodeWithMockedDependencies->getOutput();
        $this->assertContains('<input type="submit" name="submit" value="Invite" />', $output);
    }
}
