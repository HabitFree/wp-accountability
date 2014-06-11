<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestPhpLibrary extends HfTestCase {
    public function testNothing() {
        $this->assertTrue( 1 == 1 );
    }
} 