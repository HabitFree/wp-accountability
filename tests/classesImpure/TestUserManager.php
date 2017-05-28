<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestUserManager extends HfTestCase {

    // Helper Functions

    // Tests

    public function testEmailInviteSendingUsingMocks() {
        $this->setReturnValue( $this->mockMessenger, 'generateSecureEmailId', 555 );
        $this->setReturnValue( $this->mockDatabase, 'generateEmailID', 5 );

        $result = $this->mockedUserManager->sendInvitation( 1, 'me@test.com', 3 );

        $this->assertEquals( $result, 555 );
    }

    public function testGettingCurrentUserLogin() {
        $UserManager = $this->factory->makeUserManager();
        $user        = wp_get_current_user();

        $this->assertEquals( $UserManager->getCurrentUserLogin(), $user->user_login );
    }

    public function testGetFriends() {
        $this->setReturnValue( $this->mockDatabase, 'getPartners', 'duck' );

        $this->assertEquals( 'duck', $this->mockedUserManager->getPartners( 1 ) );
    }

    public function testSendInvitationUsesCodeLibraryToConvertStringToTime() {
        $this->expectOnce( $this->mockCodeLibrary, 'convertStringToTime', array('+7 days') );

        $this->mockedUserManager->sendInvitation( 1, 'me@my.com' );
    }

    public function testProcessInviteDeletesExpiredInvites() {
        $this->expectOnce($this->mockMessenger, 'deleteExpiredInvites');

        $this->mockedUserManager->processInvite('', '');
    }

    public function testDeleteRelationship() {
        $this->expectOnce($this->mockDatabase, 'deleteRelationship', array('dog', 'cat'));
        $this->mockedUserManager->deleteRelationship('dog', 'cat');
    }

    public function testUserManagerAddsDefaultSubWhenProcessingNewUser() {
        $this->expectOnce($this->mockDatabase, 'setDefaultGoalSubscription', array(9));
        $this->mockedUserManager->processNewUser(9);
    }

    public function testUserManagerUsesDatabaseToRecordInvite() {
        $this->expectOnce($this->mockDatabase, 'recordInvite');
        $this->mockedUserManager->sendInvitation(1, 'test@test.com');
    }

    public function testProcessAllUsersDoesntSendEmails() {
        $users = $this->makeMockUsers();
        $this->setReturnValue($this->mockCms, 'getUsers', $users);

        $this->expectNever($this->mockMessenger, 'sendEmailToUser');
        $this->mockedUserManager->processAllUsers();
    }

    public function testProcessAllUsersGetsUsers() {
        $users = $this->makeMockUsers();
        $this->setReturnValue($this->mockCms, 'getUsers', $users);

        $this->expectOnce($this->mockCms, 'getUsers');
        $this->mockedUserManager->processAllUsers();
    }

    public function testProcessAllUsersSetsDefaultGoalSubscriptions() {
        $users = $this->makeMockUsers();
        $this->setReturnValue($this->mockCms, 'getUsers', $users);

        $this->expectAtLeastOnce($this->mockDatabase, 'setDefaultGoalSubscription', array(7));
        $this->mockedUserManager->processAllUsers();
    }

    public function testUserManagerSendsWelcomeEmailWhenProcessingNewUser() {
        $message = "<p>Welcome to HabitFree! You can <a href=''>edit your account settings by clicking here</a>.</p>";
        $this->expectOnce($this->mockMessenger,'sendEmailToUser',array(7, 'Welcome!',$message));
        $this->mockedUserManager->processNewUser(7);
    }
}