<?php

//require_once( dirname( dirname( __FILE__ ) ) . '/hf-accountability.php' );

abstract class HfTestCase extends \PHPUnit_Framework_TestCase {
    protected $backupGlobals = false;
    protected $Factory;

    protected $MockDatabase;
    protected $MockMessenger;
    protected $MockAssetLocator;
    protected $MockCms;
    protected $MockSecurity;
    protected $MockCodeLibrary;

    protected $UserManagerWithMockedDependencies;
    protected $MailerWithMockedDependencies;

    function __construct() {
        $this->Factory = new HfFactory();
    }

    protected function setUp() {
        $this->resetMocks();
        $this->resetUserManagerWithMockedDependencies();
        $this->resetMailerWithMockedDependencies();
    }

    private function resetMocks() {
        $this->MockDatabase     = $this->makeMock( 'HfMysqlDatabase' );
        $this->MockMessenger    = $this->makeMock( 'HfMailer' );
        $this->MockAssetLocator = $this->makeMock( 'HfUrlFinder' );
        $this->MockCms          = $this->makeMock( 'HfWordPress' );
        $this->MockSecurity     = $this->makeMock( 'HfSecurity' );
        $this->MockCodeLibrary  = $this->makeMock( 'HfPhpLibrary' );
    }

    private function resetUserManagerWithMockedDependencies() {
        $this->UserManagerWithMockedDependencies = new HfUserManager(
            $this->MockDatabase,
            $this->MockMessenger,
            $this->MockAssetLocator,
            $this->MockCms,
            $this->MockCodeLibrary
        );
    }

    private function resetMailerWithMockedDependencies() {
        $this->MailerWithMockedDependencies = new HfMailer(
            $this->MockAssetLocator,
            $this->MockSecurity,
            $this->MockDatabase,
            $this->MockCms
        );
    }

    protected function makeMock( $className ) {
        return $this->getMockBuilder( $className )->disableOriginalConstructor()->getMock();
    }

    protected function setReturnValue( $Mock, $method, $value ) {
        return $Mock->expects( $this->any() )->method( $method )->will( $this->returnValue( $value ) );
    }

    protected function setReturnValues( $Mock, $method, $values ) {
        $AdjustedMock     = $Mock->expects( $this->any() )->method( $method );
        $consecutiveCalls = call_user_func_array( array($this, "onConsecutiveCalls"), $values );

        return $AdjustedMock->will( $consecutiveCalls );
    }

    protected function expectAtLeastOnce( $Mock, $method, $args = array() ) {
        $ExpectantMock = $Mock->expects( $this->atLeastOnce() )->method( $method );

        $this->addWithArgsExpectation( $args, $ExpectantMock );
    }

    protected function expectAt( $Mock, $method, $at, $args = array() ) {
        $ExpectantMock = $Mock->expects( $this->at( $at ) )->method( $method );

        $this->addWithArgsExpectation( $args, $ExpectantMock );
    }

    protected function expectNever( $Mock, $method, $args = array() ) {
        $ExpectantMock = $Mock->expects( $this->never() )->method( $method );

        $this->addWithArgsExpectation( $args, $ExpectantMock );
    }

    protected function expectOnce( $Mock, $method, $args = array() ) {
        $ExpectantMock = $Mock->expects( $this->once() )->method( $method );

        $this->addWithArgsExpectation( $args, $ExpectantMock );
    }

    private function addWithArgsExpectation( $args, $ExpectantMock ) {
        if ( !empty( $args ) ) {
            $expectations = array();

            foreach ( $args as $arg ) {
                $expectations[] = $this->equalTo( $arg );
            }

            call_user_func_array( array($ExpectantMock, "with"), $expectations );
        }
    }

    protected function classImplementsInterface( $class, $interface ) {
        $interfacesImplemented = class_implements( $class );

        return in_array( $interface, $interfacesImplemented );
    }

    protected function haystackContainsNeedle( $haystack, $needle ) {
        return strstr( $haystack, $needle ) != false;
    }
} 