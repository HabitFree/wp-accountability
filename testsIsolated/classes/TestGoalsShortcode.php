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

    public function testIncludesGoalCardsDataWhenRenderingTemplate() {
        $this->mockUserManager->setReturnValue( 'isUserLoggedIn', true );

        $this->mockedGoalsShortcode->getOutput();

        $call = $this->mockTimber->getCalls("render")[0];
        $data = $call[1];

        $this->assertTrue( array_key_exists( "goals", $data ));
    }

    public function testGetsGoalCardsDataWhenFormSubmitted() {
        $_POST['submit'] = true;

        $this->mockUserManager->setReturnValue( 'isUserLoggedIn', true );
        $this->mockUserManager->setReturnValue( "getCurrentUserId", 7 );
        $this->mockUserManager->setReturnValue( "getPartners", [] );

        $this->mockedGoalsShortcode->getOutput();

        $this->assertCalledWith( $this->mockGoals, "getGoalCardsData", 7 );
    }

    public function testSendsMessagesWhenFormSubmitted() {
        $_POST['submit'] = true;

        $this->mockUserManager->setReturnValue( 'isUserLoggedIn', true );
        $this->mockUserManager->setReturnValue( "getPartners", [] );

        $this->mockedGoalsShortcode->getOutput();

        $call = $this->mockTimber->getCalls("render")[0];
        $data = $call[1];

        $this->assertTrue( array_key_exists( "messages", $data ));
    }

    public function testSendsCurrentPageUrl() {
        $this->mockUserManager->setReturnValue( 'isUserLoggedIn', true );
        $this->mockUrlFinder->setReturnValue( "getCurrentPageUrl", "google.com" );

        $this->mockedGoalsShortcode->getOutput();

        $call = $this->mockTimber->getCalls("render")[0];
        $data = $call[1];

        $this->assertEquals( "google.com", $data["postUrl"] );
    }
}