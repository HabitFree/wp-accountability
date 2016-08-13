<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HfHealth implements Hf_iHealth
{
    private $db;

    public function __construct(
        Hf_iDatabase $database
    ) {
        $this->db = $database;
    }

    public function getHealth($goalId, $userId)
    {
        $reports = $this->db->getAllReportsForGoal($goalId, $userId);
        if (!$reports) {
            return 0;
        }

        $lastDate = end($reports)->date;
        $lastDateClean = explode(' ', $lastDate)[0];
        $dayStats = $this->getDayStats($lastDateClean, $reports);

        return $this->calculateHealth($dayStats);
    }

    private function getDayStats($lastDateClean, $reports) {
        $status = 0;
        $dayStats = [];

        for ($i = 0; $i < 365; $i++) {
            $time = strtotime("-$i days", strtotime($lastDateClean));
            $status = $this->getDayStatus($reports, $time, $status);

            array_unshift($dayStats, $status);
        }
        return $dayStats;
    }

    private function getDayStatus($reports, $time, $lastStatus) {
        $dayReports = $this->getReportsForDay($reports, $time);

        if ($dayReports === null) {
            return ($lastStatus === 0) ? 0 : $lastStatus;
        }

        for ($i = 0; $i < count($dayReports); $i++) {
            if ($dayReports[$i]->isSuccessful === 0) {
                return 0;
            }
        }

        return 1;
    }

    private function getReportsForDay($reports, $time) {
        $dateString = date('Y-m-d', $time);
        $results = [];
        foreach ($reports as $i => $report) {
            if (strpos($report->date, $dateString) !== false) {
                $results[] = $reports[$i];
            }
        }
        return ($results) ? $results : null;
    }

    private function calculateHealth($days) {
        $exponent = 2 / (365+1);
        $health = 0;

        foreach ($days as $day) {
            $health = (intval($day) * $exponent) + ($health * (1-$exponent));
        }

        return $health;
    }
}