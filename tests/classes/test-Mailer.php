<?php
require_once( dirname( dirname( __FILE__ ) ) . '/HfTestCase.php' );

class TestMailer extends HfTestCase {
    // Helper Functions

    private function makeDependencies() {
        $UrlFinder    = $this->myMakeMock( 'HfUrlFinder' );
        $Security     = $this->myMakeMock( 'HfSecurity' );
        $Database     = $this->myMakeMock( 'HfMysqlDatabase' );
        $WordPressApi = $this->myMakeMock( 'HfWordPressInterface' );

        return array($UrlFinder, $Security, $Database, $WordPressApi);
    }

    // Tests

    public function testSomething() {
        $this->assertEquals( 1, 1 );
    }

    public function testSendEmailByUserID() {
        list( $UrlFinder, $Security, $Database, $WordPressApi ) = $this->makeDependencies();

        $this->mySetReturnValue( $WordPressApi, 'getVar', 5 );
        $this->mySetReturnValue( $WordPressApi, 'getUserEmail', 'me@test.com' );

        $this->myExpectAtLeastOnce( $Database, 'recordEmail', array(1, 'test', 'test', 5, 'me@test.com') );

        $Mailer = new HfMailer( $UrlFinder, $Security, $Database, $WordPressApi );
        $Mailer->sendEmailToUser( 1, 'test', 'test' );
    }

    public function testSendEmailToUserAndSpecifyEmailID() {
        list( $UrlFinder, $Security, $Database, $WordPressApi ) = $this->makeDependencies();

        $Mailer = new HfMailer( $UrlFinder, $Security, $Database, $WordPressApi );

        $this->mySetReturnValue($WordPressApi, 'sendWpEmail', true);
        $this->mySetReturnValue($WordPressApi, 'getUserEmail', 'me@test.com');

        $this->myExpectAtLeastOnce($Database, 'recordEmail', array(1, 'test subject', 'test body', 123, 'me@test.com'));

        $Mailer->sendEmailToUserAndSpecifyEmailID( 1, 'test subject', 'test body', 123 );
    }
}