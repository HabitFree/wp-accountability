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

        $streaks = $this->findStreaks($goalID,$userID);

/*        usort($streaks, function ($a, $b) {
            $a_val = (int) strtotime($a['date']);
            $b_val = (int) strtotime($b['date']);

            if($a_val > $b_val) return 1;
            if($a_val < $b_val) return -1;
            return 0;
        });*/

        $card = $this->markupGenerator->goalCard(
            $goalID,
            $goal->title,
            $daysSinceLastReport,
            $streaks
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

    private function streakExtension($reportTime, $lastTime)
    {
        if ($lastTime === null) {
            return 1;
        } else {
            $seconds = $reportTime - $lastTime;
            return $this->convertSecondsToDays($seconds);
        }
    }

    private function findStreaks(
        $goalId,
        $userId,
        $reports = null,
        $streaks = array(),
        $candidateStreak = 0,
        $lastTime = null,
        $lastReport = null
    ) {
        if ($reports === null) {
            $reports = $this->database->getAllReportsForGoal($goalId, $userId);
        }

        if (empty($reports)) {
            $streaks[] = $this->newStreak($candidateStreak, $lastReport);
            return $streaks;
        }

        $report = array_shift($reports);
        $reportTime = $this->codeLibrary->convertStringToTime($report->date);
        $isReportSuccessful = $report->isSuccessful == 1;

        if (!$isReportSuccessful && !empty($candidateStreak)) {
            $streaks[] = $this->newStreak($candidateStreak, $lastReport);
        }

        $candidateStreak = $this->newCandidateStreak($candidateStreak, $lastTime, $isReportSuccessful, $reportTime);

        return $this->findStreaks(
            $goalId,
            $userId,
            $reports,
            $streaks,
            $candidateStreak,
            $reportTime,
            $report
        );
    }

    private function newCandidateStreak($candidateStreak, $lastTime, $isReportSuccessful, $reportTime)
    {
        return ($isReportSuccessful) ? $candidateStreak + $this->streakExtension($reportTime, $lastTime) : 0;
    }

    private function newStreak($candidateStreak, $lastReport)
    {
        $date = $lastReport->date;
        $date = explode(' ',$date)[0];
        return array(
            'length' => $candidateStreak,
            'date' => $date
        );
    }
} 