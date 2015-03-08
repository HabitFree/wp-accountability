<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfMailer implements Hf_iMessenger {
    private $Security;
    private $Database;
    private $PageLocator;
    private $ContentManagementSystem;
    private $CodeLibrary;

    function HfMailer( Hf_iAssetLocator $PageLocator, Hf_iSecurity $Security, Hf_iDatabase $Database, Hf_iCms $ContentManagementSystem, Hf_iCodeLibrary $CodeLibrary ) {
        $this->Database                = $Database;
        $this->Security                = $Security;
        $this->PageLocator             = $PageLocator;
        $this->ContentManagementSystem = $ContentManagementSystem;
        $this->CodeLibrary             = $CodeLibrary;
    }

    function sendEmailToUser( $userId, $subject, $body ) {
        $to = $this->ContentManagementSystem->getUserEmail( $userId );
        $this->ContentManagementSystem->sendEmail( $to, $subject, $body );
        $emailId = intval( $this->ContentManagementSystem->getVar( 'hf_email', 'max(emailID)' ) );

        $this->Database->recordEmail( $userId, $subject, $body, $emailId, $to );
    }

    function sendEmailToAddress( $address, $subject, $body ) {
        $success = $this->ContentManagementSystem->sendEmail( $address, $subject, $body );
        $emailID = $this->Database->idOfLastEmail();

        if ( $success ) {
            $this->Database->recordEmail( null, $subject, $body, $emailID, $address );

            return $emailID;
        } else {
            return false;
        }
    }

    function sendReportRequestEmail( $userId ) {
        $subject = "How's it going?";
        $nonce = $this->makeNonce();
        $reportUrl = $this->generateReportURL($nonce);
        $body = $this->makeReportRequestBody($reportUrl);

        $this->sendAndRecordEmailWithAutoAuth($userId, $subject, $body, $nonce);
    }

    function generateReportURL( $reportRequestId ) {
        $baseURL = $this->PageLocator->getPageUrlByTitle( 'Goals' );

        $parameters = array(
            'n' => $reportRequestId
        );

        return $this->addParametersToUrl( $baseURL, $parameters );
    }

    function sendEmailToUserAndSpecifyEmailID( $userID, $subject, $body, $emailID ) {
        $to = $this->ContentManagementSystem->getUserEmail( $userID );

        $this->ContentManagementSystem->sendEmail( $to, $subject, $body );
        $this->Database->recordEmail( $userID, $subject, $body, $emailID, $to );
    }

    private function generateReportRequestExpirationDate() {
        $oneWeek         = 7 * 24 * 60 * 60;
        $timePlusOneWeek = $this->CodeLibrary->getCurrentTime() + $oneWeek;

        return date( 'Y-m-d H:i:s', $timePlusOneWeek );
    }

    function generateSecureEmailId() {
        return $this->Security->createRandomString( 250 );
    }

    function generateInviteURL( $inviteID ) {
        $baseURL = $this->PageLocator->getPageUrlByTitle( 'Authenticate' );

        $parameters = array(
            'n'   => $inviteID,
            'tab' => 2
        );

        return $this->addParametersToUrl( $baseURL, $parameters );
    }

    public function isThrottled( $userID ) {
        if ( $this->hasNotRespondedSinceLastEmail( $userID ) ) {
            return !$this->emailIntervalExceeded( $userID );
        } else {
            return false;
        }
    }

    private function hasNotRespondedSinceLastEmail( $userID ) {
        $daysSinceLastReport = $this->Database->daysSinceAnyReport( $userID );
        $daysSinceLastEmail  = $this->Database->daysSinceLastEmail( $userID );

        return $daysSinceLastReport > $daysSinceLastEmail;
    }

    private function daysBetweenLastTwoEmails( $userID ) {
        $daysSinceLastEmail         = $this->Database->daysSinceLastEmail( $userID );
        $daysSinceSecondToLastEmail = $this->Database->daysSinceSecondToLastEmail( $userID );

        return $daysSinceSecondToLastEmail - $daysSinceLastEmail;
    }

    private function emailIntervalExceeded( $userID ) {
        $daysSinceLastEmail = $this->Database->daysSinceLastEmail( $userID );
        $currentInterval    = $this->currentInterval( $userID );

        return $daysSinceLastEmail > $currentInterval;
    }

    private function currentInterval( $userID ) {
        $lastInterval    = $this->daysBetweenLastTwoEmails( $userID );
        $currentInterval = $lastInterval * 2;

        return $currentInterval;
    }

    private function addParametersToUrl( $url, $parameters ) {
        $name  = key( $parameters );
        $value = array_shift( $parameters );

        if ( strpos( $url, '?' ) !== false ) {
            $url .= '&' . $name . '=' . $value;
        } else {
            $url .= '?' . $name . '=' . $value;
        }

        if ( count( $parameters ) > 0 ) {
            $url = $this->addParametersToUrl( $url, $parameters );
        }

        return $url;
    }

    public function isEmailValid( $userId, $emailId ) {
        return $this->Database->isEmailValid( $userId, $emailId );
    }

    public function isReportRequestValid( $requestId ) {
        return $this->Database->isReportRequestValid( $requestId );
    }

    public function deleteReportRequest( $requestId ) {
        $this->Database->deleteReportRequest( $requestId );
    }

    public function getReportRequestUserId( $requestId ) {
        return $this->Database->getReportRequestUserId( $requestId );
    }

    public function updateReportRequestExpirationDate( $requestId, $expirationTime ) {
        $this->Database->updateReportRequestExpirationDate( $requestId, $expirationTime );
    }

    public function deleteExpiredInvites() {
        $invites = $this->Database->getAllInvites();
        foreach ($invites as $invite) {
            if ($this->isInviteOrReportRequestExpired($invite)) {
                $this->Database->deleteInvite($invite->inviteID);
            }
        }
    }

    public function deleteExpiredReportRequests() {
        $reportRequests = $this->Database->getAllReportRequests();
        foreach ($reportRequests as $reportRequest) {
            if ($this->isInviteOrReportRequestExpired($reportRequest)) {
                $this->Database->deleteReportRequest($reportRequest->requestID);
            }
        }
    }

    private function isInviteOrReportRequestExpired( $inviteOrReportRequest ) {
        $ExpirationDate = date_create_from_format( 'Y-m-d H:i:s', $inviteOrReportRequest->expirationDate );
        $expirationTime = $ExpirationDate->getTimestamp();
        $isExpired      = $expirationTime < $this->CodeLibrary->getCurrentTime();

        return $isExpired;
    }

    private function sendAndRecordEmailWithAutoAuth($userId, $subject, $message, $nonce)
    {
        $emailId = $this->Database->generateEmailId();
        $this->sendEmailToUserAndSpecifyEmailID($userId, $subject, $message, $emailId);
        $expirationDate = $this->generateReportRequestExpirationDate();
        $this->Database->recordReportRequest($nonce, $userId, $emailId, $expirationDate);
    }

    private function makeReportRequestBody($reportUrl)
    {
        $message = "<p>Time to <a href='$reportUrl'>check in</a>.</p>";
        return $message;
    }

    public function sendReportNotificationEmail($partnerId, $userId, $subject, $report) {
        $nonce = $this->makeNonce();
        $this->sendAndRecordEmailWithAutoAuth($partnerId,$subject,$report,$nonce);
    }

    private function makeNonce()
    {
        return $this->Security->createRandomString(250);
    }
}