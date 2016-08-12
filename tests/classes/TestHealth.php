<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestHealth extends HfTestCase {
    public function testHasGetHealthMethod() {
        $this->mockedHealth->getHealth(1, 1);
    }
}
