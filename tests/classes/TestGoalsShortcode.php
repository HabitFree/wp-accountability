<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGoalsShortcode extends HfTestCase {
    // Helper Functions

    private function makeGoalsShortcodeMockDependencies() {
        $UserManager = $this->myMakeMock( 'HfUserManager' );
        $Messenger   = $this->myMakeMock( 'HfMailer' );
        $PageLocator = $this->myMakeMock( 'HfUrlFinder' );
        $Goals       = $this->myMakeMock( 'HfGoals' );
        $Security    = $this->myMakeMock( 'HfSecurity' );

        return array($UserManager, $Messenger, $PageLocator, $Goals, $Security);
    }

    // Tests

    public function testGoalsShortcodeGenerateReportNoticeEmail() {
        $_POST           = array();
        $_POST['submit'] = '';
        $_POST[1]        = 1;
        $_POST[2]        = 0;

        list( $UserManager, $Messenger, $PageLocator, $Goals, $Security ) = $this->makeGoalsShortcodeMockDependencies();

        $mockPartner = new stdClass();
        $mockPartner->ID = 1;
        $mockPartner->user_login = 'Dan';
        $mockPartners = array($mockPartner);
        $this->mySetReturnValue($UserManager, 'getPartners', $mockPartners);

        $mockGoalSubs = array(new stdClass());
        $this->mySetReturnValue($Goals, 'getGoalSubscriptions', $mockGoalSubs);

        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', true);
        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Don');
        $this->mySetReturnValues($Goals, 'getGoalTitle', array('Eat durian','Go running' ));

        $Goals = new HfGoalsShortcode( $UserManager, $Messenger, $PageLocator, $Goals, $Security );

        $expectedBody =
            "<p>Hello, Dan,</p><p>Your friend Don just reported on their progress. Here's how they're doing:</p><ul><li>Eat durian: Success</li><li>Go running: Failure</li></ul>";

        $this->myExpectOnce( $Messenger, 'sendEmailToUser', array(1, 'Don just reported', $expectedBody) );

        $Goals->getOutput();
    }

    public function testGoalsShortcodeGenerateReportNoticeEmailScenarioTwo() {
        $_POST           = array();
        $_POST['submit'] = '';
        $_POST[1]        = 0;

        list( $UserManager, $Messenger, $PageLocator, $Goals, $Security ) = $this->makeGoalsShortcodeMockDependencies();

        $mockPartner = new stdClass();
        $mockPartner->ID = 1;
        $mockPartner->user_login = 'Jack';
        $mockPartners = array($mockPartner);
        $this->mySetReturnValue($UserManager, 'getPartners', $mockPartners);

        $mockGoalSubs = array(new stdClass());
        $this->mySetReturnValue($Goals, 'getGoalSubscriptions', $mockGoalSubs);

        $this->mySetReturnValue($UserManager, 'isUserLoggedIn', true);
        $this->mySetReturnValue($UserManager, 'getCurrentUserLogin', 'Jim');
        $this->mySetReturnValue($Goals, 'getGoalTitle', 'Eat durian');

        $Goals = new HfGoalsShortcode( $UserManager, $Messenger, $PageLocator, $Goals, $Security );

        $expectedBody =
            "<p>Hello, Jack,</p><p>Your friend Jim just reported on their progress. Here's how they're doing:</p><ul><li>Eat durian: Failure</li></ul>";

        $this->myExpectOnce( $Messenger, 'sendEmailToUser', array(1, 'Jim just reported', $expectedBody) );

        $Goals->getOutput();
    }
}
