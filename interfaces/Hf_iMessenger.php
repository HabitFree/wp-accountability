<?php

interface Hf_iMessenger {
    public function sendEmailToAddress( $address, $subject, $body );

    public function generateSecureEmailId();

    public function isThrottled( $userID );

    public function generateInviteURL( $inviteID );

    public function sendReportRequestEmail( $userId );

    public function generateReportURL( $reportRequestId );

    public function sendEmailToUser( $userID, $subject, $body );

    public function sendEmailToUserAndSpecifyEmailID( $userID, $subject, $body, $emailID );

    public function recordInvite( $inviteID, $inviterID, $inviteeEmail, $emailID, $expirationDate );

    public function isEmailValid( $userId, $emailId );

    public function isReportRequestValid( $requestId );

    public function deleteReportRequest( $requestId );

    public function getReportRequestUserId( $requestId );

    public function updateReportRequestExpirationDate( $requestId, $expirationTime );

    public function deleteExpiredInvites();

    public function deleteExpiredReportRequests();
}