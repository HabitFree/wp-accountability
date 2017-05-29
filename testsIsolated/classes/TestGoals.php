<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase2.php');

class TestGoals extends HfTestCase2 {
    public function testSendReportRequestEmailsChecksThrottling() {
        $this->setMockReturns();

        $this->mockMysqlDatabase->setReturnValue( 'daysSinceLastReport', 2 );
        $this->mockMailer->setReturnValue( 'isThrottled', true );

        $this->mockedGoals->sendReportRequestEmails();

        $this->assertCalled( $this->mockMailer, 'isThrottled' );
    }

    private function setMockReturns() {
        $mockUsers = $this->makeMockUsers();
        $this->mockWordPress->setReturnValue( 'getSubscribedUsers', $mockUsers );

        $mockGoalSubs = $this->makeMockGoalSubs();
        $this->mockMysqlDatabase->setReturnValue( 'getGoalSubscriptions', $mockGoalSubs );

        $mockLevel = $this->makeMockLevel();
        $this->mockMysqlDatabase->setReturnValue( 'getLevel', $mockLevel );

        $this->setReturnValsForFindingStreaks([[0,true], [1,false]]);
    }

    private function makeMockGoalSubs() {
        $mockGoalSub  = $this->makeMockGoalSub();
        $mockGoalSubs = [$mockGoalSub];

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

        $this->mockMysqlDatabase->setReturnValue( 'daysSinceLastEmail', 2 );
        $this->mockMysqlDatabase->setReturnValue( 'daysSinceLastReport', 2 );
        $this->mockMailer->setReturnValue( 'isThrottled', false );

        $this->mockedGoals->sendReportRequestEmails();

        $this->assertCalled( $this->mockMailer, 'sendReportRequestEmail' );
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenReportNotDue() {
        $this->setMockReturns();

        $this->mockMysqlDatabase->setReturnValue( 'daysSinceLastEmail', 2 );
        $this->mockMysqlDatabase->setReturnValue( 'daysSinceLastReport', 0 );

        $this->assertNotCalled( $this->mockMailer, 'sendReportRequestEmail' );

        $this->mockedGoals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenUserThrottled() {
        $this->setMockReturns();

        $this->mockMysqlDatabase->setReturnValue( 'daysSinceLastEmail', 2 );
        $this->mockMysqlDatabase->setReturnValue( 'daysSinceLastReport', 5 );
        $this->mockMailer->setReturnValue( 'IsThrottled', true );

        $this->assertNotCalled( $this->mockMailer, 'sendReportRequestEmail' );

        $this->mockedGoals->sendReportRequestEmails();
    }

    public function testCurrentLevelTarget() {
        $mockLevel = $this->makeMockLevel();
        $this->mockMysqlDatabase->setReturnValue( 'getLevel', $mockLevel );

        $target = $this->mockedGoals->currentLevelTarget( 5 );

        $this->assertEquals( $target, 14 );
    }

    public function testGetGoalTitle() {
        $mockGoal        = new stdClass();
        $mockGoal->title = 'Eat durian';
        $this->mockMysqlDatabase->setReturnValue( 'getGoal', $mockGoal );

        $goalTitle = $this->mockedGoals->getGoalTitle( 1 );

        $this->assertEquals( $mockGoal->title, $goalTitle );
    }

    public function testRecordAccountabilityReport() {
        $this->mockedGoals->recordAccountabilityReport( 1, 2, 3, 4 );

        $this->assertCalledWith( $this->mockMysqlDatabase, 'recordAccountabilityReport', 1, 2, 3, 4);
    }

    public function testGetGoalSubscriptions() {
        $expected = [1, 2, 3, 4];

        $this->mockMysqlDatabase->setReturnValue( 'getGoalSubscriptions', $expected );

        $actual = $this->mockedGoals->getGoalSubscriptions( 1 );

        $this->assertEquals( $expected, $actual );
    }

    public function testIsAnyGoalDueGetsGoalSubscriptionsFromDatabase() {
        $this->setMockReturns();

        $this->mockedGoals->sendReportRequestEmails();

        $this->assertCalledWith( $this->mockMysqlDatabase, 'getGoalSubscriptions', 7);
    }

    public function testgoalCardUsesDatabaseGetGoalMethod() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks([[0,true], [1,false]]);

        $MockSub = $this->makeMockGoalSub();
        $this->mockedGoals->goalCard( $MockSub );

        $this->assertCalledWith( $this->mockMysqlDatabase, 'getGoal', 1);
    }

    private function setReturnValsForGoalCardCreation()
    {
        $mockSub = $this->makeMockGoalSub();
        $this->mockMysqlDatabase->setReturnValue( "getGoalSubscriptions", [$mockSub] );

        $mockGoal = $this->makeMockGoal();
        $this->mockMysqlDatabase->setReturnValue( 'getGoal', $mockGoal);

        $mockLevel = $this->makeMockLevel();
        $this->mockMysqlDatabase->setReturnValue( 'getLevel', $mockLevel);

        $this->mockMysqlDatabase->setReturnValue( 'daysSinceLastReport', 3.1415);
        $this->mockHealth->setReturnValue( 'getHealth', .5);
        $this->mockStreaks->setReturnValue( "streaks", [5] );
    }

    public function testUsesHtmlGeneratorToMakeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks();
        $MockSub = $this->makeMockGoalSub();

        $this->mockedGoals->goalCard($MockSub);

        $this->assertCalledWith($this->mockHtmlGenerator, 'goalCard',
            1,
            'Title',
            3.1415000000000002,
            0,
            0,
            .5
        );
    }

    public function testReturnsHtmlGeneratorMadeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks([]);
        $MockSub = $this->makeMockGoalSub();
        $this->mockHtmlGenerator->setReturnValue( 'goalCard', 'goose');

        $result = $this->mockedGoals->goalCard($MockSub);

        $this->assertEquals('goose', $result);
    }

    private function makeMockGoal()
    {
        $MockGoal = new stdClass();
        $MockGoal->title = 'Title';
        $MockGoal->description = 'Description';
        return $MockGoal;
    }

    private function setReturnValsForFindingStreaks()
    {
        $this->mockStreaks->setReturnValues('streaks', [[0], [0]]);
    }

    private function daysInSeconds($days)
    {
        $secondsInADay = 24 * 60 * 60;
        $time1 = $days * $secondsInADay;
        return $time1;
    }

    private function setMockReportReturnVals($reportInfos, $reports = [])
    {
        $info = array_shift($reportInfos);
        $mockReport = $this->makeMockReportFromInfo($info);
        $reports[] = $mockReport;

        if (empty($reportInfos)) {
            $this->mockMysqlDatabase->setReturnValue( 'getAllReportsForGoal', $reports);
        } else {
            $this->setMockReportReturnVals($reportInfos, $reports);
        }
    }

    private function mockReport($isSuccessful, $date)
    {
        $report = new stdClass();
        $report->isSuccessful = ($isSuccessful) ? 1 : 0;
        $report->date = $date;
        return $report;
    }

    private function setMockTimeReturnVals($reportInfos, $times = array())
    {
        $info = array_shift($reportInfos);
        $dayOfReport = $info[0];
        $time = $this->daysInSeconds($dayOfReport);
        $times[] = $time;

        if (empty($reportInfos)) {
            $this->mockCodeLibrary->setReturnValues( 'convertStringToTime', $times);
        } else {
            $this->setMockTimeReturnVals($reportInfos, $times);
        }
    }

    private function makeMockReportFromInfo($info)
    {
        $dayOfReport = $info[0];
        $isSuccess = $info[1];
        $time = $this->daysInSeconds($dayOfReport);
        $dateString = date('Y-m-d H:i:s', $time);
        return $this->mockReport($isSuccess, $dateString);
    }

    public function testGivesHtmlGeneratorCurrentStreak() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks();
        $MockSub = $this->makeMockGoalSub();

        $currentStreak = 0;
        $daysSinceLastReport = 3.1415000000000002;

        $this->mockedGoals->goalCard($MockSub);

        $this->assertCalledWith($this->mockHtmlGenerator, 'goalCard',
            1,
            'Title',
            $daysSinceLastReport,
            $currentStreak,
            0,
            .5
        );
    }

    public function testFindsStreaksWhenMakingGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->mockStreaks->setReturnValues('streaks', [[0], [0]]);
        $mockSub = $this->makeMockGoalSub();
        $this->mockedGoals->goalCard($mockSub);
        $this->assertCalledWith( $this->mockStreaks,'streaks', 1, 7 );
    }

    public function testUsesHealthToMakeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks();
        $MockSub = $this->makeMockGoalSub();

        $this->mockedGoals->goalCard($MockSub);

        $this->assertCalledWith($this->mockHtmlGenerator, 'goalCard',
            1,
            'Title',
            3.1415000000000002,
            0,
            0,
            .5
        );
    }

    public function testGetGoalCardsData() {
        $this->setReturnValsForGoalCardCreation();

        $this->mockedGoals->getGoalCardsData( 0 );

        $this->assertCalledWith( $this->mockMysqlDatabase, "getGoalSubscriptions", 0 );
    }

    public function testGetsHealth() {
        $this->setReturnValsForGoalCardCreation();

        $this->mockedGoals->getGoalCardsData( 0 );

        $this->assertCalled( $this->mockHealth, "getHealth" );
    }

    public function testsReturnsData() {
        $this->setReturnValsForGoalCardCreation();

        $result = $this->mockedGoals->getGoalCardsData( 0 );

        $this->assertTrue( is_array( $result ) );
    }
}