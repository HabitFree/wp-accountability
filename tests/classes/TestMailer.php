<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestMailer extends HfTestCase {
    private $MockAssetLocator;
    private $MockSecurity;
    private $MockDatabase;
    private $MockCms;

    private $MailerWithMockedDependencies;

    // Helper Functions

    protected function setUp() {
        $this->resetMocks();
        $this->resetMailerWithMockedDependencies();
    }

    private function resetMocks() {
        $this->MockAssetLocator = $this->myMakeMock( 'HfUrlFinder' );
        $this->MockSecurity     = $this->myMakeMock( 'HfSecurity' );
        $this->MockDatabase     = $this->myMakeMock( 'HfMysqlDatabase' );
        $this->MockCms          = $this->myMakeMock( 'HfWordPress' );
    }

    private function resetMailerWithMockedDependencies() {
        $this->MailerWithMockedDependencies = new HfMailer(
            $this->MockAssetLocator,
            $this->MockSecurity,
            $this->MockDatabase,
            $this->MockCms
        );
    }

    // Tests

    public function testSendEmailByUserID() {
        $this->mySetReturnValue( $this->MockCms, 'getVar', 5 );
        $this->mySetReturnValue( $this->MockCms, 'getUserEmail', 'me@test.com' );

        $this->myExpectAtLeastOnce( $this->MockDatabase, 'recordEmail', array(1, 'test', 'test', 5, 'me@test.com') );

        $this->MailerWithMockedDependencies->sendEmailToUser( 1, 'test', 'test' );
    }

    public function testSendEmailToUserAndSpecifyEmailID() {
        $this->mySetReturnValue( $this->MockCms, 'sendWpEmail', true );
        $this->mySetReturnValue( $this->MockCms, 'getUserEmail', 'me@test.com' );

        $this->myExpectAtLeastOnce( $this->MockDatabase, 'recordEmail', array(1, 'test subject', 'test body', 123, 'me@test.com') );

        $this->MailerWithMockedDependencies->sendEmailToUserAndSpecifyEmailID( 1, 'test subject', 'test body', 123 );
    }

    public function testIsThrottledReturnsFalse() {
        $this->mySetReturnValue( $this->MockDatabase, 'daysSinceAnyReport', 100 );
        $this->mySetReturnValue( $this->MockDatabase, 'daysSinceLastEmail', 10 );
        $this->mySetReturnValue( $this->MockDatabase, 'daysSinceSecondToLastEmail', 12 );

        $result = $this->MailerWithMockedDependencies->isThrottled( 1 );

        $this->assertEquals( $result, false );
    }

    public function testIsThrottledReturnsTrue() {
        $this->mySetReturnValue( $this->MockDatabase, 'daysSinceAnyReport', 100 );
        $this->mySetReturnValue( $this->MockDatabase, 'daysSinceLastEmail', 10 );
        $this->mySetReturnValue( $this->MockDatabase, 'daysSinceSecondToLastEmail', 17 );

        $result = $this->MailerWithMockedDependencies->isThrottled( 1 );

        $this->assertEquals( $result, true );
    }

    public function testMailerPointsInviteUrlToRegistrationTab() {
        $this->mySetReturnValue( $this->MockAssetLocator, 'getPageUrlByTitle', 'habitfree.org/authenticate' );

        $result   = $this->MailerWithMockedDependencies->generateInviteURL( 777 );
        $expected = 'habitfree.org/authenticate?n=777&tab=2';

        $this->assertEquals( $expected, $result );
    }

    public function testIsEmailValid() {
        $this->mySetReturnValue( $this->MockDatabase, 'isEmailValid', true );

        $this->assertTrue( $this->MailerWithMockedDependencies->isEmailValid( 1, 1 ) );
    }

    public function IGNOREtestSendReportRequestEmailUsesSecureId() {
        $this->myExpectAtLeastOnce($this->MockSecurity, 'createRandomString', array(250));
        $this->MailerWithMockedDependencies->sendReportRequestEmail(1);
    }
}