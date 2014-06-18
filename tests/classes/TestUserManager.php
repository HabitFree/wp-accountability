<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestUserManager extends HfTestCase {
    // Helper Functions

    private function makeUserManagerMockDependencies() {
        $UrlFinder = $this->myMakeMock( 'HfUrlFinder' );
        $Database  = $this->myMakeMock( 'HfMysqlDatabase' );
        $Messenger = $this->myMakeMock( 'HfMailer' );
        $Cms       = $this->myMakeMock( 'HfWordPress' );

        return array($UrlFinder, $Database, $Messenger, $Cms);
    }

    // Tests

    public function testEmailInviteSendingUsingMocks() {
        list( $UrlFinder, $Database, $Messenger, $Cms ) = $this->makeUserManagerMockDependencies();

        $this->mySetReturnValue( $Messenger, 'generateSecureEmailId', 555 );
        $this->mySetReturnValue( $Database, 'generateEmailID', 5 );

        $UserManager = new HfUserManager( $Database, $Messenger, $UrlFinder, $Cms );
        $result      = $UserManager->sendInvitation( 1, 'me@test.com', 3 );

        $this->assertEquals( $result, 555 );
    }

    public function testInviteStorageInInviteTableUsingMocks() {
        list( $UrlFinder, $Database, $MessengerMock, $Cms ) = $this->makeUserManagerMockDependencies();

        $Security = $this->myMakeMock( 'HfSecurity' );

        $Messenger = new HfMailer( $UrlFinder, $Security, $Database, $Cms );

        $this->mySetReturnValue( $Database, 'idOfLastEmail', 5 );
        $this->mySetReturnValue( $Database, 'generateEmailID', 5 );
        $this->mySetReturnValue( $Cms, 'sendWpEmail', true );
        $this->mySetReturnValue( $Security, 'createRandomString', 555 );

        $expirationDate = date( 'Y-m-d H:i:s', strtotime( '+' . 3 . ' days' ) );

        $expectedRecord = array(
            'inviteID'       => 555,
            'inviterID'      => 1,
            'inviteeEmail'   => 'me@test.com',
            'emailID'        => 5,
            'expirationDate' => $expirationDate
        );

        $this->myExpectAtLeastOnce( $Database, 'insertIntoDb', array('hf_invite', $expectedRecord) );

        $UserManager = new HfUserManager( $Database, $Messenger, $UrlFinder, $Cms );
        $UserManager->sendInvitation( 1, 'me@test.com', 3 );
    }

    public function testGettingCurrentUserLogin() {
        $UserManager = $this->Factory->makeUserManager();
        $user        = wp_get_current_user();

        $this->assertEquals( $UserManager->getCurrentUserLogin(), $user->user_login );
    }

    public function testProcessInviteByInviteeEmail() {
        list( $UrlFinder, $Database, $Messenger, $Cms ) = $this->makeUserManagerMockDependencies();

        $RealCms = $this->Factory->makeCms();

        $UserManager = new HfUserManager( $Database, $Messenger, $UrlFinder, $RealCms );

        $user = get_user_by( 'email', 'taken@taken.com' );

        $this->mySetReturnValue( $Database, 'getInviterID', 1 );
        $this->myExpectOnce( $Database, 'createRelationship', array($user->ID, 1) );

        $UserManager->processInvite( 'taken@taken.com', 555 );
    }

    public function testGetFriends() {
        $Database = $this->myMakeMock('HfMysqlDatabase');
        $this->mySetReturnValue($Database, 'getPartners', 'duck');

        $Messenger = $this->myMakeMock('HfMailer');
        $AssetLocator = $this->myMakeMock('HfUrlFinder');
        $Cms = $this->myMakeMock('HfWordPress');

        $UserManager = new HfUserManager($Database, $Messenger, $AssetLocator, $Cms);

        $this->assertEquals('duck', $UserManager->getPartners(1));
    }
}