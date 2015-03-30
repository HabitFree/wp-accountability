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
        Hf_iDatabase $Database,
        Hf_iCodeLibrary $codeLibrary
    ) {
        $this->Messenger               = $Messenger;
        $this->ContentManagementSystem = $ContentManagementSystem;
        $this->MarkupGenerator         = $MarkupGenerator;
        $this->Database                = $Database;
        $this->codeLibaray = $codeLibrary;
    }

    function generateGoalCard( $sub ) {
        $userID        = intval( $sub->userID );

        $goalID        = intval( $sub->goalID );
        $goal          = $this->Database->getGoal( $goalID );
        $daysOfSuccess = $this->currentStreak( $goalID, $userID );
        $daysSinceLastReport = $this->Database->daysSinceLastReport($goalID, $userID);

        $level         = $this->Database->getLevel( $daysOfSuccess );
        $levelPercentComplete = round($this->levelPercentComplete($goalID, $userID), 1);
        $levelDaysToComplete = round($this->daysToNextLevel($goalID, $userID));
        $bar          = $this->goalProgressBar( $goalID, $userID );

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

    private function currentStreak( $goalId, $userId ) {
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
        $daysOfSuccess = $this->currentStreak( $goalId, $userId );

        return ( $this->currentStreak( $goalId, $userId ) / $this->currentLevelTarget( $daysOfSuccess ) ) * 100;
    }

    function daysToNextLevel( $goalId, $userId ) {
        $daysOfSuccess = $this->currentStreak( $goalId, $userId );
        $target        = $this->currentLevelTarget( $daysOfSuccess );

        return $target - $daysOfSuccess;
    }

    function goalProgressBar( $goalId, $userId ) {
        $currentStreak = $this->currentStreak($goalId,$userId);
        $longestStreak = $this->findLongestStreak($goalId, $userId);
        $percent = $this->determinePercentOfLongestStreak($longestStreak, $currentStreak);
        $this->ContentManagementSystem->prepareQuery('%d / %d',array($currentStreak,$longestStreak));
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
        $daysOfSuccess       = $this->currentStreak( $goalId, $userId );
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

    private function convertSecondsToDays($seconds)
    {
        $minutes = $seconds / 60;
        $hours = $minutes / 60;
        $days = $hours / 24;
        return $days;
    }

    private function findLongestStreak($goalId, $userId)
    {
        $reports = $this->Database->getAllReportsForGoal($goalId, $userId);
        $longestStreak = 0;
        $candidateStreak = 0;
        foreach ($reports as $report) {
            $currentTime = $this->codeLibaray->convertStringToTime($report->date);
            if ($report->isSuccessful == 1) {
                if (isset($lastTime)) {
                    $seconds = $currentTime - $lastTime;
                    $days = $this->convertSecondsToDays($seconds);
                    $candidateStreak += $days;
                    if ($candidateStreak > $longestStreak) {
                        $longestStreak = $candidateStreak;
                    }
                }
            } else {
                $candidateStreak = 0;
            }
            $lastTime = $currentTime;
        }
        return $longestStreak;
    }

    private function determinePercentOfLongestStreak($longestStreak, $currentStreak)
    {
        if ($longestStreak) {
            $percent = $currentStreak / $longestStreak;
            return $percent;
        } else {
            $percent = $currentStreak / 1;
            return $percent;
        }
    }
} 