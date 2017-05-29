<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestGoals extends HfTestCase {
    public function testSendReportRequestEmailsChecksThrottling() {
        $this->setMockReturns();

        $this->setReturnValue( $this->mockDatabase, 'daysSinceLastReport', 2 );
        $this->setReturnValue( $this->mockMessenger, 'isThrottled', true );

        $this->expectAtLeastOnce( $this->mockMessenger, 'isThrottled' );

        $this->mockedGoals->sendReportRequestEmails();
    }

    private function setMockReturns() {
        $mockUsers = $this->makeMockUsers();
        $this->setReturnValue( $this->mockCms, 'getSubscribedUsers', $mockUsers );

        $mockGoalSubs = $this->makeMockGoalSubs();
        $this->setReturnValue( $this->mockDatabase, 'getGoalSubscriptions', $mockGoalSubs );

        $mockLevel = $this->makeMockLevel();
        $this->setReturnValue( $this->mockDatabase, 'getLevel', $mockLevel );

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

        $this->setReturnValue( $this->mockDatabase, 'daysSinceLastEmail', 2 );
        $this->setReturnValue( $this->mockDatabase, 'daysSinceLastReport', 2 );
        $this->setReturnValue( $this->mockMessenger, 'isThrottled', false );

        $this->expectAtLeastOnce( $this->mockMessenger, 'sendReportRequestEmail' );

        $this->mockedGoals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenReportNotDue() {
        $this->setMockReturns();

        $this->setReturnValue( $this->mockDatabase, 'daysSinceLastEmail', 2 );
        $this->setReturnValue( $this->mockDatabase, 'daysSinceLastReport', 0 );

        $this->expectNever( $this->mockMessenger, 'sendReportRequestEmail' );

        $this->mockedGoals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenUserThrottled() {
        $this->setMockReturns();

        $this->setReturnValue( $this->mockDatabase, 'daysSinceLastEmail', 2 );
        $this->setReturnValue( $this->mockDatabase, 'daysSinceLastReport', 5 );
        $this->setReturnValue( $this->mockMessenger, 'IsThrottled', true );

        $this->expectNever( $this->mockMessenger, 'sendReportRequestEmail' );

        $this->mockedGoals->sendReportRequestEmails();
    }

    public function testCurrentLevelTarget() {
        $mockLevel = $this->makeMockLevel();
        $this->setReturnValue( $this->mockDatabase, 'getLevel', $mockLevel );

        $target = $this->mockedGoals->currentLevelTarget( 5 );

        $this->assertEquals( $target, 14 );
    }

    public function testGetGoalTitle() {
        $mockGoal        = new stdClass();
        $mockGoal->title = 'Eat durian';
        $this->setReturnValue( $this->mockDatabase, 'getGoal', $mockGoal );

        $goalTitle = $this->mockedGoals->getGoalTitle( 1 );

        $this->assertEquals( $mockGoal->title, $goalTitle );
    }

    public function testRecordAccountabilityReport() {
        $this->expectOnce( $this->mockDatabase, 'recordAccountabilityReport', array(1, 2, 3, 4) );

        $this->mockedGoals->recordAccountabilityReport( 1, 2, 3, 4 );
    }

    public function testGetGoalSubscriptions() {
        $expected = array(1, 2, 3, 4);

        $this->setReturnValue( $this->mockDatabase, 'getGoalSubscriptions', $expected );

        $actual = $this->mockedGoals->getGoalSubscriptions( 1 );

        $this->assertEquals( $expected, $actual );
    }

    public function testIsAnyGoalDueGetsGoalSubscriptionsFromDatabase() {
        $this->setMockReturns();

        $this->expectOnce( $this->mockDatabase, 'getGoalSubscriptions', [7]);

        $this->mockedGoals->sendReportRequestEmails();
    }

    public function testgoalCardUsesDatabaseGetGoalMethod() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks([[0,true], [1,false]]);

        $this->expectOnce( $this->mockDatabase, 'getGoal', [1]);

        $MockSub = $this->makeMockGoalSub();
        $this->mockedGoals->goalCard( $MockSub );
    }

    private function setReturnValsForGoalCardCreation()
    {
        $MockGoal = $this->makeMockGoal();
        $this->setReturnValue($this->mockDatabase, 'getGoal', $MockGoal);

        $MockLevel = $this->makeMockLevel();
        $this->setReturnValue($this->mockDatabase, 'getLevel', $MockLevel);

        $this->setReturnValue($this->mockDatabase, 'daysSinceLastReport', 3.1415);
        $this->setReturnValue($this->mockHealth, 'getHealth', .5);
    }

    public function testUsesHtmlGeneratorToMakeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks();
        $MockSub = $this->makeMockGoalSub();

        $this->expectOnce($this->mockMarkupGenerator, 'goalCard', [
            1,
            'Title',
            3.1415000000000002,
            0,
            0,
            .5
        ]);

        $this->mockedGoals->goalCard($MockSub);
    }

    public function testReturnsHtmlGeneratorMadeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks([]);
        $MockSub = $this->makeMockGoalSub();
        $this->setReturnValue($this->mockMarkupGenerator, 'goalCard', 'goose');

        $result = $this->mockedGoals->goalCard($MockSub);

        $this->assertEquals('goose', $result);
    }

    public function testPassesFalseDaysSinceLastReportWhenMakingGoalCard() {
        $this->setReturnValue($this->mockDatabase,'daysSinceLastReport',false);
        $mockGoal = $this->makeMockGoal();
        $mockSub = $this->makeMockGoalSub();

        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks(array());

        $this->expectOnce($this->mockMarkupGenerator, 'goalCard', [
            1,
            $mockGoal->title,
            false,
            0
        ]);
        $this->mockedGoals->goalCard($mockSub);
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
        $this->setReturnValues($this->mockStreaks,'streaks', [[0], [0]]);
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
            $this->setReturnValue($this->mockDatabase, 'getAllReportsForGoal', $reports);
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
            $this->setReturnValues($this->mockCodeLibrary, 'convertStringToTime', $times);
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
        $this->expectOnce($this->mockMarkupGenerator, 'goalCard', [
            1,
            'Title',
            $daysSinceLastReport,
            $currentStreak,
            0,
            .5
        ]);

        $this->mockedGoals->goalCard($MockSub);
    }

    public function testFindsStreaksWhenMakingGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValues($this->mockStreaks,'streaks', [[0], [0]]);
        $this->expectAtLeastOnce($this->mockStreaks,'streaks', [1,7]);
        $mockSub = $this->makeMockGoalSub();
        $this->mockedGoals->goalCard($mockSub);
    }

    public function testUsesHealthToMakeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks();
        $MockSub = $this->makeMockGoalSub();

        $this->expectOnce($this->mockMarkupGenerator, 'goalCard', [
            1,
            'Title',
            3.1415000000000002,
            0,
            0,
            .5
        ]);

        $this->mockedGoals->goalCard($MockSub);
    }
}