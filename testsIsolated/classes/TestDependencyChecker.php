<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestDependencyChecker extends HfTestCase {
    public function testGetDependencyErrors() {
        $this->expectAtLeastOnce( $this->mockCms, "isPluginActive", ["timber"] );
        $this->mockedDependencyChecker->getDependencyErrors();
    }

    public function testRegistersError() {
        $this->expectAtLeastOnce( $this->mockCms, "addSettingsError", [
            "hfDependencyError",
            "hfDependencyError",
            "hf-accountability requires the following plugins: timber"
        ] );
        $this->mockedDependencyChecker->getDependencyErrors();
    }
}