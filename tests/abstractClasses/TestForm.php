<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestForm extends HfTestCase {

    public function testFormAbstractClassExists() {
        $this->assertTrue( class_exists( 'HfForm' ) );
    }
}
