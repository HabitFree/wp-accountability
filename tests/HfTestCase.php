<?php
//require_once( dirname( dirname( __FILE__ ) ) . '/hf-accountability.php' );

abstract class HfTestCase extends \PHPUnit_Framework_TestCase {
    protected $backupGlobals = false;
    protected $Factory;

    function __construct() {
        $this->Factory = new HfFactory();
    }

    protected function myMakeMock( $className ) {
        return $this->getMockBuilder( $className )->disableOriginalConstructor()->getMock();
    }

    protected function mySetReturnValue( $Mock, $method, $value ) {
        return $Mock->expects( $this->any() )->method( $method )->will( $this->returnValue( $value ) );
    }

    protected function mySetReturnValues( $Mock, $method, $values ) {
        $AdjustedMock = $Mock->expects( $this->any() )->method( $method );
        $consecutiveCalls = call_user_func_array( array($this, "onConsecutiveCalls"), $values );
        return $AdjustedMock->will($consecutiveCalls);
    }

    protected function myExpectAtLeastOnce( $Mock, $method, $args = array() ) {
        $ExpectantMock = $Mock->expects( $this->atLeastOnce() )->method( $method );

        $this->addWithArgsExpectation( $args, $ExpectantMock );
    }

    protected function myExpectAt( $Mock, $method, $at, $args = array() ) {
        $ExpectantMock = $Mock->expects( $this->at($at) )->method( $method );

        $this->addWithArgsExpectation( $args, $ExpectantMock );
    }

    protected function myExpectNever( $Mock, $method, $args = array() ) {
        $ExpectantMock = $Mock->expects( $this->never() )->method( $method );

        $this->addWithArgsExpectation( $args, $ExpectantMock );
    }

    protected function myExpectOnce( $Mock, $method, $args = array() ) {
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