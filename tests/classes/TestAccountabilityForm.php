<?php
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestAccountabilityForm extends HfTestCase {
    // Helper Functions
    
    // Tests

    public function testHfAccountabilityFormClassExists() {
        $this->assertTrue( class_exists( 'HfAccountabilityForm' ) );
    }

    public function testHfAccountabilityFormClassHasPopulateMethod() {
        $Goals              = $this->myMakeMock( 'HfGoals' );
        $AccountabilityForm = new HfAccountabilityForm( 'test.com', $Goals );
        $this->assertTrue( method_exists( $AccountabilityForm, 'populate' ) );
    }
}
