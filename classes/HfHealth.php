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

		$dayStats = $this->getDayStats($reports);

        return $this->calculateHealth($dayStats);
    }

    private function getDayStats($reports) {
		$firstDate = reset($reports)->date;
		$lastDate = end($reports)->date;
		$firstDateClean = explode(' ', $firstDate)[0];
		$lastDateClean = explode(' ', $lastDate)[0];

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

	public function getHealths( $goalId, $userId ) {
		$reports = $this->db->getAllReportsForGoal($goalId, $userId);

		if (!$reports) {
			$now = $this->php->getCurrentTime();
			$monthString = $this->makeMonthString( $now );
			return [[$monthString, 0]];
		}

		$lastDate = end($reports)->date;
		$lastDateClean = explode(' ', $lastDate)[0];

		$healths = [];

		for ($i = 365; $i > 0; $i--) {
			$time = strtotime("-$i days", strtotime($lastDateClean));

			$dayOfMonth = date( "d", $time );

			if ($dayOfMonth === '01') {
				$healths[] = [
					$this->makeMonthString( $time ),
					$this->calculateHealthAtTime( $time, $reports )
				];
			}
		}

    	return $healths;
	}

	private function makeMonthString( $time )
	{
		return date( "n/Y", $time );
	}

	private function calculateHealthAtTime( $time, $reports ) {


    	$relevantReports = array_filter( $reports, function($report) use($time) {
    		$statTime = strtotime( $report->date );
    		return $statTime <= $time;
		} );

    	if ($relevantReports) {
			$dayStats = $this->getDayStats($relevantReports);
			return $this->calculateHealth( $dayStats );
		}

		return 0;
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
}