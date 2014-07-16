<?php

interface Hf_iDatabase {
    public function recordEmail( $userID, $subject, $message, $emailID = null, $emailAddress = null );

    public function generateEmailId();

    public function daysSinceLastEmail( $userID );

    public function timeOfLastSuccess( $goalID, $userID );

    public function idOfLastEmail();

    public function daysSinceSecondToLastEmail( $userId );

    public function daysSinceLastReport( $goalID, $userID );

    public function removeNullValuePairs( $array );

    public function installDb();

    public function insertIntoDb( $table, $data );

    public function createRelationship( $userOneID, $userTwoID );

    public function timeOfFirstSuccess( $goalID, $userID );

    public function isEmailValid( $userID, $emailID );

    public function getRow( $table, $criterion );

    public function timeOfLastFail( $goalID, $userID );

    public function level( $daysOfSuccess );

    public function recordAccountabilityReport( $userID, $goalID, $isSuccessful, $emailID = null );

    public function daysSinceAnyReport( $userID );

    public function deleteInvite( $inviteID );

    public function getGoalSubscriptions( $userID );

    public function getInviterID( $nonce );

    public function getPartners( $userId );

    public function getGoal( $goalId );

    public function recordReportRequest( $requestId, $userId, $emailId, $expirationDate );

    public function isReportRequestValid( $requestId );

    public function deleteReportRequest( $requestId );

    public function getReportRequestUserId( $requestId );

    public function updateReportRequestExpirationDate( $requestId, $expirationTime );

    public function getAllInvites();

    public function getAllReportRequests();

    public function getQuotations( $context );

    public function deleteRelationship( $userId1, $userId2 );

    public function setDefaultGoalSubscription( $userId );
}