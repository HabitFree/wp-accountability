<?php
if ( ! defined( 'ABSPATH' ) ) exit;
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
            'getHomeUrl',
            'prepareQuery',
            'insertOrReplaceRow',
            'getOption'
        );

        foreach($methodNames as $methodName) {
            $this->helperTestMethodExists($methodName);
        }
    }

    public function testPrepareMethod() {
        $expected = "select * from 'wptest_duck' where duckId = 5";
        $actual = $this->Factory->makeCms()->prepareQuery('select * from %s where duckId = %d', array('wptest_duck', 5));

        $this->assertEquals($expected, $actual);
    }
}
