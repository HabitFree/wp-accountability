<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iDatabase {
    public function recordEmail( $userID, $subject, $message, $emailID = null, $emailAddress = null );

    public function generateEmailId();

    public function daysSinceLastEmail( $userID );

    public function timeOfLastSuccess( $goalId, $userId );

    public function idOfLastEmail();

    public function daysSinceSecondToLastEmail( $userId );

    public function daysSinceLastReport( $goalId, $userId );

    public function removeNullValuePairs( $array );

    public function installDb();

    public function createRelationship( $userOneID, $userTwoID );

    public function timeOfFirstSuccess( $goalId, $userId );

    public function isEmailValid( $userId, $emailId );

    public function timeOfLastFail( $goalId, $userId );

    public function getLevel( $daysOfSuccess );

    public function recordAccountabilityReport( $userID, $goalID, $isSuccessful, $emailID = null );

    public function daysSinceAnyReport( $userId );

    public function deleteInvite( $inviteID );

    public function getGoalSubscriptions( $userId );

    public function getInviterId( $nonce );

    public function getPartners( $userId );

    public function getGoal( $goalId );

    public function recordReportRequest( $nonceString, $userId, $emailId, $expirationDate );

    public function isReportRequestValid( $requestId );

    public function deleteReportRequest( $requestId );

    public function getReportRequestUserId( $requestId );

    public function updateReportRequestExpirationDate( $requestId, $expirationTime );

    public function getAllInvites();

    public function getAllReportRequests();

    public function getQuotations( $context );

    public function deleteRelationship( $userId1, $userId2 );

    public function setDefaultGoalSubscription( $userId );

    public function getAllReportsForGoal($goalId, $userId);
}