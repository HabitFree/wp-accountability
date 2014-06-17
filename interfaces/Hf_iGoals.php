<?php

interface Hf_iGoals {
    function daysOfSuccess( $goalId, $userId );

    function currentLevelTarget( $daysOfSuccess );

    function generateGoalCard( $sub );

    function sendReportRequestEmails();

    function levelBarForGoal( $goalId, $userId );

    function levelPercentComplete( $goalId, $userId );

    function daysToNextLevel( $goalId, $userId );

    function getGoalTitle( $goalID );

    public function recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId );

    public function getGoalSubscriptions( $userId );
}