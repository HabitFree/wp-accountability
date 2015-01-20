<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGoals extends HfTestCase {
    public function testSendReportRequestEmailsChecksThrottling() {
        $this->setMockReturns();

        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceLastReport', 2 );
        $this->setReturnValue( $this->MockMailer, 'isThrottled', true );

        $this->expectAtLeastOnce( $this->MockMailer, 'isThrottled' );

        $this->MockedGoals->sendReportRequestEmails();
    }

    private function setMockReturns() {
        $mockUsers = $this->makeMockUsers();
        $this->setReturnValue( $this->MockWordPress, 'getSubscribedUsers', $mockUsers );

        $mockGoalSubs = $this->makeMockGoalSubs();
        $this->setReturnValue( $this->MockMysqlDatabase, 'getGoalSubscriptions', $mockGoalSubs );

        $mockLevel = $this->makeMockLevel();
        $this->setReturnValue( $this->MockMysqlDatabase, 'getLevel', $mockLevel );
    }

    private function makeMockGoalSubs() {
        $mockGoalSub  = $this->makeMockGoalSub();
        $mockGoalSubs = array($mockGoalSub);

        return $mockGoalSubs;
    }

    private function makeMockLevel() {
        $mockLevel                = new stdClass();
        $mockLevel->levelID       = 2;
        $mockLevel->title         = 'Title';
        $mockLevel->target        = 14;
        $mockLevel->emailInterval = 1;

        return $mockLevel;
    }

    private function makeMockGoalSub() {
        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSub->userID = 7;

        return $mockGoalSub;
    }

    public function testSendReportRequestEmailsSendsEmailWhenReportDue() {
        $this->setMockReturns();

        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceLastEmail', 2 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceLastReport', 2 );
        $this->setReturnValue( $this->MockMailer, 'isThrottled', false );

        $this->expectAtLeastOnce( $this->MockMailer, 'sendReportRequestEmail' );

        $this->MockedGoals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenReportNotDue() {
        $this->setMockReturns();

        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceLastEmail', 2 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceLastReport', 0 );

        $this->expectNever( $this->MockMailer, 'sendReportRequestEmail' );

        $this->MockedGoals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenUserThrottled() {
        $this->setMockReturns();

        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceLastEmail', 2 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceLastReport', 5 );
        $this->setReturnValue( $this->MockMailer, 'IsThrottled', true );

        $this->expectNever( $this->MockMailer, 'sendReportRequestEmail' );

        $this->MockedGoals->sendReportRequestEmails();
    }

    public function testCurrentLevelTarget() {
        $mockLevel = $this->makeMockLevel();
        $this->setReturnValue( $this->MockMysqlDatabase, 'getLevel', $mockLevel );

        $target = $this->MockedGoals->currentLevelTarget( 5 );

        $this->assertEquals( $target, 14 );
    }

    public function testGetGoalTitle() {
        $mockGoal        = new stdClass();
        $mockGoal->title = 'Eat durian';
        $this->setReturnValue( $this->MockMysqlDatabase, 'getGoal', $mockGoal );

        $goalTitle = $this->MockedGoals->getGoalTitle( 1 );

        $this->assertEquals( $mockGoal->title, $goalTitle );
    }

    public function testRecordAccountabilityReport() {
        $this->expectOnce( $this->MockMysqlDatabase, 'recordAccountabilityReport', array(1, 2, 3, 4) );

        $this->MockedGoals->recordAccountabilityReport( 1, 2, 3, 4 );
    }

    public function testGetGoalSubscriptions() {
        $expected = array(1, 2, 3, 4);

        $this->setReturnValue( $this->MockMysqlDatabase, 'getGoalSubscriptions', $expected );

        $actual = $this->MockedGoals->getGoalSubscriptions( 1 );

        $this->assertEquals( $expected, $actual );
    }

    public function testSendEmailReportRequests() {
        $Factory = new HfFactory();
        $Goals   = $Factory->makeGoals();
        $Goals->sendReportRequestEmails();
    }

    public function testIsAnyGoalDueGetsGoalSubscriptionsFromDatabase() {
        $this->setMockReturns();

        $this->expectOnce( $this->MockMysqlDatabase, 'getGoalSubscriptions', array(7) );

        $this->MockedGoals->sendReportRequestEmails();
    }

    public function testGenerateGoalCardUsesDatabaseGetGoalMethod() {
        $this->setReturnValsForGoalCardCreation();

        $this->expectOnce( $this->MockMysqlDatabase, 'getGoal', array(1) );

        $MockSub = $this->makeMockGoalSub();
        $this->MockedGoals->generateGoalCard( $MockSub );
    }

    private function setReturnValsForGoalCardCreation()
    {
        $MockGoal = new stdClass();
        $MockGoal->title = 'Title';
        $MockGoal->description = 'Description';
        $this->setReturnValue($this->MockMysqlDatabase, 'getGoal', $MockGoal);

        $MockLevel = $this->makeMockLevel();
        $this->setReturnValue($this->MockMysqlDatabase, 'getLevel', $MockLevel);

        $this->setReturnValue($this->MockMysqlDatabase, 'daysSinceLastReport', 3.1415);
    }

    public function testUsesHtmlGeneratorToMakeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $MockSub = $this->makeMockGoalSub();

        $this->expectOnce($this->mockMarkupGenerator, 'makeGoalCard', array(
            'Title',
            'Description',
            1,
            3,
            2,
            'Title',
            0,
            14,
            ''
        ));

        $this->MockedGoals->generateGoalCard($MockSub);
    }

    public function testReturnsHtmlGeneratorMadeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $MockSub = $this->makeMockGoalSub();
        $this->setReturnValue($this->mockMarkupGenerator, 'makeGoalCard', 'goose');

        $result = $this->MockedGoals->generateGoalCard($MockSub);

        $this->assertEquals('goose', $result);
    }
} 