<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestPhpLibrary extends HfTestCase {

    // Helper Functions

    public function helperTestMethodExists($methodName) {
        $this->assertTrue( method_exists( $this->MockCodeLibrary, $methodName ) );
    }

    // Tests

    public function testNothing() {
        $this->assertTrue( 1 == 1 );
    }

    public function testMethodsExist() {
        $methodNames = array(
            'randomKeyFromArray'
        );

        foreach($methodNames as $methodName) {
            $this->helperTestMethodExists($methodName);
        }
    }
} 