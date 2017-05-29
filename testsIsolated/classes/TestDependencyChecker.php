<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestDependencyChecker extends HfTestCase {
    public function testGetDependencyErrors() {
        $this->expectAtLeastOnce( $this->mockCms, "isPluginActive", ["timber-library/timber.php"] );
        $this->mockedDependencyChecker->checkForDependencyErrors();
    }

    public function testRegistersError() {
        $this->expectAtLeastOnce( $this->mockCms, "addAdminErrorMessage", [
            "hf-accountability requires the following plugins: Timber"
        ] );
        $this->mockedDependencyChecker->checkForDependencyErrors();
    }
}