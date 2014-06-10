<?php
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestUserManager extends HfTestCase {
    // Helper Functions

    private function makeUserManagerMockDependencies() {
        $UrlFinder = $this->myMakeMock( 'HfUrlFinder' );
        $Database  = $this->myMakeMock( 'HfMysqlDatabase' );
        $Messenger = $this->myMakeMock( 'HfMailer' );
        $Cms       = $this->myMakeMock( 'HfWordPressInterface' );
        $PhpApi    = $this->myMakeMock( 'HfPhpLibrary' );

        return array($UrlFinder, $Database, $Messenger, $Cms, $PhpApi);
    }

    // Tests

    public function testEmailInviteSendingUsingMocks() {
        list( $UrlFinder, $Database, $Messenger, $Cms, $PhpApi ) = $this->makeUserManagerMockDependencies();

        $this->mySetReturnValue($Messenger, 'generateInviteID', 555);
        $this->mySetReturnValue($Database, 'generateEmailID', 5);

        $UserManager = new HfUserManager( $Database, $Messenger, $UrlFinder, $Cms, $PhpApi );
        $result      = $UserManager->sendInvitation( 1, 'me@test.com', 3 );

        $this->assertEquals( $result, 555 );
    }

    public function testInviteStorageInInviteTableUsingMocks() {
        list( $UrlFinder, $Database, $MessengerMock, $Cms, $PhpApi ) = $this->makeUserManagerMockDependencies();

        $Security = $this->myMakeMock( 'HfSecurity' );

        $Messenger = new HfMailer( $UrlFinder, $Security, $Database, $Cms );

        $this->mySetReturnValue($Database, 'idOfLastEmail', 5);
        $this->mySetReturnValue($Database, 'generateEmailID', 5);
        $this->mySetReturnValue($Cms, 'sendWpEmail', true);
        $this->mySetReturnValue($Security, 'createRandomString', 555);

        $expirationDate = date( 'Y-m-d H:i:s', strtotime( '+' . 3 . ' days' ) );

        $expectedRecord = array(
            'inviteID'       => 555,
            'inviterID'      => 1,
            'inviteeEmail'   => 'me@test.com',
            'emailID'        => 5,
            'expirationDate' => $expirationDate
        );

        $this->myExpectAtLeastOnce($Database, 'insertIntoDb', array('hf_invite', $expectedRecord));

        $UserManager = new HfUserManager( $Database, $Messenger, $UrlFinder, $Cms, $PhpApi );
        $UserManager->sendInvitation( 1, 'me@test.com', 3 );
    }
}