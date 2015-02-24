<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfGoals implements Hf_iGoals {
    private $Database;
    private $MarkupGenerator;
    private $ContentManagementSystem;
    private $Messenger;

    function __construct(
        Hf_iMessenger $Messenger,
        Hf_iCms $ContentManagementSystem,
        Hf_iMarkupGenerator $MarkupGenerator,
        Hf_iDatabase $Database
    ) {
        $this->Messenger               = $Messenger;
        $this->ContentManagementSystem = $ContentManagementSystem;
        $this->MarkupGenerator         = $MarkupGenerator;
        $this->Database                = $Database;
    }

    function generateGoalCard( $sub ) {
        $userID        = intval( $sub->userID );

        $goalID        = intval( $sub->goalID );
        $goal          = $this->Database->getGoal( $goalID );
        $daysOfSuccess = $this->daysOfSuccess( $goalID, $userID );
        $daysSinceLastReport = round($this->Database->daysSinceLastReport($goalID, $userID));

        $level         = $this->Database->getLevel( $daysOfSuccess );
        $levelPercentComplete = round($this->levelPercentComplete($goalID, $userID), 1);
        $levelDaysToComplete = round($this->daysToNextLevel($goalID, $userID));
        $bar          = $this->levelBarForGoal( $goalID, $userID );

        $card = $this->MarkupGenerator->makeGoalCard(
            $goal->title,
            $goal->description,
            $goalID,
            $daysSinceLastReport,
            $level->levelID,
            $level->title,
            $levelPercentComplete,
            $levelDaysToComplete,
            $bar
        );

        return $card;
    }

    private function daysOfSuccess( $goalId, $userId ) {
        $dateInSecondsOfFirstSuccess = $this->Database->timeOfFirstSuccess( $goalId, $userId );
        $dateInSecondsOfLastSuccess  = $this->Database->timeOfLastSuccess( $goalId, $userId );
        $dateInSecondsOfLastFail     = $this->Database->timeOfLastFail( $goalId, $userId );

        $secondsInADay = 86400;

        if ( !$dateInSecondsOfLastSuccess ) {
            $daysOfSuccess = 0;
        } elseif ( !$dateInSecondsOfLastFail ) {
            $daysOfSuccess = ( $dateInSecondsOfLastSuccess - $dateInSecondsOfFirstSuccess ) / $secondsInADay;
        } else {
            $difference    = $dateInSecondsOfLastSuccess - $dateInSecondsOfLastFail;
            $daysOfSuccess = $difference / $secondsInADay;
            if ( $daysOfSuccess < 0 ) {
                $daysOfSuccess = 0;
            }
        }

        return $daysOfSuccess;
    }

    function levelPercentComplete( $goalId, $userId ) {
        $daysOfSuccess = $this->daysOfSuccess( $goalId, $userId );

        return ( $this->daysOfSuccess( $goalId, $userId ) / $this->currentLevelTarget( $daysOfSuccess ) ) * 100;
    }

    function daysToNextLevel( $goalId, $userId ) {
        $daysOfSuccess = $this->daysOfSuccess( $goalId, $userId );
        $target        = $this->currentLevelTarget( $daysOfSuccess );

        return $target - $daysOfSuccess;
    }

    function levelBarForGoal( $goalId, $userId ) {
        $percent = $this->levelPercentComplete( $goalId, $userId );

        return $this->MarkupGenerator->progressBar( $percent, '' );
    }

    function currentLevelTarget( $daysOfSuccess ) {
        $level = $this->Database->getLevel( $daysOfSuccess );

        return $level->target;
    }

    function sendReportRequestEmails() {
        $users = $this->ContentManagementSystem->getSubscribedUsers();

        foreach ( $users as $user ) {
            if ( $this->isAnyGoalDue( $user->ID ) and !$this->Messenger->isThrottled( $user->ID ) ) {
                $this->Messenger->sendReportRequestEmail( $user->ID );
            }
        }
    }

    private function isAnyGoalDue( $userId ) {
        $goalSubs = $this->getGoalSubscriptions( $userId );
        foreach ( $goalSubs as $goalSub ) {
            if ( $this->isGoalDue( $goalSub->goalID, $userId ) ) {
                return true;
            }
        }

        return false;
    }

    public function getGoalSubscriptions( $userId ) {
        return $this->Database->getGoalSubscriptions( $userId );
    }

    private function isGoalDue( $goalId, $userId ) {
        $daysOfSuccess       = $this->daysOfSuccess( $goalId, $userId );
        $level               = $this->Database->getLevel( $goalId, $userId, $daysOfSuccess );
        $emailInterval       = $level->emailInterval;
        $daysSinceLastReport = $this->Database->daysSinceLastReport( $goalId, $userId );

        return $daysSinceLastReport > $emailInterval;
    }

    public function getGoalTitle( $goalId ) {
        return $this->Database->getGoal( $goalId )->title;
    }

    public function recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId = null ) {
        $this->Database->recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId );
    }
} 