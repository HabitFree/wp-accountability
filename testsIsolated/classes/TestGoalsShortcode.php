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

    public function testGetsGoalCardsData() {
        $this->mockUserManager->setReturnValue( 'isUserLoggedIn', true );
        $this->mockUserManager->setReturnValue( "getCurrentUserId", 7 );

        $this->mockedGoalsShortcode->getOutput();

        $this->assertCalledWith( $this->mockGoals, "getGoalCardsData", 7 );
    }
}