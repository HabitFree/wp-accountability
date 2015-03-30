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
        $this->setReturnValue($this->mockDatabase, 'getAllReportsForGoal', array());
    }

    public function testUsesHtmlGeneratorToMakeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $MockSub = $this->makeMockGoalSub();

        $this->expectOnce($this->mockMarkupGenerator, 'makeGoalCard', array(
            'Title',
            'Description',
            1,
            3.1415000000000002,
            2,
            'Title',
            0,
            14,
            ''
        ));

        $this->mockedGoals->generateGoalCard($MockSub);
    }

    public function testReturnsHtmlGeneratorMadeGoalCard() {
        $this->setReturnValsForGoalCardCreation();
        $MockSub = $this->makeMockGoalSub();
        $this->setReturnValue($this->mockMarkupGenerator, 'makeGoalCard', 'goose');

        $result = $this->mockedGoals->generateGoalCard($MockSub);

        $this->assertEquals('goose', $result);
    }

    public function testPassesFalseDaysSinceLastReportWhenMakingGoalCard() {
        $this->setReturnValue($this->mockDatabase,'daysSinceLastReport',false);
        $mockGoal = $this->makeMockGoal();
        $mockLevel = $this->makeMockLevel();
        $mockSub = $this->makeMockGoalSub();

        $this->setReturnValsForGoalCardCreation();

        $this->expectOnce($this->mockMarkupGenerator, 'makeGoalCard', array(
            $mockGoal->title,
            $mockGoal->description,
            1,
            false,
            $mockLevel->levelID,
            $mockLevel->title,
            null,
            14.0,
            null
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

    public function testGoalProgressBarGetsCurrentStreak() {
        $this->setReturnValsForGoalCardCreation();
        $this->expectOnce($this->mockDatabase,'timeOfFirstSuccess');
        $this->mockedGoals->goalProgressBar(1,7);
    }

    public function testGoalProgressBarGetsAllReports() {
        $this->setReturnValsForGoalCardCreation();
        $this->expectOnce($this->mockDatabase, 'getAllReportsForGoal',array(1,7));
        $this->mockedGoals->goalProgressBar(1,7);
    }

    public function testGoalProgressBarConvertsReportTimeStringsToTimes() {
        $reports = $this->makeMockReports();
        $this->setReturnValue($this->mockDatabase,'getAllReportsForGoal',$reports);
        $this->expectAt($this->mockCodeLibrary,'convertStringToTime',0,array('2015-03-04 15:54:32'));
        $this->mockedGoals->goalProgressBar(1,7);
    }

    public function testGoalProgressBarCreatesProgressBar() {
        $reports = $this->makeMockReports();
        $this->setReturnValue($this->mockDatabase,'getAllReportsForGoal',$reports);

        $this->setReturnValsForProgressBarCreation();

        $this->expectOnce($this->mockMarkupGenerator,'progressBar',array(.5));
        $this->mockedGoals->goalProgressBar(1,7);
    }

    private function makeMockReports()
    {
        $genericReport = new stdClass();
        $genericReport->isSuccessful = 1;
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
        $reports = Array($report1, $report2, $report3, $report4, $report5);
        return $reports;
    }

    private function setReturnValsForProgressBarCreation()
    {
        $time1 = 0 * 24 * 60 * 60;
        $time2 = 1 * 24 * 60 * 60;
        $time3 = 2 * 24 * 60 * 60;
        $time4 = 3 * 24 * 60 * 60;
        $time5 = 4 * 24 * 60 * 60;
        $times = array($time1, $time2, $time3, $time4, $time5);
        $this->setReturnValues($this->mockCodeLibrary, 'convertStringToTime', $times);

        $firstSuccess = 0;
        $lastSuccess = 24 * 60 * 60;
        $this->setReturnValue($this->mockDatabase,'timeOfFirstSuccess',$firstSuccess);
        $this->setReturnValue($this->mockDatabase,'timeOfLastSuccess',$lastSuccess);
    }

    public function testGoalProgressBarPreparesLabel() {
        $reports = $this->makeMockReports();
        $this->setReturnValue($this->mockDatabase,'getAllReportsForGoal',$reports);

        $this->setReturnValsForProgressBarCreation();

        $this->expectOnce($this->mockCms,'prepareQuery',array('%d / %d', array(1,2)));
        $this->mockedGoals->goalProgressBar(1,7);
    }
}