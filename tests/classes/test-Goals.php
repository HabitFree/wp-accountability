<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGoals extends HfTestCase {
    public function testSendReportRequestEmailsChecksThrottling() {
        $Messenger     = $this->myMakeMock('HfMailer');
        $WebsiteApi    = $this->myMakeMock('HfWordPressInterface');
        $HtmlGenerator = $this->myMakeMock('HfHtmlGenerator');
        $DbConnection  = $this->myMakeMock('HfMysqlDatabase');
        $CodeLibrary   = $this->myMakeMock('HfPhpLibrary');

        $mockUser     = new stdClass();
        $mockUser->ID = 1;
        $mockUsers    = array($mockUser);
        $this->mySetReturnValue($WebsiteApi, 'getSubscribedUsers', $mockUsers);

        $mockGoalSub         = new stdClass();
        $mockGoalSub->goalID = 1;
        $mockGoalSubs        = array($mockGoalSub);
        $this->mySetReturnValue($DbConnection, 'getRows', $mockGoalSubs);

        $mockLevel                = new stdClass();
        $mockLevel->emailInterval = 1;
        $this->mySetReturnValue($DbConnection, 'level', $mockLevel);

        $this->mySetReturnValue($DbConnection, 'daysSinceLastReport', 2);
        $this->mySetReturnValue($Messenger, 'isThrottled', true);

        $this->myExpectAtLeastOnce($Messenger, 'isThrottled');

        $Goals = new HfGoals( $Messenger, $WebsiteApi, $HtmlGenerator, $DbConnection, $CodeLibrary );
        $Goals->sendReportRequestEmails();
    }
} 