<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestGoalsShortcode extends HfTestCase {
    // Helper Functions

    private function setValidReportRequest555() {
        $_GET['n'] = 555;
        $this->setReturnValue( $this->mockMessenger, 'isReportRequestValid', true );
    }

    private function setDefaultIterables() {
        $this->setReturnValue( $this->mockGoals, 'getGoalSubscriptions', array() );

        $mockPartners = $this->makeMockPartners( 'Jack' );
        $this->setReturnValue( $this->mockUserManager, 'getPartners', $mockPartners );
    }

    private function makeMockPartners( $name ) {
        $mockPartner             = new stdClass();
        $mockPartner->ID         = 1;
        $mockPartner->user_login = $name;

        return array($mockPartner);
    }

    private function setReportSuccess() {
        $this->setDefaultIterables();
        $_POST['submit'] = '';
        $_POST[1]        = '1';
    }

    private function setDefaultQuotationValues() {
        $this->setReturnValue( $this->mockCodeLibrary, 'randomKeyFromArray', 0 );
        $MockQuotation = $this->makeMockQuotation();
        $this->setReturnValue( $this->mockDatabase, 'getQuotations', array($MockQuotation) );
    }

    private function makeMockQuotation() {
        $MockQuotation            = new stdClass();
        $MockQuotation->post_content = 'hello';
        $MockQuotation->post_title = 'Nathan';

        return $MockQuotation;
    }

    // Tests

    public function testGoalsShortcodeGenerateReportNoticeEmail() {
        $_POST['submit'] = '';
        $_POST[1]        = '1';
        $_POST[2]        = '0';

        $this->setDefaultQuotationValues();

        $mockPartners = $this->makeMockPartners( 'Dan' );
        $this->setReturnValue( $this->mockUserManager, 'getPartners', $mockPartners );

        $mockGoalSubs = array(new stdClass());
        $this->setReturnValue( $this->mockGoals, 'getGoalSubscriptions', $mockGoalSubs );

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue( $this->mockUserManager, 'getUsernameById', 'Don' );
        $this->setReturnValues( $this->mockGoals, 'getGoalTitle', array('eat durian', 'go running') );
        $this->setReturnValue($this->mockAssetLocator, 'getPageUrlByTitle', 'url');

        $goalsShortcode = $this->makeEloquentGoalsShortcode();

        $expectedBody =
            "<p>Hello, Dan,</p><p>Your friend Don just reported on their progress. Here's how they're doing:</p>" .
            "<ul><li>Don't eat durian: <span style='color:#088A08;'>Success</span></li>" .
            "<li>Don't go running: <span style='color:#8A0808;'>Setback</span></li></ul>";

        $this->expectOnce( $this->mockMessenger, 'sendReportNotificationEmail', array(1, 'Don just reported', $expectedBody) );

        $goalsShortcode->getOutput();
    }

    public function testGoalsShortcodeGenerateReportNoticeEmailScenarioTwo() {
        $this->setDefaultIterables();
        $_POST['submit'] = '';
        $_POST[1]        = '0';

        $this->setDefaultQuotationValues();

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue( $this->mockUserManager, 'getUsernameById', 'Jim' );
        $this->setReturnValue( $this->mockGoals, 'getGoalTitle', 'eat durian' );

        $GoalsShortcode = $this->makeEloquentGoalsShortcode();

        $expectedBody =
            "<p>Hello, Jack,</p><p>Your friend Jim just reported on their progress. Here's how they're doing:</p>" .
            "<ul><li>Don't eat durian: <span style='color:#8A0808;'>Setback</span></li></ul>";

        $this->expectOnce( $this->mockMessenger, 'sendReportNotificationEmail', array(1, 'Jim just reported', $expectedBody) );

        $GoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeClassExists() {
        $this->assertTrue( class_exists( 'HfGoalsShortcode' ) );
    }

    public function testGoalsShortcodeOutputsAnything() {
        $goalsShortcode = $this->factory->makeGoalsShortcode();
        $output         = $goalsShortcode->getOutput();

        $this->assertTrue( strlen( $output ) > 0 );
    }

    public function testGoalsShortcodeChecksNonce() {
        $_GET['n'] = 555;
        $this->expectOnce( $this->mockMessenger, 'isReportRequestValid', array(555) );
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', false );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeDoesNotAuthenticateUserWhenNoNonce() {
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', false );
        $this->expectOnce( $this->mockSecurity, 'requireLogin' );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeDoesNotDeleteReportRequest() {
        $this->setValidReportRequest555();
        $this->setDefaultIterables();
        $this->expectNever( $this->mockMessenger, 'deleteReportRequest' );
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', false );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeGetsUserIdFromReportRequest() {
        $this->setValidReportRequest555();
        $this->setDefaultIterables();
        $this->expectOnce( $this->mockMessenger, 'getReportRequestUserId', array(555) );
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', false );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeGetsUserIdFromUserManager() {
        $this->setValidReportRequest555();
        $this->setDefaultIterables();
        $this->expectOnce( $this->mockUserManager, 'getCurrentUserId' );
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeUpdatesExpirationDate() {
        $this->setValidReportRequest555();
        $this->setDefaultIterables();
        $this->expectOnce( $this->mockMessenger, 'updateReportRequestExpirationDate' );
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', false );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeSetsExpirationDateToOneHourInFuture() {
        $this->setValidReportRequest555();
        $this->setDefaultIterables();
        $this->expectOnce( $this->mockMessenger, 'updateReportRequestExpirationDate', array(555, 10 + ( 60 * 60 )) );
        $this->setReturnValue( $this->mockCodeLibrary, 'getCurrentTime', 10 );
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', false );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeDeletesReportRequestIfReportSubmitted() {
        $this->setValidReportRequest555();
        $this->setDefaultIterables();
        $_POST['submit'] = '';

        $this->expectOnce( $this->mockMessenger, 'deleteReportRequest', array(555) );

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', false );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeUpdatsReportRequestWhenUserLoggedIn() {
        $this->setValidReportRequest555();
        $this->setDefaultIterables();
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->expectOnce( $this->mockMessenger, 'updateReportRequestExpirationDate' );
        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeDoesNotUpdateReportRequestWhenReportSubmitted() {
        $this->setValidReportRequest555();
        $this->setDefaultIterables();
        $_POST['submit'] = '';
        $this->expectNever( $this->mockMessenger, 'updateReportRequestExpirationDate' );
        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeIgnoresInvalidNonces() {
        $this->setDefaultIterables();
        $_GET['n'] = 666;
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue( $this->mockMessenger, 'isReportRequestValid', false );
        $this->expectNever( $this->mockMessenger, 'updateReportRequestExpirationDate' );
        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeUsesReportRequestToGetUsernameWhenUserNotLoggedIn() {
        $this->setValidReportRequest555();
        $this->setReportSuccess();
        $this->setDefaultQuotationValues();

        $this->setReturnValue( $this->mockMessenger, 'getReportRequestUserId', 9 );
        $this->setReturnValue( $this->mockMessenger, 'isReportRequestValid', true );
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', false );
        $this->expectOnce( $this->mockMessenger, 'getReportRequestUserId' );
        $this->expectOnce( $this->mockUserManager, 'getUsernameById', array(9) );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeDeletesExpiredReportRequests() {
        $this->expectOnce( $this->mockMessenger, 'deleteExpiredReportRequests' );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeAsksForQuotationForSuccess() {
        $this->setReportSuccess();
        $this->setDefaultQuotationValues();

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );

        $this->expectOnce( $this->mockDatabase, 'getQuotations', array('For Success') );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeSelectsRandomQuotation() {
        $this->setReportSuccess();
        $this->setDefaultQuotationValues();

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue($this->mockDatabase, 'getQuotations', array($this->setDefaultQuotationValues()));

        $this->expectOnce($this->mockCodeLibrary, 'randomKeyFromArray', array(array($this->makeMockQuotation())));

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeUsesMarkupGeneratorToCreateSuccessMessage() {
        $this->setReportSuccess();
        $this->setDefaultQuotationValues();

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );

        $this->expectOnce( $this->mockMarkupGenerator, 'makeSuccessMessage', array('Thanks for checking in!') );

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeMakesQuoteMessage() {
        $this->setReportSuccess();
        $this->setDefaultQuotationValues();

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue($this->mockDatabase, 'getQuotations', 'duck');

        $this->expectOnce($this->mockMarkupGenerator, 'makeQuoteMessage');

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeUsesRandomQuotationToMakeQuoteMessage() {
        $this->setReportSuccess();
        $this->setDefaultQuotationValues();

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue($this->mockDatabase, 'getQuotations', array($this->makeMockQuotation()));

        $this->expectOnce($this->mockMarkupGenerator, 'makeQuoteMessage', array($this->makeMockQuotation()));

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeDisplaysRandomQuotationOnSuccess() {
        $goalsShortcode = $this->makeEloquentGoalsShortcode();

        $this->setReportSuccess();
        $this->setDefaultQuotationValues();

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue($this->mockDatabase, 'getQuotations', array($this->makeMockQuotation()));

        $haystack = $goalsShortcode->getOutput();
        $needle = '<p class="quote">"hello" — Nathan</p>';

        $this->assertContains($needle, $haystack);
    }

    public function testGoalsShortcodeGetsQuotationForSetbackWhenSetbackOccurred() {
        $this->setDefaultIterables();
        $_POST['submit'] = '';
        $_POST[1]        = '0';
        $this->setDefaultQuotationValues();
        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );

        $this->expectOnce($this->mockDatabase, 'getQuotations', array('For Setback'));

        $this->mockedGoalsShortcode->getOutput();
    }

    public function testGoalsShortcodeDoesntDisplayEmptyQuotation() {
        $this->setReportSuccess();

        $this->setReturnValue( $this->mockUserManager, 'isUserLoggedIn', true );
        $this->setReturnValue($this->mockDatabase, 'getQuotations', array());

        $this->expectNever($this->mockMarkupGenerator, 'makeQuoteMessage');

        $this->mockedGoalsShortcode->getOutput();
    }

    private function makeEloquentGoalsShortcode()
    {
        return new HfGoalsShortcode(
            $this->mockUserManager,
            $this->mockMessenger,
            $this->mockAssetLocator,
            $this->mockGoals,
            $this->mockSecurity,
            $this->factory->makeMarkupGenerator(),
            $this->mockCodeLibrary,
            $this->mockDatabase
        );
    }
}
