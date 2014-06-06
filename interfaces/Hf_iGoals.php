<?php

interface Hf_iGoals {
    function daysOfSuccess( $goalID, $userID );

    function currentLevelTarget( $daysOfSuccess );

    function nextLevelName( $daysOfSuccess );

    function generateGoalCard( $sub );

    function sendReportRequestEmails();

    function levelBarForGoal( $goalID, $userID );

    function levelPercentComplete( $goalID, $userID );

    function daysToNextLevel( $goalID, $userID );
}