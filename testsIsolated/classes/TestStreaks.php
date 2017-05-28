<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestStreaks extends HfTestCase {
    public function testHfStreaksClassExists() {
        $this->assertTrue( class_exists( 'HfStreaks' ) );
    }

    public function testImplementsHf_iStreaks() {
        $this->assertTrue($this->classImplementsInterface('HfStreaks','Hf_iStreaks'));
    }

    public function testGetsAllReportsFromDatabase() {
        $this->expectOnce($this->mockDatabase,'getAllReportsForGoal',array(1,7));
        $this->mockedStreaks->streaks(1,7);
    }

    public function testReturnsZeroWhenNoReports() {
        $this->setReturnValue($this->mockDatabase,'getAllReportsForGoal',array());
        $streaks = $this->mockedStreaks->streaks(1,7);
        $this->assertEquals(array(0),$streaks);
    }

    public function testReturnsOneWhenOneSuccessfulReport() {
        $report = new stdClass();
        $report->isSuccessful = 1;
        $report->date = 'date';

        $this->setReturnValue($this->mockDatabase,'getAllReportsForGoal',array($report));
        $streaks = $this->mockedStreaks->streaks(1,7);
        $this->assertEquals(array(1),$streaks);
    }

    public function testAddsUpSuccesses() {
        $report = new stdClass();
        $report->isSuccessful = 1;
        $report->date = 'date';

        $this->setReturnValue($this->mockDatabase, 'getAllReportsForGoal',array($report,$report));

        $daySeconds = 24 * 60 * 60;
        $this->setReturnValues($this->mockCodeLibrary,'convertStringToTime',array(0,$daySeconds));

        $streaks = $this->mockedStreaks->streaks(1,7);
        $this->assertEquals(array(2),$streaks);
    }
}
