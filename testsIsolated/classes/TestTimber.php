<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestTimber extends HfTestCase {
    public function testHasRenderMethod() {
        $this->assertMethodExists( $this->mockTimber, "render" );
    }
}
