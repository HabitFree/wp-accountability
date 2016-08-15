<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestAccountabilityForm extends HfTestCase {
    // Helper Functions
    
    // Tests

    public function testHfAccountabilityFormClassExists() {
        $this->assertTrue( class_exists( 'HfAccountabilityForm' ) );
    }

    public function testHfAccountabilityFormClassHasPopulateMethod() {
        $Goals              = $this->makeMock( 'HfGoals' );
        $AccountabilityForm = new HfAccountabilityForm( 'test.com', $Goals );
        $this->assertTrue( method_exists( $AccountabilityForm, 'populate' ) );
    }

    public function testHfAccountabilityFormIncludesReportCardsClass() {
        $Goals              = $this->makeMock( 'HfGoals' );
        $AccountabilityForm = new HfAccountabilityForm( 'test.com', $Goals );
        $result = $AccountabilityForm->getOutput();
        $needle = 'report-cards';
        $this->assertContains($needle,$result);
    }

    public function testIncludesGoogleChartsScript() {
        $Goals              = $this->makeMock( 'HfGoals' );
        $AccountabilityForm = new HfAccountabilityForm( 'test.com', $Goals );
        $AccountabilityForm->populate(array());
        $result = $AccountabilityForm->getOutput();
        $needle = 'https://www.gstatic.com/charts/loader.js';
        $this->assertContains($needle,$result);
    }
}
