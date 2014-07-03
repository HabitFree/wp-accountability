<?php

interface Hf_iDatabase {
    public function recordEmail( $userID, $subject, $message, $emailID = null, $emailAddress = null );

    public function insertMultipleRows( $table, $rows );

    public function generateEmailId();

    public function daysSinceLastEmail( $userID );

    public function timeOfLastSuccess( $goalID, $userID );

    public function getFullTableName( $table );

    public function idOfLastEmail();

    public function insertIgnoreIntoDb( $table, $data );

    public function daysSinceSecondToLastEmail( $userID );

    public function daysSinceLastReport( $goalID, $userID );

    public function getRows( $table, $where, $outputType = OBJECT );

    public function insertUpdateIntoDb( $table, $data );

    public function removeNullValuePairs( $array );

    public function installDb();

    public function insertIntoDb( $table, $data );

    public function createRelationship( $userOneID, $userTwoID );

    public function timeOfFirstSuccess( $goalID, $userID );

    public function isEmailValid( $userID, $emailID );

    public function getRow( $table, $criterion );

    public function timeOfLastFail( $goalID, $userID );

    public function deleteRow( $table, $where );

    public function level( $daysOfSuccess );

    public function countRowsInTable( $table );

    public function recordAccountabilityReport( $userID, $goalID, $isSuccessful, $emailID = null );

    public function updateRows( $table, $data, $where );

    public function daysSinceAnyReport( $userID );

    public function getTable( $table );

    public function deleteInvite( $inviteID );

    public function getGoalSubscriptions( $userID );

    public function escapeData( $data );

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
}