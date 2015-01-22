<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestUrlFinder extends HfTestCase {
    // Helper Functions


    private function makeMockWordPressReturnMockPage() {
        $MockPage     = new stdClass();
        $MockPage->ID = 5;
        $this->setReturnValue( $this->mockCms, 'getPageByTitle', $MockPage );
    }

    private function setDefaultCmsReturnValues() {
        $this->makeMockWordPressReturnMockPage();
        $this->setReturnValue( $this->mockCms, 'getPermalink', 'www.site.com/page' );
    }
    
    // Tests
    
    public function testGetHomePageUrl() {
        $this->setReturnValue($this->mockCms, 'getHomeUrl', 'thePond');
        $actual = $this->MockedAssetLocator->getHomePageUrl();

        $this->assertEquals('thePond', $actual);
    }

    public function testGetPageUrlByTitleUsesCms() {
        $this->setDefaultCmsReturnValues();

        $this->expectOnce($this->mockCms, 'getPageByTitle', array('test'));
        $this->expectOnce($this->mockCms, 'getPermalink', array(5));

        $actual = $this->MockedAssetLocator->getPageUrlByTitle('test');

        $this->assertEquals('www.site.com/page', $actual);
    }

    public function testGetLoginUrl() {
        $this->setDefaultCmsReturnValues();

        $actual = $this->MockedAssetLocator->getLoginUrl();

        $this->assertEquals('www.site.com/page', $actual);
    }

    public function testUrlFinderExists() {
        $this->assertTrue(class_exists('HfUrlFinder'));
    }
}
