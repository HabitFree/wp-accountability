<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HfHealth implements Hf_iHealth
{
    private $db;
    private $php;

    public function __construct(
        HfMysqlDatabase $database,
		HfPhpLibrary $php
    ) {
        $this->db = $database;
        $this->php = $php;
    }

    public function getHealth($goalId, $userId)
    {
        $reports = $this->db->getAllReportsForGoal($goalId, $userId);
        if (!$reports) {
            return 0;
        }

        $firstDate = reset($reports)->date;
        $lastDate = end($reports)->date;
        $firstDateClean = explode(' ', $firstDate)[0];
        $lastDateClean = explode(' ', $lastDate)[0];

        $dayStats = $this->getDayStats($firstDateClean, $lastDateClean, $reports);

        return $this->calculateHealth($dayStats);
    }

    private function getDayStats($firstDateClean, $lastDateClean, $reports) {
        $status = 0;
        $dayStats = [];
        $timeLimit = strtotime($firstDateClean);

        for ($i = 0; $i < 365; $i++) {
            $time = strtotime("-$i days", strtotime($lastDateClean));
            if ($time < $timeLimit) break;

            $status = $this->getDayStatus($reports, $time, $status);
            
            array_unshift($dayStats, $status);
        }
        return $dayStats;
    }

    private function getDayStatus($reports, $time, $lastStatus) {
        $dayReports = $this->getReportsForDay($reports, $time);

        if ($dayReports === null) {
            return $lastStatus;
        }

        for ($i = 0; $i < count($dayReports); $i++) {
            if ($dayReports[$i]->isSuccessful == 0) {
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

    private function calculateHealth( $dayStatuses) {
        $health = 0;

        foreach ( $dayStatuses as $status ) {
            $health = $this->adjustedHealth( $health, $status );
        }

        return round( $health, 3 );
    }

    private function adjustedHealth( $previousHealth, $nextStatus ) {
		$exponent = 2 / 97;

		return (intval($nextStatus) * $exponent) + ($previousHealth * (1-$exponent));
	}

	public function getHealths() {
    	$time = $this->php->getCurrentTime();
    	$monthString = date( "n/Y", $time );

    	return [ $monthString, 0 ];
	}
}