<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestUserManager extends HfTestCase {

    // Helper Functions

    // Tests

    public function testEmailInviteSendingUsingMocks() {
        $this->setReturnValue( $this->MockMailer, 'generateSecureEmailId', 555 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'generateEmailID', 5 );

        $result = $this->MockedUserManager->sendInvitation( 1, 'me@test.com', 3 );

        $this->assertEquals( $result, 555 );
    }

    public function testGettingCurrentUserLogin() {
        $UserManager = $this->Factory->makeUserManager();
        $user        = wp_get_current_user();

        $this->assertEquals( $UserManager->getCurrentUserLogin(), $user->user_login );
    }

    public function testGetFriends() {
        $this->setReturnValue( $this->MockMysqlDatabase, 'getPartners', 'duck' );

        $this->assertEquals( 'duck', $this->MockedUserManager->getPartners( 1 ) );
    }

    public function testSendInvitationUsesCodeLibraryToConvertStringToTime() {
        $this->expectOnce( $this->MockPhpLibrary, 'convertStringToTime', array('+7 days') );

        $this->MockedUserManager->sendInvitation( 1, 'me@my.com' );
    }

    public function testProcessInviteDeletesExpiredInvites() {
        $this->expectOnce($this->MockMailer, 'deleteExpiredInvites');

        $this->MockedUserManager->processInvite('', '');
    }

    public function testDeleteRelationship() {
        $this->expectOnce($this->MockMysqlDatabase, 'deleteRelationship', array('dog', 'cat'));
        $this->MockedUserManager->deleteRelationship('dog', 'cat');
    }

    public function testUserManagerAddsDefaultSubWhenProcessingNewUser() {
        $this->expectOnce($this->MockMysqlDatabase, 'setDefaultGoalSubscription', array(9));
        $this->MockedUserManager->processNewUser(9);
    }

    public function testUserManagerUsesDatabaseToRecordInvite() {
        $this->expectOnce($this->MockMysqlDatabase, 'recordInvite');
        $this->MockedUserManager->sendInvitation(1, 'test@test.com');
    }

    public function testProcessAllUsersDoesntSendEmails() {
        $users = $this->makeMockUsers();
        $this->setReturnValue($this->mockCms, 'getUsers', $users);

        $this->expectNever($this->MockMailer, 'sendEmailToUser');
        $this->MockedUserManager->processAllUsers();
    }

    public function testProcessAllUsersGetsUsers() {
        $users = $this->makeMockUsers();
        $this->setReturnValue($this->mockCms, 'getUsers', $users);

        $this->expectOnce($this->mockCms, 'getUsers');
        $this->MockedUserManager->processAllUsers();
    }

    public function testProcessAllUsersSetsDefaultGoalSubscriptions() {
        $users = $this->makeMockUsers();
        $this->setReturnValue($this->mockCms, 'getUsers', $users);

        $this->expectAtLeastOnce($this->MockMysqlDatabase, 'setDefaultGoalSubscription', array(7));
        $this->MockedUserManager->processAllUsers();
    }
}