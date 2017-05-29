<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestGoalsShortcode extends HfTestCase {
    public function testRendersTemplate() {
        $this->expectAtLeastOnce( $this->mockTimber, "render", ["goals.twig",[]] );
        $this->mockedGoalsShortcode->getOutput();
    }
}