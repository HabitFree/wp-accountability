<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestUserManager extends HfTestCase {

    // Helper Functions

    // Tests

    public function testEmailInviteSendingUsingMocks() {
        $this->setReturnValue( $this->MockMessenger, 'generateSecureEmailId', 555 );
        $this->setReturnValue( $this->MockDatabase, 'generateEmailID', 5 );

        $result = $this->MockedUserManager->sendInvitation( 1, 'me@test.com', 3 );

        $this->assertEquals( $result, 555 );
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

        $this->assertEquals( 'duck', $this->MockedUserManager->getPartners( 1 ) );
    }

    public function testSendInvitationUsesCodeLibraryToConvertStringToTime() {
        $this->expectOnce( $this->MockCodeLibrary, 'convertStringToTime', array('+7 days') );

        $this->MockedUserManager->sendInvitation( 1, 'me@my.com' );
    }

    public function testProcessInviteDeletesExpiredInvites() {
        $this->expectOnce($this->MockMessenger, 'deleteExpiredInvites');

        $this->MockedUserManager->processInvite('', '');
    }

    public function testDeleteRelationship() {
        $this->expectOnce($this->MockDatabase, 'deleteRelationship', array('dog', 'cat'));
        $this->MockedUserManager->deleteRelationship('dog', 'cat');
    }

    public function testUserManagerAddsDefaultSubWhenProcessingNewUser() {
        $this->expectOnce($this->MockDatabase, 'setDefaultGoalSubscription', array(9));
        $this->MockedUserManager->processNewUser(9);
    }

    public function testUserManagerUsesDatabaseToRecordInvite() {
        $this->expectOnce($this->MockDatabase, 'recordInvite');
        $this->MockedUserManager->sendInvitation(1, 'test@test.com');
    }

    public function testProcessAllUsersDoesntSendEmails() {
        $users = $this->makeMockUsers();
        $this->setReturnValue($this->MockCms, 'getUsers', $users);

        $this->expectNever($this->MockMessenger, 'sendEmailToUser');
        $this->MockedUserManager->processAllUsers();
    }

    public function testProcessAllUsersGetsUsers() {
        $users = $this->makeMockUsers();
        $this->setReturnValue($this->MockCms, 'getUsers', $users);

        $this->expectOnce($this->MockCms, 'getUsers');
        $this->MockedUserManager->processAllUsers();
    }

    public function testProcessAllUsersSetsDefaultGoalSubscriptions() {
        $users = $this->makeMockUsers();
        $this->setReturnValue($this->MockCms, 'getUsers', $users);

        $this->expectAtLeastOnce($this->MockDatabase, 'setDefaultGoalSubscription', array(7));
        $this->MockedUserManager->processAllUsers();
    }
}