<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

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

        $this->setReturnValsForFindingStreaks(1);
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

    public function testSendEmailReportRequests() {
        $Factory = new HfFactory();
        $Goals   = $Factory->makeGoals();
        $Goals->sendReportRequestEmails();
    }

    public function testIsAnyGoalDueGetsGoalSubscriptionsFromDatabase() {
        $this->setMockReturns();

        $this->expectOnce( $this->mockDatabase, 'getGoalSubscriptions', array(7) );

        $this->mockedGoals->sendReportRequestEmails();
    }

    public function testGenerateGoalCardUsesDatabaseGetGoalMethod() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks(1);

        $this->expectOnce( $this->mockDatabase, 'getGoal', array(1) );

        $MockSub = $this->makeMockGoalSub();
        $this->mockedGoals->generateGoalCard( $MockSub );
    }

    private function setReturnValsForGoalCardCreation()
    {
        $MockGoal = $this->makeMockGoal();
        $this->setReturnValue($this->mockDatabase, 'getGoal', $MockGoal);

        $MockLevel = $this->makeMockLevel();
        $this->setReturnValue($this->mockDatabase, 'getLevel', $MockLevel);

        $this->setReturnValue($this->mockDatabase, 'daysSinceLastReport', 3.1415);
    }

    public function testUsesHtmlGeneratorToMakeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks(1);
        $MockSub = $this->makeMockGoalSub();

        $this->expectOnce($this->mockMarkupGenerator, 'makeGoalCard', array(
            'Title',
            'Description',
            1,
            3.1415000000000002,
            1,
            2
        ));

        $this->mockedGoals->generateGoalCard($MockSub);
    }

    public function testReturnsHtmlGeneratorMadeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks(1);
        $MockSub = $this->makeMockGoalSub();
        $this->setReturnValue($this->mockMarkupGenerator, 'makeGoalCard', 'goose');

        $result = $this->mockedGoals->generateGoalCard($MockSub);

        $this->assertEquals('goose', $result);
    }

    public function testPassesFalseDaysSinceLastReportWhenMakingGoalCard() {
        $this->setReturnValue($this->mockDatabase,'daysSinceLastReport',false);
        $mockGoal = $this->makeMockGoal();
        $mockSub = $this->makeMockGoalSub();

        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks(1);

        $this->expectOnce($this->mockMarkupGenerator, 'makeGoalCard', array(
            $mockGoal->title,
            $mockGoal->description,
            1,
            false,
            1,
            2
        ));
        $this->mockedGoals->generateGoalCard($mockSub);
    }

    private function makeMockGoal()
    {
        $MockGoal = new stdClass();
        $MockGoal->title = 'Title';
        $MockGoal->description = 'Description';
        return $MockGoal;
    }

    public function testGoalProgressBarGetsAllReports() {
        $this->setReturnValsForGoalCardCreation();
        $this->setReturnValsForFindingStreaks(1);
        $this->expectAtLeastOnce($this->mockDatabase, 'getAllReportsForGoal',array(1,7));
        $this->mockedGoals->goalProgressBar(1,7);
    }

    public function testGoalProgressBarConvertsReportTimeStringsToTimes() {
        $this->setReturnValsForFindingStreaks(1);
        $this->expectAt($this->mockCodeLibrary,'convertStringToTime',0,array('firstSuccess'));
        $this->mockedGoals->goalProgressBar(1,7);
    }

    public function testGoalProgressBarCreatesProgressBar() {
        $this->setReturnValsForFindingStreaks(1);

        $this->expectOnce($this->mockMarkupGenerator,'progressBar',array(.5));
        $this->mockedGoals->goalProgressBar(1,7);
    }

    private function makeMockReports()
    {
        $genericReport = new stdClass();
        $genericReport->isSuccessful = 1;

        $first = clone $genericReport;
        $first->date = 'firstSuccess';
        $last = clone $genericReport;
        $last->date = 'lastSuccess';
        
        $report1 = clone $genericReport;
        $report1->date = '2015-03-04 15:54:32';
        $report2 = clone $genericReport;
        $report2->date = '2015-03-06 15:54:32';
        $report3 = clone $genericReport;
        $report3->isSuccessful = 0;
        $report3->date = '2015-03-07 15:54:32';
        $report4 = clone $genericReport;
        $report4->date = '2015-03-08 15:54:32';
        $report5 = clone $genericReport;
        $report5->date = '2015-03-09 15:54:32';
        
        $reports = Array(
            Array($first, $last),
            Array($report1, $report2, $report3, $report4, $report5),
            Array(),
            Array(),
            Array()
        );
        return $reports;
    }

    private function setReturnValsForFindingStreaks($currentStreak)
    {
        $reports = $this->makeMockReports();
        $this->setReturnValues($this->mockDatabase,'getAllReportsForGoal',$reports);

        $firstSuccess = 0;
        $lastSuccess = $currentStreak * 24 * 60 * 60;

        $time1 = 0 * 24 * 60 * 60;
        $time2 = 1 * 24 * 60 * 60;
        $time3 = 2 * 24 * 60 * 60;
        $time4 = 3 * 24 * 60 * 60;
        $time5 = 4 * 24 * 60 * 60;
        $times = array($firstSuccess, $lastSuccess, $time1, $time2, $time3, $time4, $time5);
        $this->setReturnValues($this->mockCodeLibrary, 'convertStringToTime', $times);
    }

    public function testGoalProgressBarUsesLabel() {
        $this->setReturnValsForFindingStreaks(1);
        $this->expectOnce($this->mockMarkupGenerator,'progressBar',array(.5,'1 day to longest streak'));
        $this->mockedGoals->goalProgressBar(1,7);
    }

    public function testGoalProgressBarLabelsLongestStreak() {
        $this->setReturnValsForFindingStreaks(2);
        $this->expectOnce($this->mockMarkupGenerator,'progressBar',array(1,'Longest streak!'));
        $this->mockedGoals->goalProgressBar(1,7);
    }

    public function testGoalProgressBarLabelsUseProperDayForm() {
        $this->setReturnValsForFindingStreaks(0);
        $this->expectOnce($this->mockMarkupGenerator,'progressBar',array(0,'2 days to longest streak'));
        $this->mockedGoals->goalProgressBar(1,7);
    }

    public function testGoalProgressBarRoundsLabelProperly() {
        $this->setReturnValsForFindingStreaks(0.55);
        $this->expectOnce($this->mockMarkupGenerator,'progressBar',array(0.27500000000000002,'1.5 days to longest streak'));
        $this->mockedGoals->goalProgressBar(1,7);
    }
}