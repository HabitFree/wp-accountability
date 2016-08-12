<?php
if ( ! defined( 'ABSPATH' ) ) exit;
interface Hf_iGoals {
    function currentLevelTarget( $daysOfSuccess );

    function goalCard( $sub );

    function sendReportRequestEmails();

    function levelPercentComplete( $goalId, $userId );

    function daysToNextLevel( $goalId, $userId );

    function getGoalTitle( $goalID );

    public function recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId );

    public function getGoalSubscriptions( $userId );
}