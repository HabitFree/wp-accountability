<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestDependencyChecker extends HfTestCase {
    public function testGetDependencyErrors() {
        $this->mockedDependencyChecker->getDependencyErrors();
    }
}