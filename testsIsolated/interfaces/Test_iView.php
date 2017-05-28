<?php
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class Test_iView extends HfTestCase {
    // Helper Functions
    
    // Tests

    public function testViewInterfaceExists() {
        $this->assertTrue( interface_exists( 'Hf_iView' ) );
    }
}
