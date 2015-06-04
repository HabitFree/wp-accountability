<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HfStreaks implements Hf_iStreaks {
    private $database;
    private $codeLibrary;

    public function __construct(
        Hf_iDatabase $database,
        Hf_iCodeLibrary $codeLibrary
    ) {
        $this->database = $database;
        $this->codeLibrary = $codeLibrary;
    }

    public function streaks(
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
            $streaks[] = $candidateStreak;
            return $streaks;
        }

        $report = array_shift($reports);
        $reportTime = $this->codeLibrary->convertStringToTime($report->date);

        if (!($report->isSuccessful) && !empty($candidateStreak)) {
            $streaks[] = $candidateStreak;
        }

        $candidateStreak = $this->newCandidateStreak($candidateStreak, $lastTime, $report->isSuccessful, $reportTime);

        return $this->streaks(
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

    private function streakExtension($reportTime, $lastTime)
    {
        if ($lastTime === null) {
            return 1;
        } else {
            $seconds = $reportTime - $lastTime;
            return $this->convertSecondsToDays($seconds);
        }
    }

    private function convertSecondsToDays($seconds)
    {
        $minutes = $seconds / 60;
        $hours = $minutes / 60;
        $days = $hours / 24;
        return $days;
    }
}