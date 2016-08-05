<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestMailer extends HfTestCase {
    // Helper Functions

    private function makeMockInvite( $expirationDate, $inviteId ) {
        $mockInvite                 = new stdClass();
        $mockInvite->expirationDate = $expirationDate;
        $mockInvite->inviteID       = $inviteId;

        return $mockInvite;
    }

    private function makeMockReportRequest($expirationDate, $requestId) {
        $reportRequest                 = new stdClass();
        $reportRequest->expirationDate = $expirationDate;
        $reportRequest->requestID      = $requestId;

        return $reportRequest;
    }

    private function makeFreshExpirationDate() {
        $freshExpirationTime = strtotime( '+' . 3 . ' days' );
        $freshExpirationDate = date( 'Y-m-d H:i:s', $freshExpirationTime );

        return $freshExpirationDate;
    }

    // Tests

    public function testSendEmailByUserID() {
        $this->setReturnValue( $this->mockCms, 'getVar', 5 );
        $this->setReturnValue( $this->mockCms, 'getUserEmail', 'me@test.com' );

        $this->expectAtLeastOnce( $this->mockDatabase, 'recordEmail', array(1, 'test', 'test', 5, 'me@test.com') );

        $this->mockedMessenger->sendEmailToUser( 1, 'test', 'test' );
    }

    public function testSendEmailToUserAndSpecifyEmailID() {
        $this->setReturnValue( $this->mockCms, 'sendEmail', true );
        $this->setReturnValue( $this->mockCms, 'getUserEmail', 'me@test.com' );

        $this->expectAtLeastOnce( $this->mockDatabase, 'recordEmail', array(1, 'test subject', 'test body', 123, 'me@test.com') );

        $this->mockedMessenger->sendEmailToUserAndSpecifyEmailID( 1, 'test subject', 'test body', 123 );
    }

    public function testIsThrottledReturnsFalse() {
        $this->setReturnValue( $this->mockDatabase, 'daysSinceAnyReport', 100 );
        $this->setReturnValue( $this->mockDatabase, 'daysSinceLastEmail', 10 );
        $this->setReturnValue( $this->mockDatabase, 'daysSinceSecondToLastEmail', 12 );

        $result = $this->mockedMessenger->isThrottled( 1 );

        $this->assertEquals( $result, false );
    }

    public function testIsThrottledReturnsTrue() {
        $this->setReturnValue( $this->mockDatabase, 'daysSinceAnyReport', 100 );
        $this->setReturnValue( $this->mockDatabase, 'daysSinceLastEmail', 10 );
        $this->setReturnValue( $this->mockDatabase, 'daysSinceSecondToLastEmail', 17 );

        $result = $this->mockedMessenger->isThrottled( 1 );

        $this->assertEquals( $result, true );
    }

    public function testMailerPointsInviteUrlToRegistrationTab() {
        $this->setReturnValue( $this->mockAssetLocator, 'getPageUrlByTitle', 'habitfree.org/authenticate' );

        $result   = $this->mockedMessenger->generateInviteURL( 777 );
        $expected = 'habitfree.org/authenticate?n=777&tab=2';

        $this->assertEquals( $expected, $result );
    }

    public function testIsEmailValid() {
        $this->setReturnValue( $this->mockDatabase, 'isEmailValid', true );

        $this->assertTrue( $this->mockedMessenger->isEmailValid( 1, 1 ) );
    }

    public function testSendReportRequestEmailGeneratesNonce() {
        $this->expectAtLeastOnce( $this->mockSecurity, 'createRandomString', array(250) );
        $this->mockedMessenger->sendReportRequestEmail( 1 );
    }

    public function testGenerateReportUrlUsesNonce() {
        $baseURL = 'habitfree.org/test';
        $this->setReturnValue( $this->mockAssetLocator, 'getPageUrlByTitle', $baseURL );

        $actual = $this->mockedMessenger->generateReportURL( 555 );

        $expected = $baseURL . '?n=555';

        $this->assertEquals( $expected, $actual );
    }

    public function testSendReportRequestEmailUsesNonceToCreateUrl() {
        $this->setReturnValue( $this->mockSecurity, 'createRandomString', 555 );
        $baseURL = 'habitfree.org/test';
        $this->setReturnValue( $this->mockAssetLocator, 'getPageUrlByTitle', $baseURL );
        $reportUrl = $baseURL . '?n=555';

        $userID  = 1;
        $subject = "How's it going?";
        $body    = "<p>Time to <a href='" . $reportUrl . "'>check in</a>.</p>";
        $emailID = null;
        $to      = null;

        $this->expectOnce( $this->mockDatabase, 'recordEmail', array($userID, $subject, $body, $emailID, $to) );

        $this->mockedMessenger->sendReportRequestEmail( $userID );
    }

    public function testSendReportRequestEmailRecordsRequest() {
        $this->setReturnValue( $this->mockSecurity, 'createRandomString', 555 );
        $this->setReturnValue( $this->mockDatabase, 'generateEmailID', 7 );

        $userId  = 1;
        $emailId = 7;

        $this->expectOnce( $this->mockDatabase, 'recordReportRequest', array(555, $userId, $emailId) );

        $this->mockedMessenger->sendReportRequestEmail( $userId );
    }

    public function testIsReportRequestValid() {
        $this->setReturnValue( $this->mockDatabase, 'isReportrequestValid', true );
        $this->assertTrue( $this->mockedMessenger->isReportRequestValid( 555 ) );
    }

    public function testIsReportRequestValidReturnsFalse() {
        $this->setReturnValue( $this->mockDatabase, 'isReportrequestValid', false );
        $this->assertFalse( $this->mockedMessenger->isReportRequestValid( 555 ) );
    }

    public function testDeleteReportRequest() {
        $this->expectOnce( $this->mockDatabase, 'deleteReportRequest', array(555) );
        $this->mockedMessenger->deleteReportRequest( 555 );
    }

    public function testGetReportRequestUserId() {
        $this->expectOnce( $this->mockDatabase, 'getReportRequestUserId', array(555) );
        $this->mockedMessenger->getReportRequestUserId( 555 );
    }

    public function testGetReportRequestUserIdReturnsValue() {
        $this->setReturnValue( $this->mockDatabase, 'getReportRequestUserId', 5 );
        $actual = $this->mockedMessenger->getReportRequestUserId( 555 );
        $this->assertEquals( 5, $actual );
    }

    public function testUpdateReportRequestExpirationDate() {
        $this->expectOnce( $this->mockDatabase, 'updateReportRequestExpirationDate', array(555, 'abcd') );
        $this->mockedMessenger->updateReportRequestExpirationDate( 555, 'abcd' );
    }

    public function testMailerCreatesLigitExpirationDateForReportRequests() {
        $this->setReturnValue( $this->mockSecurity, 'createRandomString', 123 );
        $this->setReturnValue( $this->mockDatabase, 'generateEmailId', 5 );
        $this->setReturnValue( $this->mockCodeLibrary, 'getCurrentTime', 1403551104 );

        $this->expectOnce( $this->mockDatabase, 'recordReportRequest', array(123, 1, 5, "2014-06-30 14:18:24") );

        $this->mockedMessenger->sendReportRequestEmail( 1 );
    }

    public function testDeleteExpiredInvitesGetsInvites() {
        $mockInvite = $this->makeMockInvite( '2014-6-20 13:22:12', '5ab' );

        $mockInvites = array($mockInvite);
        $this->setReturnValue( $this->mockDatabase, 'getAllInvites', $mockInvites );

        $this->expectOnce( $this->mockDatabase, 'getAllInvites' );

        $this->mockedMessenger->deleteExpiredInvites();
    }

    public function testDeleteExpiredInvitesDeletesExpiredInvite() {
        $mockInvite = $this->makeMockInvite( '2014-6-20 13:22:12', '5ab' );

        $this->setReturnValue( $this->mockCodeLibrary, 'getCurrentTime', time() );
        $mockInvites = array($mockInvite);
        $this->setReturnValue( $this->mockDatabase, 'getAllInvites', $mockInvites );

        $this->expectOnce( $this->mockDatabase, 'deleteInvite', array('5ab') );

        $this->mockedMessenger->deleteExpiredInvites();
    }

    public function testDeleteExpiredInviteDoesNotDeleteFreshInvite() {
        $expirationTime = strtotime( '+' . 3 . ' days' );
        $expirationDate = date( 'Y-m-d H:i:s', $expirationTime );

        $this->setReturnValue( $this->mockCodeLibrary, 'getCurrentTime', time() );

        $mockInvite = $this->makeMockInvite( $expirationDate, 'fresh' );
        $mockInvites = array($mockInvite);

        $this->setReturnValue( $this->mockDatabase, 'getAllInvites', $mockInvites );

        $this->expectNever( $this->mockDatabase, 'deleteInvite' );

        $this->mockedMessenger->deleteExpiredInvites();
    }

    public function testDeleteExpiredInviteDeletesStaleKeepsFresh() {
        $this->setReturnValue( $this->mockCodeLibrary, 'getCurrentTime', time() );

        $freshExpirationDate = $this->makeFreshExpirationDate();

        $freshMockInvite = $this->makeMockInvite( $freshExpirationDate, 'fresh' );
        $staleMockInvite = $this->makeMockInvite( '2014-6-20 13:22:12', 'stale' );

        $mockInvites = array($freshMockInvite, $staleMockInvite);

        $this->setReturnValue( $this->mockDatabase, 'getAllInvites', $mockInvites );

        $this->expectOnce( $this->mockDatabase, 'deleteInvite' );
        $this->expectOnce( $this->mockDatabase, 'deleteInvite', array('stale') );

        $this->mockedMessenger->deleteExpiredInvites();
    }

    public function testDeleteExpiredReportRequestsGetsReportRequests() {
        $staleReportRequest = $this->makeMockReportRequest('2014-6-20 13:22:12', 'stale');
        $this->setReturnValue($this->mockDatabase, 'getAllReportRequests', array($staleReportRequest));

        $this->expectOnce($this->mockDatabase, 'getAllReportRequests');
        $this->mockedMessenger->deleteExpiredReportRequests();
    }

    public function testDeleteExpiredReportRequestsDeletesExpiredReportRequest() {
        $this->setReturnValue( $this->mockCodeLibrary, 'getCurrentTime', time() );

        $staleReportRequest = $this->makeMockReportRequest('2014-6-20 13:22:12', 'stale');
        $this->setReturnValue($this->mockDatabase, 'getAllReportRequests', array($staleReportRequest));

        $this->expectOnce($this->mockDatabase, 'deleteReportRequest', array('stale'));
        $this->mockedMessenger->deleteExpiredReportRequests();
    }

    public function testDeleteExpiredReportRequestsDoesNotDeleteFreshReportRequest() {
        $this->setReturnValue( $this->mockCodeLibrary, 'getCurrentTime', time() );

        $freshExpirationDate = $this->makeFreshExpirationDate();

        $freshReportRequest = $this->makeMockReportRequest($freshExpirationDate, 'fresh');
        $this->setReturnValue($this->mockDatabase, 'getAllReportRequests', array($freshReportRequest));

        $this->expectNever($this->mockDatabase, 'deleteReportRequest');
        $this->mockedMessenger->deleteExpiredReportRequests();
    }

    public function testDeleteExpiredReportRequestsDeletesStaleAndKeepsFresh() {
        $this->setReturnValue( $this->mockCodeLibrary, 'getCurrentTime', time() );

        $freshExpirationDate = $this->makeFreshExpirationDate();

        $freshReportRequest = $this->makeMockReportRequest($freshExpirationDate, 'fresh');
        $staleReportRequest = $this->makeMockReportRequest('2014-6-20 13:22:12', 'stale');

        $this->setReturnValue($this->mockDatabase, 'getAllReportRequests', array($freshReportRequest, $staleReportRequest));

        $this->expectOnce($this->mockDatabase, 'deleteReportRequest');
        $this->expectOnce($this->mockDatabase, 'deleteReportRequest', array('stale'));

        $this->mockedMessenger->deleteExpiredReportRequests();
    }

    public function testSendReportNotificationEmail() {
        $this->expectOnce($this->mockCms,'getUserEmail',array(1));
        $this->sendReportNotificationEmail();
    }

    private function sendReportNotificationEmail()
    {
        $partnerId = 1;
        $subject = 'Somebody just reported';
        $report = 'They did this and that and the other';
        $this->mockedMessenger->sendReportNotificationEmail($partnerId, $subject, $report);
    }

    public function testSendReportNotificationEmailSendsEmail() {
        $this->setReturnValue($this->mockCms,'getUserEmail','partner@email.com');
        $this->setReturnValue($this->mockAssetLocator,'getPageUrlByTitle','url');
        $this->setReturnValue($this->mockSecurity,'createRandomString','nonce');
        $this->expectOnce($this->mockCms,'sendEmail',
            array(
                'partner@email.com',
                'Somebody just reported',
                "They did this and that and the other<p><a href='url?n=nonce'>Click here to submit your own report.</a></p>"
            )
        );
        $this->sendReportNotificationEmail();
    }

    public function testSendReportNotifcationEmailGeneratesEmailId() {
        $this->expectOnce($this->mockDatabase,'generateEmailId');
        $this->sendReportNotificationEmail();
    }

    public function testSendReportNotificationEmailGeneratesNonce() {
        $this->expectOnce($this->mockSecurity,'createRandomString',array(250));
        $this->sendReportNotificationEmail();
    }

    public function testSendReportNotificationEmailUsesNonce() {
        $this->setReturnValue($this->mockSecurity,'createRandomString','nonce');
        $this->expectOnce($this->mockDatabase,'recordReportRequest',array('nonce',1,null,'1970-01-07 18:00:00'));
        $this->sendReportNotificationEmail();
    }
}