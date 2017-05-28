<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase.php');

class TestWordPress extends HfTestCase {
    public function testMethodsExist() {
        $methods = array(
            'deleteRows',
            'isEmailTaken',
            'getPageByTitle',
            'getPermalink',
            'getHomeUrl',
            'prepareQuery',
            'insertOrReplaceRow',
            'getOption',
            'getPluginDirectoryUrl',
            'getNonceField',
            'isNonceValid',
            'updateUserMeta',
            'registerPostType',
            'isPluginActive',
            'addSettingsError'
        );

        foreach($methods as $method) {
            $this->assertMethodExists($this->mockCms, $method);
        }
    }

//  Impure test
//    public function testPrepareMethod() {
//        $expected = "select * from 'wptest_duck' where duckId = 5";
//        $actual = $this->factory->makeCms()->prepareQuery('select * from %s where duckId = %d', array('wptest_duck', 5));
//
//        $this->assertEquals($expected, $actual);
//    }
}
