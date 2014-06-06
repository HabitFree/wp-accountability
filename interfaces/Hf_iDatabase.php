<?php

interface Hf_iDatabase {
    public function recordEmail( $userID, $subject, $message, $emailID = null, $emailAddress = null );

    public function insertMultipleRows( $table, $rows );

    public function generateEmailID();

    public function daysSinceLastEmail( $userID );

    public function timeOfLastSuccess( $goalID, $userID );

    public function getFullTableName( $table );

    public function idOfLastEmail();

    public function insertIgnoreIntoDb( $table, $data );

    public function daysSinceSecondToLastEmail( $userID );

    public function daysSinceLastReport( $goalID, $userID );

    public function emptyTable( $table );

    public function createColumnSchemaObject( $field, $type, $null, $key, $default, $extra );

    public function getRows( $table, $where, $outputType = OBJECT );

    public function insertUpdateIntoDb( $table, $data );

    public function removeNullValuePairs( $array );

    public function installDb();

    public function insertIntoDb( $table, $data );

    public function createRelationship( $userOneID, $userTwoID );

    public function timeOfFirstSuccess( $goalID, $userID );

    public function emailIsValid( $userID, $emailID );

    public function getRow( $table, $criterion );

    public function sudoReactivateExtension();

    public function timeOfLastFail( $goalID, $userID );

    public function deleteRow( $table, $where );

    public function level( $daysOfSuccess );

    public function countRowsInTable( $table );

    public function submitAccountabilityReport( $userID, $goalID, $isSuccessful, $emailID = null );

    public function updateRows( $table, $data, $where );

    public function daysSinceAnyReport( $userID );

    public function getTable( $table );

    public function deleteInvite( $inviteID );

    public function getGoalSubscriptions( $userID );

    public function getTableSchema( $table );

    public function escapeData( $data );

    public function getInvite( $nonce );
}