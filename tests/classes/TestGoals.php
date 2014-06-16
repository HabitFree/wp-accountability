<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGoals extends HfTestCase {
    // Helper Functions

    private function makeMockDependencies() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $Database      = $this->myMakeMock( 'HfMysqlDatabase' );

        return array($Messenger, $WebsiteApi, $HtmlGenerator, $Database);
    }

    // Tests

    public function testSendReportRequestEmailsChecksThrottling() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->myMakeMock( 'HfMysqlDatabase' );
        $CodeLibrary   = $this->myMakeMock( 'HfPhpLibrary' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->mySetReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->mySetReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->mySetReturnValue( $DbConnection, 'level', $mockLevel );

        $this->mySetReturnValue( $DbConnection, 'daysSinceLastReport', 2 );
        $this->mySetReturnValue( $Messenger, 'isThrottled', true );

        $this->myExpectAtLeastOnce( $Messenger, 'isThrottled' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );
        $Goals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsSendsEmailWhenReportDue() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->myMakeMock( 'HfMysqlDatabase' );
        $CodeLibrary   = $this->myMakeMock( 'HfPhpLibrary' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->mySetReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->mySetReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->mySetReturnValue( $DbConnection, 'level', $mockLevel );

        $this->mySetReturnValue( $DbConnection, 'daysSinceLastEmail', 2 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceLastReport', 2 );
        $this->mySetReturnValue( $Messenger, 'isThrottled', false );

        $this->myExpectAtLeastOnce( $Messenger, 'sendReportRequestEmail' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );
        $Goals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenReportNotDue() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->myMakeMock( 'HfMysqlDatabase' );
        $CodeLibrary   = $this->myMakeMock( 'HfPhpLibrary' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->mySetReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->mySetReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->mySetReturnValue( $DbConnection, 'level', $mockLevel );

        $this->mySetReturnValue( $DbConnection, 'daysSinceLastEmail', 2 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceLastReport', 0 );

        $this->myExpectNever( $Messenger, 'sendReportRequestEmail' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );
        $Goals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenUserThrottled() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->myMakeMock( 'HfMysqlDatabase' );
        $CodeLibrary   = $this->myMakeMock( 'HfPhpLibrary' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->mySetReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->mySetReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->mySetReturnValue( $DbConnection, 'level', $mockLevel );

        $this->mySetReturnValue( $DbConnection, 'daysSinceLastEmail', 2 );
        $this->mySetReturnValue( $DbConnection, 'daysSinceLastReport', 5 );
        $this->mySetReturnValue( $Messenger, 'IsThrottled', true );

        $this->myExpectNever( $Messenger, 'sendReportRequestEmail' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );
        $Goals->sendReportRequestEmails();
    }

    public function testCurrentLevelTarget() {
        $Messenger     = $this->myMakeMock( 'HfMailer' );
        $WebsiteApi    = $this->myMakeMock( 'HfWordPressInterface' );
        $HtmlGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->myMakeMock( 'HfMysqlDatabase' );

        $mockLevel         = new stdClass();
        $mockLevel->target = 14;
        $this->mySetReturnValue( $DbConnection, 'level', $mockLevel );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );

        $target = $Goals->currentLevelTarget( 5 );

        $this->assertEquals( $target, 14 );
    }

    public function testGetGoalTitle() {
        list( $Messenger, $WebsiteApi, $HtmlGenerator, $Database ) = $this->makeMockDependencies();

        $mockGoal         = new stdClass();
        $mockGoal->title  = 'Eat durian';
        $this->mySetReturnValue($Database, 'getGoal', $mockGoal);

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $Database );

        $goalTitle = $Goals->getGoalTitle(1);

        $this->assertEquals($mockGoal->title, $goalTitle);
    }

    public function testRecordAccountabilityReport() {
        list( $Messenger, $WebsiteApi, $HtmlGenerator, $Database ) = $this->makeMockDependencies();

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $Database );

        $this->myExpectOnce($Database, 'recordAccountabilityReport', array(1, 2, 3, 4));

        $Goals->recordAccountabilityReport(1, 2, 3, 4);
    }

    public function testGetGoalSubscriptions() {
        list( $Messenger, $WebsiteApi, $HtmlGenerator, $Database ) = $this->makeMockDependencies();

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $Database );

        $expected = array(1, 2, 3, 4);

        $this->mySetReturnValue($Database, 'getGoalSubscriptions', $expected);

        $actual = $Goals->getGoalSubscriptions(1);

        $this->assertEquals($expected, $actual);
    }
} 