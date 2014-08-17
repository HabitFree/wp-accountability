<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestUrlFinder extends HfTestCase {
    // Helper Functions


    private function makeMockCmsReturnMockPage() {
        $MockPage     = new stdClass();
        $MockPage->ID = 5;
        $this->setReturnValue( $this->MockCms, 'getPageByTitle', $MockPage );
    }

    private function setDefaultCmsReturnValues() {
        $this->makeMockCmsReturnMockPage();
        $this->setReturnValue( $this->MockCms, 'getPermalink', 'www.site.com/page' );
    }
    
    // Tests
    
    public function testGetHomePageUrl() {
        $this->setReturnValue($this->MockCms, 'getHomeUrl', 'thePond');
        $actual = $this->AssetLocatorWithMockedDependencies->getHomePageUrl();

        $this->assertEquals('thePond', $actual);
    }

    public function testGetPageUrlByTitleUsesCms() {
        $this->setDefaultCmsReturnValues();

        $this->expectOnce($this->MockCms, 'getPageByTitle', array('test'));
        $this->expectOnce($this->MockCms, 'getPermalink', array(5));

        $actual = $this->AssetLocatorWithMockedDependencies->getPageUrlByTitle('test');

        $this->assertEquals('www.site.com/page', $actual);
    }

    public function testGetLoginUrl() {
        $this->setDefaultCmsReturnValues();

        $actual = $this->AssetLocatorWithMockedDependencies->getLoginUrl();

        $this->assertEquals('www.site.com/page', $actual);
    }

    public function testUrlFinderExists() {
        $this->assertTrue(class_exists('HfUrlFinder'));
    }
}
