<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase2.php');

class TestHealth extends HfTestCase2 {
    private function getHealth($days) {
        $exponent = 2 / 97; // was 2 / (365 + 1)
        $health = 0;

        foreach ($days as $day) {
            $health = (intval($day) * $exponent) + ($health * (1-$exponent));
        }

        return round($health,3);
    }

    private function makeReports($days, $startTime = 0) {
        $reports = array();

        foreach ($days as $i => $day) {
            $time = $startTime + ($i * 24 * 60 * 60);

            $report = new stdClass();
            $report->isSuccessful = strval($day);
            $report->date = date('Y-m-d H:i:s', $time);

            $reports[] = $report;
        }

        return $reports;
    }

    public function testReturnsZeroWhenNoReports() {
        $this->mockMysqlDatabase->setReturnValue( 'getAllReportsForGoal', [] );
        $result = $this->mockedHealth->getHealth(1, 1);
        $this->assertEquals($result, 0);
    }

    public function testReturnsCorrectValueFor5daysOfSetback() {
        $days = str_pad('',360,'1');
        $days = str_pad($days,365,'0');
        $days = str_split($days);

        $health = $this->getHealth($days);
        $reports = $this->makeReports($days);
        $this->mockMysqlDatabase->setReturnValue( 'getAllReportsForGoal',$reports);

        $result = $this->mockedHealth->getHealth(1,1);

        $this->assertEquals($health, $result);
    }

    public function testAlgorithmGivesFullHealthForYearOfSuccess() {
        $days = str_pad('',365,'1');
        $days = str_split($days);

        $health = $this->getHealth($days);

        $this->assertEquals(1, $health);
    }

    public function testAlgorithmDoesNotGiveFullHealthForOneSuccess() {
        $days = array('1');

        $health = $this->getHealth($days);

        $this->assertTrue($health < 1);
    }

    public function testHealthReturnsCorrectHealthForLowNumberOfReports() {
        $days = array('1');

        $health = $this->getHealth($days);
        $reports = $this->makeReports($days);
		$this->mockMysqlDatabase->setReturnValue( 'getAllReportsForGoal',$reports);

        $result = $this->mockedHealth->getHealth(1,1);

        $this->assertEquals($health, $result);
    }

    public function testGetHealthsWithNoReports() {
    	$time = 1496254842; // May 31, 2017

		$this->mockPhpLibrary->setReturnValue( "getCurrentTime", $time );
		$this->mockMysqlDatabase->setReturnValue( 'getAllReportsForGoal', [] );

		$healths = $this->mockedHealth->getHealths(1,1);

		$this->assertEquals( [[ "5/2017", 0 ]], $healths );
	}

	public function testGetHealthsForTwoMonthsAllFailures() {
		$startTime = 1496254842; // May 31, 2017

		$this->setReturnValuesForGetHealths( 20, $startTime, false );

		$healths = $this->mockedHealth->getHealths(1,1);

		$this->assertEquals( [
			[ "7/2016", 0 ],
			[ "8/2016", 0 ],
			[ "9/2016", 0 ],
			[ "10/2016", 0 ],
			[ "11/2016", 0 ],
			[ "12/2016", 0 ],
			[ "1/2017", 0 ],
			[ "2/2017", 0 ],
			[ "3/2017", 0 ],
			[ "4/2017", 0 ],
			[ "5/2017", 0 ],
			[ "6/2017", 0 ],
		], $healths );
	}

	public function testGetHealthsForTwelveMonthsEndsWithFullHealth() {
		$this->setReturnValuesForGetHealths( 367, 0, true );

		$healths = $this->mockedHealth->getHealths(1,1);

		$lastEntry = end($healths);
		$lastHealth = end($lastEntry);

		$this->assertEquals( 1, $lastHealth );
	}

	public function setReturnValuesForGetHealths( $nDays, $timeOfFirstReport, $areReportSuccessful ): void
	{
		$nSeconds = $nDays * 24 * 60 * 60;
		$timeOfLastReport = $timeOfFirstReport + $nSeconds;
		$statusType = $areReportSuccessful ? '1' : '0';

		$dayStatuses = str_pad( '', $nDays, $statusType );
		$dayStatuses = str_split( $dayStatuses );
		$reports = $this->makeReports( $dayStatuses, $timeOfFirstReport );

		$this->mockPhpLibrary->setReturnValue( "getCurrentTime", $timeOfLastReport );
		$this->mockMysqlDatabase->setReturnValue( 'getAllReportsForGoal', $reports );
	}

	public function testGetHealthsForElevenMonthsStartsWithNoHealthHealth() {
		$this->setReturnValuesForGetHealths( 330, 0, true );

		$healths = $this->mockedHealth->getHealths(1,1);

		$firstEntry = reset($healths);
		$firstHealth = end($firstEntry);

		$this->assertEquals( 0, $firstHealth );
	}
}
