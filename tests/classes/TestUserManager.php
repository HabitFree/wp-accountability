<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestUserManager extends HfTestCase {

    // Helper Functions

    // Tests

    public function testEmailInviteSendingUsingMocks() {
        $this->setReturnValue( $this->MockMessenger, 'generateSecureEmailId', 555 );
        $this->setReturnValue( $this->MockDatabase, 'generateEmailID', 5 );

        $result = $this->UserManagerWithMockedDependencies->sendInvitation( 1, 'me@test.com', 3 );

        $this->assertEquals( $result, 555 );
    }

    public function testInviteStorageInInviteTableUsingMocks() {
        $expirationTime = strtotime( '+' . 3 . ' days' );
        $expirationDate = date( 'Y-m-d H:i:s', $expirationTime );

        $this->setReturnValue( $this->MockDatabase, 'idOfLastEmail', 5 );
        $this->setReturnValue( $this->MockDatabase, 'generateEmailID', 5 );
        $this->setReturnValue( $this->MockCms, 'sendEmail', true );
        $this->setReturnValue( $this->MockSecurity, 'createRandomString', 555 );
        $this->setReturnValue( $this->MockCodeLibrary, 'convertStringToTime', $expirationTime );

        $expectedRecord = array(
            'inviteID'       => 555,
            'inviterID'      => 1,
            'inviteeEmail'   => 'me@test.com',
            'emailID'        => 5,
            'expirationDate' => $expirationDate
        );

        $this->expectAtLeastOnce( $this->MockDatabase, 'insertIntoDb', array('hf_invite', $expectedRecord) );

        $UserManager = new HfUserManager(
            $this->MockDatabase,
            $this->MailerWithMockedDependencies,
            $this->MockAssetLocator,
            $this->MockCms,
            $this->MockCodeLibrary
        );

        $UserManager->sendInvitation( 1, 'me@test.com', 3 );
    }

    public function testGettingCurrentUserLogin() {
        $UserManager = $this->Factory->makeUserManager();
        $user        = wp_get_current_user();

        $this->assertEquals( $UserManager->getCurrentUserLogin(), $user->user_login );
    }

    public function testProcessInviteByInviteeEmail() {
        $UserManager = new HfUserManager(
            $this->MockDatabase,
            $this->MockMessenger,
            $this->MockAssetLocator,
            $this->Factory->makeCms(),
            $this->MockCodeLibrary
        );

        $user = get_user_by( 'email', 'taken@taken.com' );

        $this->setReturnValue( $this->MockDatabase, 'getInviterID', 1 );
        $this->expectOnce( $this->MockDatabase, 'createRelationship', array($user->ID, 1) );

        $UserManager->processInvite( 'taken@taken.com', 555 );
    }

    public function testGetFriends() {
        $this->setReturnValue( $this->MockDatabase, 'getPartners', 'duck' );

        $this->assertEquals( 'duck', $this->UserManagerWithMockedDependencies->getPartners( 1 ) );
    }

    public function testSendInvitationUsesCodeLibraryToConvertStringToTime() {
        $this->expectOnce( $this->MockCodeLibrary, 'convertStringToTime', array('+7 days') );

        $this->UserManagerWithMockedDependencies->sendInvitation( 1, 'me@my.com' );
    }

    public function testProcessInviteDeletesExpiredInvites() {
        $this->expectOnce($this->MockMessenger, 'deleteExpiredInvites');

        $this->UserManagerWithMockedDependencies->processInvite('', '');
    }

    public function testDeleteRelationship() {
        $this->expectOnce($this->MockDatabase, 'deleteRelationship', array('dog', 'cat'));
        $this->UserManagerWithMockedDependencies->deleteRelationship('dog', 'cat');
    }

    public function testUserManagerAddsDefaultSubWhenProcessingNewUser() {
        $this->expectOnce($this->MockDatabase, 'setDefaultGoalSubscription', array(9));
        $this->UserManagerWithMockedDependencies->processNewUser(9);
    }
}