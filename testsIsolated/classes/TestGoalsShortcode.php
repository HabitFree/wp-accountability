<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase2.php');

class TestGoalsShortcode extends HfTestCase2 {
    public function testRendersTemplate() {
        $this->mockUserManager->setReturnValue( 'isUserLoggedIn', true );

        $this->mockedGoalsShortcode->getOutput();

        $this->assertCalled( $this->mockTimber, "render" );
    }

    public function testDoesntUseAccountabilityForm() {
        $this->mockUserManager->setReturnValue( 'isUserLoggedIn', true );

        $this->mockedGoalsShortcode->getOutput();

        $call = $this->mockTimber->getCalls("render")[0];
        $data = $call[1];

        $this->assertFalse( array_key_exists( "content", $data ));
    }
}