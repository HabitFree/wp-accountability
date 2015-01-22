<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestSecurity extends HfTestCase {
    public function testRandomStringCreationLength() {
        $Security     = $this->factory->makeSecurity();
        $randomString = $Security->createRandomString( 400 );

        $this->assertEquals( strlen( $randomString ), 400 );
    }

    public function testMakeNonceFieldMethodExists() {
        $this->assertMethodExists($this->mockSecurity, 'makeNonceField');
    }

    public function testCheckNonceMethodExists() {
        $this->assertMethodExists($this->mockSecurity, 'isNonceValid');
    }
}