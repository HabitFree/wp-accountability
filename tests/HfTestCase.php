<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

//require_once( dirname( dirname( __FILE__ ) ) . '/wp-hf-accountability.php' );

abstract class HfTestCase extends \PHPUnit_Framework_TestCase {
    private $data = array();

    protected $backupGlobals = false;
    protected $Factory;

    protected $MockMysqlDatabase;
    protected $MockMailer;
    protected $MockUrlFinder;
    protected $MockWordPress;
    protected $MockSecurity;
    protected $MockPhpLibrary;
    protected $MockUserManager;
    protected $MockPageLocator;
    protected $MockGoals;
    protected $MockHtmlGenerator;
    protected $MockPartnerListShortcode;
    protected $MockInvitePartnerShortcode;
    protected $MockLoginForm;
    protected $MockRegistrationForm;
    protected $MockInviteResponseForm;

    protected $MockedInvitePartnerShortcode;
    protected $MockedUserManager;
    protected $MockedMailer;
    protected $MockedGoalsShortcode;
    protected $MockedDatabase;
    protected $MockedAssetLocator;
    protected $MockedPartnerListShortcode;
    protected $MockedAuthenticateShortcode;
    protected $MockedGoals;
    protected $MockedManagePartnersShortcode;
    protected $MockedMarkupGenerator;
    protected $MockedLoginForm;
    protected $MockedRegistrationForm;
    protected $MockedInviteResponseForm;

    function __construct() {
        $this->Factory = new HfFactory();
    }

    protected function setUp() {
        $_POST = array();
        $_GET  = array();

        $this->resetMocks();
        $this->resetMockedObjects();
    }

    private function resetMocks() {
        $this->MockMysqlDatabase               = $this->makeMock( 'HfMysqlDatabase' );
        $this->MockMailer              = $this->makeMock( 'HfMailer' );
        $this->MockUrlFinder           = $this->makeMock( 'HfUrlFinder' );
        $this->MockWordPress                    = $this->makeMock( 'HfWordPress' );
        $this->MockSecurity               = $this->makeMock( 'HfSecurity' );
        $this->MockPhpLibrary            = $this->makeMock( 'HfPhpLibrary' );
        $this->MockUserManager            = $this->makeMock( 'HfUserManager' );
        $this->MockPageLocator            = $this->makeMock( 'HfUrlFinder' );
        $this->MockGoals                  = $this->makeMock( 'HfGoals' );
        $this->MockHtmlGenerator        = $this->makeMock( 'HfHtmlGenerator' );
        $this->MockPartnerListShortcode   = $this->makeMock( 'HfPartnerListShortcode' );
        $this->MockInvitePartnerShortcode = $this->makeMock( 'HfInvitePartnerShortcode' );
        $this->MockLoginForm              = $this->makeMock( 'HfLoginForm' );
        $this->MockRegistrationForm       = $this->makeMock( 'HfRegistrationForm' );
        $this->MockInviteResponseForm     = $this->makeMock( 'HfInviteResponseForm' );

        $this->setReturnValue( $this->MockWordPress, 'getDbPrefix', 'wptests_' );
    }

    private function resetMockedObjects() {
        $this->resetMockedUserManager();
        $this->resetMockedMailer();
        $this->resetMockedGoalsShortcode();
        $this->resetMockedDatabase();
        $this->resetMockedAssetLocator();
        $this->resetMockedInvitePartnerShortcode();
        $this->resetMockedPartnerListShortcode();
        $this->resetMockedAuthenticateShortcode();
        $this->resetMockedGoals();
        $this->resetMockedManagePartnersShortcode();
        $this->MockedMarkupGenerator = new HfHtmlGenerator($this->MockWordPress);
        $this->MockedLoginForm = new HfLoginForm('url');
        $this->MockedRegistrationForm = new HfRegistrationForm('url');
        $this->MockedInviteResponseForm = new HfInviteResponseForm('url');
    }

    protected function makeMock( $className ) {
        return $this->getMockBuilder( $className )->disableOriginalConstructor()->getMock();
    }

    protected function setReturnValue( $Mock, $method, $value ) {
        return $Mock->expects( $this->any() )->method( $method )->will( $this->returnValue( $value ) );
    }

    private function resetMockedUserManager() {
        $this->MockedUserManager = new HfUserManager(
            $this->MockMysqlDatabase,
            $this->MockMailer,
            $this->MockUrlFinder,
            $this->MockWordPress,
            $this->MockPhpLibrary
        );
    }

    private function resetMockedMailer() {
        $this->MockedMailer = new HfMailer(
            $this->MockUrlFinder,
            $this->MockSecurity,
            $this->MockMysqlDatabase,
            $this->MockWordPress,
            $this->MockPhpLibrary
        );
    }

    private function resetMockedGoalsShortcode() {
        $this->MockedGoalsShortcode = new HfGoalsShortcode(
            $this->MockUserManager,
            $this->MockMailer,
            $this->MockUrlFinder,
            $this->MockGoals,
            $this->MockSecurity,
            $this->MockHtmlGenerator,
            $this->MockPhpLibrary,
            $this->MockMysqlDatabase
        );
    }

    private function resetMockedDatabase() {
        $this->MockedDatabase = new HfMysqlDatabase(
            $this->MockWordPress,
            $this->MockPhpLibrary
        );
    }

    private function resetMockedAssetLocator() {
        $this->MockedAssetLocator = new HfUrlFinder( $this->MockWordPress );
    }

    private function resetMockedInvitePartnerShortcode() {
        $this->MockedInvitePartnerShortcode = new HfInvitePartnerShortcode(
            $this->MockUrlFinder,
            $this->MockHtmlGenerator,
            $this->MockUserManager
        );
    }

    private function resetMockedPartnerListShortcode() {
        $this->MockedPartnerListShortcode = new HfPartnerListShortcode(
            $this->MockUserManager,
            $this->MockHtmlGenerator,
            $this->MockUrlFinder
        );
    }

    private function resetMockedAuthenticateShortcode() {
        $this->MockedAuthenticateShortcode = new HfAuthenticateShortcode(
            $this->MockHtmlGenerator,
            $this->MockUrlFinder,
            $this->MockWordPress,
            $this->MockUserManager,
            $this->MockLoginForm,
            $this->MockRegistrationForm,
            $this->MockInviteResponseForm
        );
    }

    private function resetMockedGoals() {
        $this->MockedGoals = new HfGoals(
            $this->MockMailer,
            $this->MockWordPress,
            $this->MockHtmlGenerator,
            $this->MockMysqlDatabase
        );
    }

    private function resetMockedManagePartnersShortcode() {
        $this->MockedManagePartnersShortcode = new HfManagePartnersShortcode(
            $this->MockSecurity,
            $this->MockUserManager,
            $this->MockPartnerListShortcode,
            $this->MockInvitePartnerShortcode
        );
    }

    protected function setReturnValues( $Mock, $method, $values ) {
        $AdjustedMock     = $Mock->expects( $this->any() )->method( $method );
        $consecutiveCalls = call_user_func_array( array( $this, "onConsecutiveCalls" ), $values );

        return $AdjustedMock->will( $consecutiveCalls );
    }

    protected function expectAtLeastOnce( $Mock, $method, $args = array() ) {
        $ExpectantMock = $Mock->expects( $this->atLeastOnce() )->method( $method );

        $this->addWithArgsExpectation( $args, $ExpectantMock );
    }

    private function addWithArgsExpectation( $args, $ExpectantMock ) {
        if ( !empty( $args ) ) {
            $expectations = array();

            foreach ( $args as $arg ) {
                $expectations[] = $this->equalTo( $arg );
            }

            call_user_func_array( array( $ExpectantMock, "with" ), $expectations );
        }
    }

    protected function expectAt( $Mock, $method, $at, $args = array() ) {
        // Any failure of at() expectations returns "Mocked method does not exist"
        // See http://stackoverflow.com/questions/3367513/phpunit-mocked-method-does-not-exist-when-using-mock-expectsthis-at

        // $at refers to the call number among all calls to any method of the specified mock,
        // NOT to the specified method of the specified mock

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

    protected function classImplementsInterface( $class, $interface ) {
        $interfacesImplemented = class_implements( $class );

        return in_array( $interface, $interfacesImplemented );
    }

    protected function assertDoesntContain( $needle, $haystack ) {
        $this->assertFalse( $this->haystackContainsNeedle( $haystack, $needle ) );
    }

    protected function haystackContainsNeedle( $haystack, $needle ) {
        return strstr( $haystack, $needle ) != false;
    }

    protected function assertMethodCallsMethodWithArgsAtAnyTime(
        $InquisitiveMock,
        $inquisitiveMethod,
        $InitiatingObject,
        $initiatingMethod,
        $expectedArgSets
    ) {
        $successes = array_pad( array(), count( $expectedArgSets ), false );

        $argsChecker = function () use ( &$successes, $expectedArgSets ) {
            $actualArgs = func_get_args();

            foreach ( $expectedArgSets as $index => $argSet ) {
                if ( $argSet === $actualArgs ) {
                    $successes[$index] = true;
                    break;
                }
            }
        };

        $InquisitiveMock->expects( $this->any() )
            ->method( $inquisitiveMethod )
            ->will( $this->returnCallback( $argsChecker ) );

        $InitiatingObject->$initiatingMethod();

        foreach ( $successes as $index => $success ) {
            $this->assertTrue( $success, serialize( $expectedArgSets[$index] ) );
        }
    }

    protected function makeMockUsers()
    {
        $user = new stdClass();
        $user->ID = 7;
        return [$user];
    }

    protected function assertMethodExists($object, $method)
    {
        $this->assertTrue(method_exists($object, $method));
    }
} 