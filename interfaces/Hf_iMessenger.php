<?php

interface Hf_iMessenger {
    public function sendEmailToAddress( $address, $subject, $body );

    public function generateInviteID();

    public function isThrottled( $userID );

    public function generateInviteURL( $inviteID );

    public function sendReportRequestEmail( $userID );

    public function markAsDelivered( $emailID );

    public function generateReportURL( $userID, $emailID );

    public function sendEmailToUser( $userID, $subject, $body );

    public function sendEmailToUserAndSpecifyEmailID( $userID, $subject, $body, $emailID );

    public function recordInvite( $inviteID, $inviterID, $inviteeEmail, $emailID, $expirationDate );

    public function isEmailValid($userId, $emailId);
}