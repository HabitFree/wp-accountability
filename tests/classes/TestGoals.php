<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGoals extends HfTestCase {
    // Helper Functions

    private function makeMockDependencies() {
        $Messenger     = $this->makeMock( 'HfMailer' );
        $WebsiteApi    = $this->makeMock( 'HfWordPress' );
        $HtmlGenerator = $this->makeMock( 'HfHtmlGenerator' );
        $Database      = $this->makeMock( 'HfMysqlDatabase' );

        return array($Messenger, $WebsiteApi, $HtmlGenerator, $Database);
    }

    // Tests

    public function testSendReportRequestEmailsChecksThrottling() {
        $Messenger     = $this->makeMock( 'HfMailer' );
        $WebsiteApi    = $this->makeMock( 'HfWordPress' );
        $HtmlGenerator = $this->makeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->makeMock( 'HfMysqlDatabase' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->setReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->setReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->setReturnValue( $DbConnection, 'level', $mockLevel );

        $this->setReturnValue( $DbConnection, 'daysSinceLastReport', 2 );
        $this->setReturnValue( $Messenger, 'isThrottled', true );

        $this->expectAtLeastOnce( $Messenger, 'isThrottled' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );
        $Goals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsSendsEmailWhenReportDue() {
        $Messenger     = $this->makeMock( 'HfMailer' );
        $WebsiteApi    = $this->makeMock( 'HfWordPress' );
        $HtmlGenerator = $this->makeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->makeMock( 'HfMysqlDatabase' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->setReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->setReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->setReturnValue( $DbConnection, 'level', $mockLevel );

        $this->setReturnValue( $DbConnection, 'daysSinceLastEmail', 2 );
        $this->setReturnValue( $DbConnection, 'daysSinceLastReport', 2 );
        $this->setReturnValue( $Messenger, 'isThrottled', false );

        $this->expectAtLeastOnce( $Messenger, 'sendReportRequestEmail' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );
        $Goals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenReportNotDue() {
        $Messenger     = $this->makeMock( 'HfMailer' );
        $WebsiteApi    = $this->makeMock( 'HfWordPress' );
        $HtmlGenerator = $this->makeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->makeMock( 'HfMysqlDatabase' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->setReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->setReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->setReturnValue( $DbConnection, 'level', $mockLevel );

        $this->setReturnValue( $DbConnection, 'daysSinceLastEmail', 2 );
        $this->setReturnValue( $DbConnection, 'daysSinceLastReport', 0 );

        $this->expectNever( $Messenger, 'sendReportRequestEmail' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );
        $Goals->sendReportRequestEmails();
    }

    public function testSendReportRequestEmailsDoesNotSendEmailWhenUserThrottled() {
        $Messenger     = $this->makeMock( 'HfMailer' );
        $WebsiteApi    = $this->makeMock( 'HfWordPress' );
        $HtmlGenerator = $this->makeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->makeMock( 'HfMysqlDatabase' );

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->setReturnValue( $WebsiteApi, 'getSubscribedUsers', $mockUsers );

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->setReturnValue( $DbConnection, 'getRows', $mockGoalSubs );

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->setReturnValue( $DbConnection, 'level', $mockLevel );

        $this->setReturnValue( $DbConnection, 'daysSinceLastEmail', 2 );
        $this->setReturnValue( $DbConnection, 'daysSinceLastReport', 5 );
        $this->setReturnValue( $Messenger, 'IsThrottled', true );

        $this->expectNever( $Messenger, 'sendReportRequestEmail' );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );
        $Goals->sendReportRequestEmails();
    }

    public function testCurrentLevelTarget() {
        $Messenger     = $this->makeMock( 'HfMailer' );
        $WebsiteApi    = $this->makeMock( 'HfWordPress' );
        $HtmlGenerator = $this->makeMock( 'HfHtmlGenerator' );
        $DbConnection  = $this->makeMock( 'HfMysqlDatabase' );

        $mockLevel         = new stdClass();
        $mockLevel->target = 14;
        $this->setReturnValue( $DbConnection, 'level', $mockLevel );

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection );

        $target = $Goals->currentLevelTarget( 5 );

        $this->assertEquals( $target, 14 );
    }

    public function testGetGoalTitle() {
        list( $Messenger, $WebsiteApi, $HtmlGenerator, $Database ) = $this->makeMockDependencies();

        $mockGoal         = new stdClass();
        $mockGoal->title  = 'Eat durian';
        $this->setReturnValue($Database, 'getGoal', $mockGoal);

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $Database );

        $goalTitle = $Goals->getGoalTitle(1);

        $this->assertEquals($mockGoal->title, $goalTitle);
    }

    public function testRecordAccountabilityReport() {
        list( $Messenger, $WebsiteApi, $HtmlGenerator, $Database ) = $this->makeMockDependencies();

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $Database );

        $this->expectOnce($Database, 'recordAccountabilityReport', array(1, 2, 3, 4));

        $Goals->recordAccountabilityReport(1, 2, 3, 4);
    }

    public function testGetGoalSubscriptions() {
        list( $Messenger, $WebsiteApi, $HtmlGenerator, $Database ) = $this->makeMockDependencies();

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $Database );

        $expected = array(1, 2, 3, 4);

        $this->setReturnValue($Database, 'getGoalSubscriptions', $expected);

        $actual = $Goals->getGoalSubscriptions(1);

        $this->assertEquals($expected, $actual);
    }

    public function testSendEmailReportRequests() {
        $Factory = new HfFactory();
        $Goals   = $Factory->makeGoals();
        $Goals->sendReportRequestEmails();
    }
} 