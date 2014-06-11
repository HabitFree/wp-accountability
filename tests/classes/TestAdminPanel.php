<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestAdminPanel extends HfTestCase {
    // Helper Functions

    // Tests

    public function testGenerateAdminPanelButtons() {
        $Mailer       = $this->myMakeMock( 'HfMailer' );
        $URLFinder    = $this->myMakeMock( 'HfUrlFinder' );
        $DbConnection = $this->myMakeMock( 'HfMysqlDatabase' );
        $UserManager  = $this->myMakeMock( 'HfUserManager' );
        $Cms          = $this->myMakeMock( 'HfWordPressInterface' );

        $this->mySetReturnValue( $URLFinder, 'getCurrentPageURL', 'test.com' );

        $AdminPanel = new HfAdminPanel( $Mailer, $URLFinder, $DbConnection, $UserManager, $Cms );

        $expectedHtml = '<form action="test.com" method="post"><p><input type="submit" name="sendTestReportRequestEmail" value="Send test report request email" /></p><p><input type="submit" name="sendTestInvite" value="Send test invite" /></p><p><input type="submit" name="sudoReactivateExtension" value="Sudo reactivate extension" /></p></form>';
        $resultHtml   = $AdminPanel->generateAdminPanelForm();

        $this->assertEquals( $expectedHtml, $resultHtml );
    }
}
