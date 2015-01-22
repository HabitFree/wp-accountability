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

        $this->expectAtLeastOnce( $this->MockMysqlDatabase, 'recordEmail', array(1, 'test', 'test', 5, 'me@test.com') );

        $this->MockedMailer->sendEmailToUser( 1, 'test', 'test' );
    }

    public function testSendEmailToUserAndSpecifyEmailID() {
        $this->setReturnValue( $this->mockCms, 'sendWpEmail', true );
        $this->setReturnValue( $this->mockCms, 'getUserEmail', 'me@test.com' );

        $this->expectAtLeastOnce( $this->MockMysqlDatabase, 'recordEmail', array(1, 'test subject', 'test body', 123, 'me@test.com') );

        $this->MockedMailer->sendEmailToUserAndSpecifyEmailID( 1, 'test subject', 'test body', 123 );
    }

    public function testIsThrottledReturnsFalse() {
        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceAnyReport', 100 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceLastEmail', 10 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceSecondToLastEmail', 12 );

        $result = $this->MockedMailer->isThrottled( 1 );

        $this->assertEquals( $result, false );
    }

    public function testIsThrottledReturnsTrue() {
        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceAnyReport', 100 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceLastEmail', 10 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'daysSinceSecondToLastEmail', 17 );

        $result = $this->MockedMailer->isThrottled( 1 );

        $this->assertEquals( $result, true );
    }

    public function testMailerPointsInviteUrlToRegistrationTab() {
        $this->setReturnValue( $this->mockAssetLocator, 'getPageUrlByTitle', 'habitfree.org/authenticate' );

        $result   = $this->MockedMailer->generateInviteURL( 777 );
        $expected = 'habitfree.org/authenticate?n=777&tab=2';

        $this->assertEquals( $expected, $result );
    }

    public function testIsEmailValid() {
        $this->setReturnValue( $this->MockMysqlDatabase, 'isEmailValid', true );

        $this->assertTrue( $this->MockedMailer->isEmailValid( 1, 1 ) );
    }

    public function testSendReportRequestEmailGeneratesNonce() {
        $this->expectAtLeastOnce( $this->MockSecurity, 'createRandomString', array(250) );
        $this->MockedMailer->sendReportRequestEmail( 1 );
    }

    public function testGenerateReportUrlUsesNonce() {
        $baseURL = 'habitfree.org/test';
        $this->setReturnValue( $this->mockAssetLocator, 'getPageUrlByTitle', $baseURL );

        $actual = $this->MockedMailer->generateReportURL( 555 );

        $expected = $baseURL . '?n=555';

        $this->assertEquals( $expected, $actual );
    }

    public function testSendReportRequestEmailUsesNonceToCreateUrl() {
        $this->setReturnValue( $this->MockSecurity, 'createRandomString', 555 );
        $baseURL = 'habitfree.org/test';
        $this->setReturnValue( $this->mockAssetLocator, 'getPageUrlByTitle', $baseURL );
        $reportUrl = $baseURL . '?n=555';

        $userID  = 1;
        $subject = "How's it going?";
        $body    = "<p>Time to <a href='" . $reportUrl . "'>check in</a>.</p>";
        $emailID = null;
        $to      = null;

        $this->expectOnce( $this->MockMysqlDatabase, 'recordEmail', array($userID, $subject, $body, $emailID, $to) );

        $this->MockedMailer->sendReportRequestEmail( $userID );
    }

    public function testSendReportRequestEmailRecordsRequest() {
        $this->setReturnValue( $this->MockSecurity, 'createRandomString', 555 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'generateEmailID', 7 );

        $userId  = 1;
        $emailId = 7;

        $this->expectOnce( $this->MockMysqlDatabase, 'recordReportRequest', array(555, $userId, $emailId) );

        $this->MockedMailer->sendReportRequestEmail( $userId );
    }

    public function testIsReportRequestValid() {
        $this->setReturnValue( $this->MockMysqlDatabase, 'isReportrequestValid', true );
        $this->assertTrue( $this->MockedMailer->isReportRequestValid( 555 ) );
    }

    public function testIsReportRequestValidReturnsFalse() {
        $this->setReturnValue( $this->MockMysqlDatabase, 'isReportrequestValid', false );
        $this->assertFalse( $this->MockedMailer->isReportRequestValid( 555 ) );
    }

    public function testDeleteReportRequest() {
        $this->expectOnce( $this->MockMysqlDatabase, 'deleteReportRequest', array(555) );
        $this->MockedMailer->deleteReportRequest( 555 );
    }

    public function testGetReportRequestUserId() {
        $this->expectOnce( $this->MockMysqlDatabase, 'getReportRequestUserId', array(555) );
        $this->MockedMailer->getReportRequestUserId( 555 );
    }

    public function testGetReportRequestUserIdReturnsValue() {
        $this->setReturnValue( $this->MockMysqlDatabase, 'getReportRequestUserId', 5 );
        $actual = $this->MockedMailer->getReportRequestUserId( 555 );
        $this->assertEquals( 5, $actual );
    }

    public function testUpdateReportRequestExpirationDate() {
        $this->expectOnce( $this->MockMysqlDatabase, 'updateReportRequestExpirationDate', array(555, 'abcd') );
        $this->MockedMailer->updateReportRequestExpirationDate( 555, 'abcd' );
    }

    public function testMailerCreatesLigitExpirationDateForReportRequests() {
        $this->setReturnValue( $this->MockSecurity, 'createRandomString', 123 );
        $this->setReturnValue( $this->MockMysqlDatabase, 'generateEmailId', 5 );
        $this->setReturnValue( $this->MockPhpLibrary, 'getCurrentTime', 1403551104 );

        $this->expectOnce( $this->MockMysqlDatabase, 'recordReportRequest', array(123, 1, 5, "2014-06-30 14:18:24") );

        $this->MockedMailer->sendReportRequestEmail( 1 );
    }

    public function testDeleteExpiredInvitesGetsInvites() {
        $mockInvite = $this->makeMockInvite( '2014-6-20 13:22:12', '5ab' );

        $mockInvites = array($mockInvite);
        $this->setReturnValue( $this->MockMysqlDatabase, 'getAllInvites', $mockInvites );

        $this->expectOnce( $this->MockMysqlDatabase, 'getAllInvites' );

        $this->MockedMailer->deleteExpiredInvites();
    }

    public function testDeleteExpiredInvitesDeletesExpiredInvite() {
        $mockInvite = $this->makeMockInvite( '2014-6-20 13:22:12', '5ab' );

        $this->setReturnValue( $this->MockPhpLibrary, 'getCurrentTime', time() );
        $mockInvites = array($mockInvite);
        $this->setReturnValue( $this->MockMysqlDatabase, 'getAllInvites', $mockInvites );

        $this->expectOnce( $this->MockMysqlDatabase, 'deleteInvite', array('5ab') );

        $this->MockedMailer->deleteExpiredInvites();
    }

    public function testDeleteExpiredInviteDoesNotDeleteFreshInvite() {
        $expirationTime = strtotime( '+' . 3 . ' days' );
        $expirationDate = date( 'Y-m-d H:i:s', $expirationTime );

        $this->setReturnValue( $this->MockPhpLibrary, 'getCurrentTime', time() );

        $mockInvite = $this->makeMockInvite( $expirationDate, 'fresh' );
        $mockInvites = array($mockInvite);

        $this->setReturnValue( $this->MockMysqlDatabase, 'getAllInvites', $mockInvites );

        $this->expectNever( $this->MockMysqlDatabase, 'deleteInvite' );

        $this->MockedMailer->deleteExpiredInvites();
    }

    public function testDeleteExpiredInviteDeletesStaleKeepsFresh() {
        $this->setReturnValue( $this->MockPhpLibrary, 'getCurrentTime', time() );

        $freshExpirationDate = $this->makeFreshExpirationDate();

        $freshMockInvite = $this->makeMockInvite( $freshExpirationDate, 'fresh' );
        $staleMockInvite = $this->makeMockInvite( '2014-6-20 13:22:12', 'stale' );

        $mockInvites = array($freshMockInvite, $staleMockInvite);

        $this->setReturnValue( $this->MockMysqlDatabase, 'getAllInvites', $mockInvites );

        $this->expectOnce( $this->MockMysqlDatabase, 'deleteInvite' );
        $this->expectOnce( $this->MockMysqlDatabase, 'deleteInvite', array('stale') );

        $this->MockedMailer->deleteExpiredInvites();
    }

    public function testDeleteExpiredReportRequestsGetsReportRequests() {
        $staleReportRequest = $this->makeMockReportRequest('2014-6-20 13:22:12', 'stale');
        $this->setReturnValue($this->MockMysqlDatabase, 'getAllReportRequests', array($staleReportRequest));

        $this->expectOnce($this->MockMysqlDatabase, 'getAllReportRequests');
        $this->MockedMailer->deleteExpiredReportRequests();
    }

    public function testDeleteExpiredReportRequestsDeletesExpiredReportRequest() {
        $this->setReturnValue( $this->MockPhpLibrary, 'getCurrentTime', time() );

        $staleReportRequest = $this->makeMockReportRequest('2014-6-20 13:22:12', 'stale');
        $this->setReturnValue($this->MockMysqlDatabase, 'getAllReportRequests', array($staleReportRequest));

        $this->expectOnce($this->MockMysqlDatabase, 'deleteReportRequest', array('stale'));
        $this->MockedMailer->deleteExpiredReportRequests();
    }

    public function testDeleteExpiredReportRequestsDoesNotDeleteFreshReportRequest() {
        $this->setReturnValue( $this->MockPhpLibrary, 'getCurrentTime', time() );

        $freshExpirationDate = $this->makeFreshExpirationDate();

        $freshReportRequest = $this->makeMockReportRequest($freshExpirationDate, 'fresh');
        $this->setReturnValue($this->MockMysqlDatabase, 'getAllReportRequests', array($freshReportRequest));

        $this->expectNever($this->MockMysqlDatabase, 'deleteReportRequest');
        $this->MockedMailer->deleteExpiredReportRequests();
    }

    public function testDeleteExpiredReportRequestsDeletesStaleAndKeepsFresh() {
        $this->setReturnValue( $this->MockPhpLibrary, 'getCurrentTime', time() );

        $freshExpirationDate = $this->makeFreshExpirationDate();

        $freshReportRequest = $this->makeMockReportRequest($freshExpirationDate, 'fresh');
        $staleReportRequest = $this->makeMockReportRequest('2014-6-20 13:22:12', 'stale');

        $this->setReturnValue($this->MockMysqlDatabase, 'getAllReportRequests', array($freshReportRequest, $staleReportRequest));

        $this->expectOnce($this->MockMysqlDatabase, 'deleteReportRequest');
        $this->expectOnce($this->MockMysqlDatabase, 'deleteReportRequest', array('stale'));

        $this->MockedMailer->deleteExpiredReportRequests();
    }
}