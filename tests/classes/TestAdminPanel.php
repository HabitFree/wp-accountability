<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestAdminPanel extends HfTestCase {
    public function testGenerateAdminPanelButtons() {
        $expectedHtml = '<form action="url" method="post"><p><input type="submit" name="sendTestReportRequestEmail" value="Send test report request email" /></p><p><input type="submit" name="sendTestInvite" value="Send test invite" /></p><p><input type="submit" name="sudoReactivateExtension" value="Sudo reactivate extension" /></p></form>';
        $resultHtml   = $this->mockedAdminPanel->generateAdminPanelForm();

        $this->assertEquals( $expectedHtml, $resultHtml );
    }

    public function testGetsCurrentUserId() {
        $this->expectOnce($this->mockUserManager, 'getCurrentUserId');
        $this->mockedAdminPanel->generateAdminPanel();
    }
}
