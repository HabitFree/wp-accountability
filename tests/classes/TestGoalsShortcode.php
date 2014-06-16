<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGoalsShortcode extends HfTestCase {
    private $MockUserManager;
    private $MockMessenger;
    private $MockPageLocator;
    private $MockGoals;
    private $MockSecurity;
    private $MockMarkupGenerator;

    // Helper Functions

    private function resetMocks() {
        $this->MockUserManager     = $this->myMakeMock( 'HfUserManager' );
        $this->MockMessenger       = $this->myMakeMock( 'HfMailer' );
        $this->MockPageLocator     = $this->myMakeMock( 'HfUrlFinder' );
        $this->MockGoals           = $this->myMakeMock( 'HfGoals' );
        $this->MockSecurity        = $this->myMakeMock( 'HfSecurity' );
        $this->MockMarkupGenerator = $this->myMakeMock( 'HfHtmlGenerator' );
    }

    // Tests

    public function testGoalsShortcodeGenerateReportNoticeEmail() {
        $this->resetMocks();

        $_POST           = array();
        $_POST['submit'] = '';
        $_POST[1]        = 0;
        $_POST[2]        = 1;

        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $mockPartner             = new stdClass();
        $mockPartner->ID         = 1;
        $mockPartner->user_login = 'Dan';
        $mockPartners            = array($mockPartner);
        $this->mySetReturnValue( $this->MockUserManager, 'getPartners', $mockPartners );

        $mockGoalSubs = array(new stdClass());
        $this->mySetReturnValue( $this->MockGoals, 'getGoalSubscriptions', $mockGoalSubs );

        $this->mySetReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
        $this->mySetReturnValue( $this->MockUserManager, 'getCurrentUserLogin', 'Don' );
        $this->mySetReturnValues( $this->MockGoals, 'getGoalTitle', array('Eat durian', 'Go running') );

        $Goals = new HfGoalsShortcode(
            $this->MockUserManager,
            $this->MockMessenger,
            $this->MockPageLocator,
            $this->MockGoals,
            $this->MockSecurity,
            $MarkupGenerator
        );

        $expectedBody =
            "<p>Hello, Dan,</p><p>Your friend Don just reported on their progress. Here's how they're doing:</p><ul><li>Eat durian: Success</li><li>Go running: Failure</li></ul>";

        $this->myExpectOnce( $this->MockMessenger, 'sendEmailToUser', array(1, 'Don just reported', $expectedBody) );

        $Goals->getOutput();
    }

    public function testGoalsShortcodeGenerateReportNoticeEmailScenarioTwo() {
        $this->resetMocks();

        $_POST           = array();
        $_POST['submit'] = '';
        $_POST[1]        = 1;

        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $mockPartner             = new stdClass();
        $mockPartner->ID         = 1;
        $mockPartner->user_login = 'Jack';
        $mockPartners            = array($mockPartner);
        $this->mySetReturnValue( $this->MockUserManager, 'getPartners', $mockPartners );

        $mockGoalSubs = array(new stdClass());
        $this->mySetReturnValue( $this->MockGoals, 'getGoalSubscriptions', $mockGoalSubs );

        $this->mySetReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
        $this->mySetReturnValue( $this->MockUserManager, 'getCurrentUserLogin', 'Jim' );
        $this->mySetReturnValue( $this->MockGoals, 'getGoalTitle', 'Eat durian' );

        $Goals = new HfGoalsShortcode(
            $this->MockUserManager,
            $this->MockMessenger,
            $this->MockPageLocator,
            $this->MockGoals,
            $this->MockSecurity,
            $MarkupGenerator
        );

        $expectedBody =
            "<p>Hello, Jack,</p><p>Your friend Jim just reported on their progress. Here's how they're doing:</p><ul><li>Eat durian: Failure</li></ul>";

        $this->myExpectOnce( $this->MockMessenger, 'sendEmailToUser', array(1, 'Jim just reported', $expectedBody) );

        $Goals->getOutput();
    }
}
