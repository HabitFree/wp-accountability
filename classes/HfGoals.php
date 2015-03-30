<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfGoals implements Hf_iGoals {
    private $database;
    private $markupGenerator;
    private $cms;
    private $messenger;
    private $codeLibrary;

    function __construct(
        Hf_iMessenger $messenger,
        Hf_iCms $cms,
        Hf_iMarkupGenerator $markupGenerator,
        Hf_iDatabase $database,
        Hf_iCodeLibrary $codeLibrary
    ) {
        $this->messenger = $messenger;
        $this->cms = $cms;
        $this->markupGenerator = $markupGenerator;
        $this->database = $database;
        $this->codeLibrary = $codeLibrary;
    }

    function generateGoalCard( $sub ) {
        $userID        = intval( $sub->userID );

        $goalID        = intval( $sub->goalID );
        $goal          = $this->database->getGoal( $goalID );
        $daysOfSuccess = $this->currentStreak( $goalID, $userID );
        $daysSinceLastReport = $this->database->daysSinceLastReport($goalID, $userID);

        $level         = $this->database->getLevel( $daysOfSuccess );
        $levelPercentComplete = round($this->levelPercentComplete($goalID, $userID), 1);
        $levelDaysToComplete = round($this->daysToNextLevel($goalID, $userID));
        $bar          = $this->goalProgressBar( $goalID, $userID );

        $card = $this->markupGenerator->makeGoalCard(
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
        $dateInSecondsOfFirstSuccess = $this->database->timeOfFirstSuccess( $goalId, $userId );
        $dateInSecondsOfLastSuccess  = $this->database->timeOfLastSuccess( $goalId, $userId );
        $dateInSecondsOfLastFail     = $this->database->timeOfLastFail( $goalId, $userId );

        if ( !$dateInSecondsOfLastSuccess ) {
            return 0;
        } else {
            $secondsOfSuccess = $this->determineSecondsOfSuccess(
                $dateInSecondsOfLastFail,
                $dateInSecondsOfLastSuccess,
                $dateInSecondsOfFirstSuccess
            );

            return $this->determineDaysOfSuccess($secondsOfSuccess);
        }
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
        $label = $this->cms->prepareQuery('%d / %d',array($currentStreak,$longestStreak));
        return $this->markupGenerator->progressBar( $percent, $label );
    }

    function currentLevelTarget( $daysOfSuccess ) {
        $level = $this->database->getLevel( $daysOfSuccess );

        return $level->target;
    }

    function sendReportRequestEmails() {
        $users = $this->cms->getSubscribedUsers();

        foreach ( $users as $user ) {
            if ( $this->isAnyGoalDue( $user->ID ) and !$this->messenger->isThrottled( $user->ID ) ) {
                $this->messenger->sendReportRequestEmail( $user->ID );
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
        return $this->database->getGoalSubscriptions( $userId );
    }

    private function isGoalDue( $goalId, $userId ) {
        $daysOfSuccess       = $this->currentStreak( $goalId, $userId );
        $level               = $this->database->getLevel( $goalId, $userId, $daysOfSuccess );
        $emailInterval       = $level->emailInterval;
        $daysSinceLastReport = $this->database->daysSinceLastReport( $goalId, $userId );

        return $daysSinceLastReport > $emailInterval;
    }

    public function getGoalTitle( $goalId ) {
        return $this->database->getGoal( $goalId )->title;
    }

    public function recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId = null ) {
        $this->database->recordAccountabilityReport( $userId, $goalId, $isSuccessful, $emailId );
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
        $reports = $this->database->getAllReportsForGoal($goalId, $userId);
        $longestStreak = 0;
        $candidateStreak = 0;
        foreach ($reports as $report) {
            $currentTime = $this->codeLibrary->convertStringToTime($report->date);
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

    private function determineSecondsOfSuccess($dateInSecondsOfLastFail, $dateInSecondsOfLastSuccess, $dateInSecondsOfFirstSuccess)
    {
        if (!$dateInSecondsOfLastFail) {
            $secondsOfSuccess = $dateInSecondsOfLastSuccess - $dateInSecondsOfFirstSuccess;
            return $secondsOfSuccess;
        } else {
            $secondsOfSuccess = $dateInSecondsOfLastSuccess - $dateInSecondsOfLastFail;
            return $secondsOfSuccess;
        }
    }

    private function determineDaysOfSuccess($secondsOfSuccess)
    {
        $daysOfSuccess = $this->convertSecondsToDays($secondsOfSuccess);
        if ($daysOfSuccess < 0) {
            $daysOfSuccess = 0;
            return $daysOfSuccess;
        }
        return $daysOfSuccess;
    }
} 