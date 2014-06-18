<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGoalsShortcode extends HfTestCase {
    private $MockUserManager;
    private $MockPageLocator;
    private $MockGoals;
    private $MockMarkupGenerator;

    // Helper Functions

    private function resetMocks() {
        $this->MockUserManager     = $this->makeMock( 'HfUserManager' );
        $this->MockMessenger       = $this->makeMock( 'HfMailer' );
        $this->MockPageLocator     = $this->makeMock( 'HfUrlFinder' );
        $this->MockGoals           = $this->makeMock( 'HfGoals' );
        $this->MockSecurity        = $this->makeMock( 'HfSecurity' );
        $this->MockMarkupGenerator = $this->makeMock( 'HfHtmlGenerator' );
    }

    // Tests

    public function testGoalsShortcodeGenerateReportNoticeEmail() {
        $this->resetMocks();

        $_POST           = array();
        $_POST['submit'] = '';
        $_POST[1]        = '1';
        $_POST[2]        = '0';

        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $mockPartner             = new stdClass();
        $mockPartner->ID         = 1;
        $mockPartner->user_login = 'Dan';
        $mockPartners            = array($mockPartner);
        $this->setReturnValue( $this->MockUserManager, 'getPartners', $mockPartners );

        $mockGoalSubs = array(new stdClass());
        $this->setReturnValue( $this->MockGoals, 'getGoalSubscriptions', $mockGoalSubs );

        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue( $this->MockUserManager, 'getCurrentUserLogin', 'Don' );
        $this->setReturnValues( $this->MockGoals, 'getGoalTitle', array('Eat durian', 'Go running') );

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

        $this->expectOnce( $this->MockMessenger, 'sendEmailToUser', array(1, 'Don just reported', $expectedBody) );

        $Goals->getOutput();
    }

    public function testGoalsShortcodeGenerateReportNoticeEmailScenarioTwo() {
        $this->resetMocks();

        $_POST           = array();
        $_POST['submit'] = '';
        $_POST[1]        = '0';

        $MarkupGenerator = $this->Factory->makeMarkupGenerator();

        $mockPartner             = new stdClass();
        $mockPartner->ID         = 1;
        $mockPartner->user_login = 'Jack';
        $mockPartners            = array($mockPartner);
        $this->setReturnValue( $this->MockUserManager, 'getPartners', $mockPartners );

        $mockGoalSubs = array(new stdClass());
        $this->setReturnValue( $this->MockGoals, 'getGoalSubscriptions', $mockGoalSubs );

        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue( $this->MockUserManager, 'getCurrentUserLogin', 'Jim' );
        $this->setReturnValue( $this->MockGoals, 'getGoalTitle', 'Eat durian' );

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

        $this->expectOnce( $this->MockMessenger, 'sendEmailToUser', array(1, 'Jim just reported', $expectedBody) );

        $Goals->getOutput();
    }

    public function testGoalsShortcodeClassExists() {
        $this->assertTrue( class_exists( 'HfGoalsShortcode' ) );
    }

    public function testGoalsShortcodeOutputsAnything() {
        $GoalsShortcode = $this->Factory->makeGoalsShortcode();
        $output         = $GoalsShortcode->getOutput();

        $this->assertTrue( strlen( $output ) > 0 );
    }
}
