<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestWordPress extends HfTestCase {
    // Helper Functions

    public function helperTestMethodExists($methodName) {
        $this->assertTrue( method_exists( $this->MockCms, $methodName ) );
    }

    // Tests

    public function testMethodsExist() {
        $methodNames = array(
            'deleteRows',
            'isEmailTaken',
            'getPageByTitle',
            'getPermalink',
            'getHomeUrl'
        );

        foreach($methodNames as $methodName) {
            $this->helperTestMethodExists($methodName);
        }
    }
}
