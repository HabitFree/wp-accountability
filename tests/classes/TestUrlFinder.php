<?php
require_once( dirname(dirname( __FILE__ )) . '/HfTestCase.php' );

class TestUrlFinder extends HfTestCase {
    // Helper Functions
    
    // Tests
    
    public function testGetHomePageUrl() {
        $homeUrl = get_home_url();
        $UrlFinder = $this->Factory->makeUrlFinder();

        $this->assertEquals($homeUrl, $UrlFinder->getHomePageUrl());
    }
}
