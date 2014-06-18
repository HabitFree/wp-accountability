<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGoalsShortcode extends HfTestCase {
    // Helper Functions

    // Tests

    public function testGoalsShortcodeGenerateReportNoticeEmail() {
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

    public function testGoalsShortcodeChecksNonce() {
        $_GET['n'] = 555;
        $this->expectOnce( $this->MockMessenger, 'isReportRequestValid', array(555) );
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', false );

        $this->GoalsShortcodeWithMockDependencies->getOutput();
    }

    public function testGoalsShortcodeDoesNotAuthenticateUserWhenNoNonce() {
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', false );
        $this->expectOnce( $this->MockSecurity, 'requireLogin' );

        $this->GoalsShortcodeWithMockDependencies->getOutput();
    }

    public function testGoalsShortcodeDeletesReportRequest() {
        $_GET['n'] = 555;
        $this->expectOnce( $this->MockMessenger, 'deleteReportRequest', array(555) );
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', false );
        $this->setReturnValue( $this->MockMessenger, 'isReportRequestValid', true );
        $this->setReturnValue( $this->MockGoals, 'getGoalSubscriptions', array() );

        $this->GoalsShortcodeWithMockDependencies->getOutput();
    }

    public function testGoalsShortcodeDeletesReportRequestEvenWhenUserLoggedIn() {
        $_GET['n'] = 555;
        $this->expectOnce( $this->MockMessenger, 'deleteReportRequest', array(555) );
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue( $this->MockMessenger, 'isReportRequestValid', true );
        $this->setReturnValue( $this->MockGoals, 'getGoalSubscriptions', array() );

        $this->GoalsShortcodeWithMockDependencies->getOutput();
    }

    public function testGoalsShortcodeGetsUserIdFromReportRequest() {
        $_GET['n'] = 555;
        $this->expectOnce( $this->MockMessenger, 'getReportRequestUserId', array(555) );
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', false );
        $this->setReturnValue( $this->MockMessenger, 'isReportRequestValid', true );
        $this->setReturnValue( $this->MockGoals, 'getGoalSubscriptions', array() );

        $this->GoalsShortcodeWithMockDependencies->getOutput();
    }

    public function testGoalsShortcodeGetsUserIdFromUserManager() {
        $_GET['n'] = 555;
        $this->expectOnce( $this->MockUserManager, 'getCurrentUserId' );
        $this->setReturnValue( $this->MockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue( $this->MockMessenger, 'isReportRequestValid', true );
        $this->setReturnValue( $this->MockGoals, 'getGoalSubscriptions', array() );

        $this->GoalsShortcodeWithMockDependencies->getOutput();
    }
}
