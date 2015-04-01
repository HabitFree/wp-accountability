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
        $daysSinceLastReport = $this->database->daysSinceLastReport($goalID, $userID);

        $currentStreak = $this->currentStreak($goalID,$userID);
        $longestStreak = $this->findLongestStreak($goalID,$userID);

        $bar          = $this->goalProgressBar( $goalID, $userID );

        $card = $this->markupGenerator->makeGoalCard(
            $goal->title,
            $goal->description,
            $goalID,
            $daysSinceLastReport,
            $currentStreak,
            $longestStreak,
            $bar
        );

        return $card;
    }

    private function currentStreak( $goalId, $userId ) {
        $streaks = $this->findStreaks($goalId, $userId);
        return end($streaks);
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
        $label = $this->makeProgressBarLabel($longestStreak, $currentStreak);
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
        $streaks = $this->findStreaks($goalId, $userId);
        return max($streaks);
    }

    private function determinePercentOfLongestStreak($longestStreak, $currentStreak)
    {
        if ($longestStreak) {
            $percent = $currentStreak / $longestStreak;
            return $percent;
        } else {
            return 1;
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

    private function makeProgressBarLabel($longestStreak, $currentStreak)
    {
        $timeToLongestStreak = round($longestStreak - $currentStreak, 1);
        if ($timeToLongestStreak == 0) {
            $label = 'Longest streak!';
            return $label;
        } elseif ($timeToLongestStreak == 1) {
            $label = "$timeToLongestStreak day to longest streak";
            return $label;
        } else {
            $label = "$timeToLongestStreak days to longest streak";
            return $label;
        }
    }

    private function streakExtension($reportTime, $lastTime)
    {
        $seconds = $reportTime - $lastTime;
        $days = $this->convertSecondsToDays($seconds);
        return $days;
    }

    private function findStreaks($goalId, $userId)
    {
        $reports = $this->database->getAllReportsForGoal($goalId, $userId);
        $streaks = array();
        $candidateStreak = 0;
        foreach ($reports as $report) {
            $reportTime = $this->codeLibrary->convertStringToTime($report->date);
            if ($report->isSuccessful == 1 && isset($lastTime)) {
                $candidateStreak += $this->streakExtension($reportTime, $lastTime);
            } else {
                $streaks[] = $candidateStreak;
                $candidateStreak = 0;
            }
            $lastTime = $reportTime;
        }
        $streaks[] = $candidateStreak;
        return $streaks;
    }
} 