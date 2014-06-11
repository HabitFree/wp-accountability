<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestSecurity extends HfTestCase {
    // Helper Functions

    // Tests

    public function testRandomStringCreationLength() {
        $Security     = $this->Factory->makeSecurity();
        $randomString = $Security->createRandomString( 400 );

        $this->assertEquals( strlen( $randomString ), 400 );
    }
}