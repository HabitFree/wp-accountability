<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

use PHPUnit\Framework\TestCase;

//require_once( dirname( dirname( __FILE__ ) ) . '/wp-hf-accountability.php' );

abstract class HfTestCase extends TestCase {
    protected $backupGlobals = false;
    protected $factory;

    protected $mockDatabase;
    protected $mockMessenger;
    protected $mockAssetLocator;
    protected $mockCms;
    protected $mockSecurity;
    protected $mockCodeLibrary;
    protected $mockUserManager;
    protected $mockGoals;
    protected $mockMarkupGenerator;
    protected $mockPartnerListShortcode;
    protected $mockInvitePartnerShortcode;
    protected $mockLoginForm;
    protected $mockRegistrationForm;
    protected $mockInviteResponseForm;
    protected $mockStreaks;
    protected $mockHealth;
    protected $mockTimber;

    protected $mockedInvitePartnerShortcode;
    protected $mockedUserManager;
    protected $mockedMessenger;
    protected $mockedGoalsShortcode;
    protected $mockedDatabase;
    protected $mockedAssetLocator;
    protected $mockedPartnerListShortcode;
    protected $mockedAuthenticateShortcode;
    protected $mockedGoals;
    protected $mockedManagePartnersShortcode;
    protected $mockedMarkupGenerator;
    protected $mockedLoginForm;
    protected $mockedRegistrationForm;
    protected $mockedInviteResponseForm;
    protected $mockedStreaks;
    protected $mockedAdminPanel;
    protected $mockedHealth;
    protected $mockedDependencyChecker;

    protected function setUp() {
        $_POST = array();
        $_GET  = array();

		$this->factory = new HfFactory();

        $this->resetMocks();
        $this->resetMockedObjects();
    }

    private function resetMocks() {
        $this->mockDatabase               = $this->makeMock( 'HfMysqlDatabase' );
        $this->mockMessenger              = $this->makeMock( 'HfMailer' );
        $this->mockAssetLocator           = $this->makeMock( 'HfUrlFinder' );
        $this->mockCms                    = $this->makeMock( 'HfWordPress' );
        $this->mockSecurity               = $this->makeMock( 'HfSecurity' );
        $this->mockCodeLibrary            = $this->makeMock( 'HfPhpLibrary' );
        $this->mockUserManager            = $this->makeMock( 'HfUserManager' );
        $this->mockGoals                  = $this->makeMock( 'HfGoals' );
        $this->mockMarkupGenerator        = $this->makeMock( 'HfHtmlGenerator' );
        $this->mockPartnerListShortcode   = $this->makeMock( 'HfPartnerListShortcode' );
        $this->mockInvitePartnerShortcode = $this->makeMock( 'HfInvitePartnerShortcode' );
        $this->mockLoginForm              = $this->makeMock( 'HfLoginForm' );
        $this->mockRegistrationForm       = $this->makeMock( 'HfRegistrationForm' );
        $this->mockInviteResponseForm     = $this->makeMock( 'HfInviteResponseForm' );
        $this->mockStreaks                = $this->makeMock( 'HfStreaks' );
        $this->mockHealth                 = $this->makeMock( 'HfHealth' );
        $this->mockTimber                 = $this->makeMock( 'HfTimber' );

        $this->setReturnValue( $this->mockCms, 'getDbPrefix', 'wptests_' );
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
        $this->mockedMarkupGenerator = new HfHtmlGenerator($this->mockCms, $this->mockAssetLocator);
        $this->resetMockedLoginForm();
        $this->mockedRegistrationForm = new HfRegistrationForm('url', $this->mockMarkupGenerator);
        $this->mockedInviteResponseForm = new HfInviteResponseForm('url', $this->mockMarkupGenerator);
        $this->mockedStreaks = new HfStreaks($this->mockDatabase, $this->mockCodeLibrary);
        $this->resetMockedAdminPanel();
        $this->mockedHealth = new HfHealth($this->mockDatabase);
        $this->mockedDependencyChecker = new HfDependencyChecker( $this->mockCms );
    }

    protected function makeMock( $className ) {
        return $this->getMockBuilder( $className )->disableOriginalConstructor()->getMock();
    }

    protected function setReturnValue( $Mock, $method, $value ) {
        return $Mock->expects( $this->any() )->method( $method )->will( $this->returnValue( $value ) );
    }

    private function resetMockedUserManager() {
        $this->mockedUserManager = new HfUserManager(
            $this->mockDatabase,
            $this->mockMessenger,
            $this->mockAssetLocator,
            $this->mockCms,
            $this->mockCodeLibrary
        );
    }

    private function resetMockedMailer() {
        $this->mockedMessenger = new HfMailer(
            $this->mockAssetLocator,
            $this->mockSecurity,
            $this->mockDatabase,
            $this->mockCms,
            $this->mockCodeLibrary
        );
    }

    private function resetMockedGoalsShortcode() {
        $this->mockedGoalsShortcode = new HfGoalsShortcode(
            $this->mockUserManager,
            $this->mockMessenger,
            $this->mockAssetLocator,
            $this->mockGoals,
            $this->mockSecurity,
            $this->mockMarkupGenerator,
            $this->mockCodeLibrary,
            $this->mockDatabase,
            $this->mockTimber
        );
    }

    private function resetMockedDatabase() {
        $this->mockedDatabase = new HfMysqlDatabase(
            $this->mockCms,
            $this->mockCodeLibrary
        );
    }

    private function resetMockedAssetLocator() {
        $this->mockedAssetLocator = new HfUrlFinder( $this->mockCms );
    }

    private function resetMockedInvitePartnerShortcode() {
        $this->mockedInvitePartnerShortcode = new HfInvitePartnerShortcode(
            $this->mockAssetLocator,
            $this->mockMarkupGenerator,
            $this->mockUserManager
        );
    }

    private function resetMockedPartnerListShortcode() {
        $this->mockedPartnerListShortcode = new HfPartnerListShortcode(
            $this->mockUserManager,
            $this->mockMarkupGenerator,
            $this->mockAssetLocator
        );
    }

    private function resetMockedAuthenticateShortcode() {
        $this->mockedAuthenticateShortcode = new HfAuthenticateShortcode(
            $this->mockMarkupGenerator,
            $this->mockAssetLocator,
            $this->mockCms,
            $this->mockUserManager,
            $this->mockLoginForm,
            $this->mockRegistrationForm,
            $this->mockInviteResponseForm
        );
    }

    private function resetMockedGoals() {
        $this->mockedGoals = new HfGoals(
            $this->mockMessenger,
            $this->mockCms,
            $this->mockMarkupGenerator,
            $this->mockDatabase,
            $this->mockCodeLibrary,
            $this->mockStreaks,
            $this->mockHealth
        );
    }

    private function resetMockedManagePartnersShortcode() {
        $this->mockedManagePartnersShortcode = new HfManagePartnersShortcode(
            $this->mockSecurity,
            $this->mockUserManager,
            $this->mockPartnerListShortcode,
            $this->mockInvitePartnerShortcode
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

    private function resetMockedLoginForm()
    {
        $this->mockedLoginForm = new HfLoginForm(
            'url',
            $this->mockMarkupGenerator,
            $this->mockCms,
            $this->mockAssetLocator,
            $this->mockUserManager
        );
    }

    private function resetMockedAdminPanel()
    {
        $this->mockedAdminPanel = new HfAdminPanel(
            'url',
            $this->mockMarkupGenerator,
            $this->mockMessenger,
            $this->mockAssetLocator,
            $this->mockDatabase,
            $this->mockUserManager,
            $this->mockCms
        );
    }
} 