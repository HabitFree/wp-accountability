<?php
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestWordPress extends HfTestCase {
    // Helper Functions
    
    // Tests

    public function testIsEmailTakenMethodExists() {
        $Factory = new HfFactory();
        $Cms     = $Factory->makeCms();

        $this->assertTrue( method_exists( $Cms, 'isEmailTaken' ) );
    }

    public function testCmsHasDeleteRowsFunction() {
        $Cms = new HfWordPress();

        $this->assertTrue( method_exists( $Cms, 'deleteRows' ) );
    }
}
