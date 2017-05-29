<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once(dirname(dirname(__FILE__)) . '/HfTestCase2.php');

class TestGoalsShortcode extends HfTestCase2 {
    public function testRendersTemplate() {
        $mockGoalSubs = array(new stdClass());
        $this->mockGoals->setReturnValue( 'getGoalSubscriptions', $mockGoalSubs );
        $this->mockUserManager->setReturnValue( 'isUserLoggedIn', true );

        $this->mockedGoalsShortcode->getOutput();

        $this->assertCalled( $this->mockTimber, "render" );
    }
}