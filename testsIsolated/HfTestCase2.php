<?php

if ( !defined( 'ABSPATH' ) ) exit;
require_once(dirname(__FILE__) . '/MockFactory.php');

abstract class HfTestCase2 extends \PHPUnit_Framework_TestCase {
    /* mock objects */

    /** @var  HfTimber $mockTimber */
    protected $mockTimber;

    /** @var  HfGoals $mockGoals */
    protected $mockGoals;

    /** @var  HfSecurity $mockSecurity */
    protected $mockSecurity;

    /** @var  HfMailer $mockMailer */
    protected $mockMailer;

    /** @var  HfUrlFinder $mockUrlFinder */
    protected $mockUrlFinder;

    /** @var  HfUserManager $mockUserManager */
    protected $mockUserManager;

    /** @var  HfHtmlGenerator $mockHtmlGenerator */
    protected $mockHtmlGenerator;

    /** @var  HfPhpLibrary $mockPhpLibrary */
    protected $mockPhpLibrary;

    /** @var  HfMysqlDatabase $mockMysqlDatabase */
    protected $mockMysqlDatabase;

    /** @var  HfWordPress $mockWordPress */
    protected $mockWordPress;

    /** @var  HfStreaks $mockStreaks */
    protected $mockStreaks;

    /** @var  HfHealth $mockHealth */
    protected $mockHealth;

    /* mocked objects */

    /** @var  HfGoalsShortcode $mockedGoalsShortcode */
    protected $mockedGoalsShortcode;

    /** @var  HfGoals $mockedGoals */
    protected $mockedGoals;

    /* helper fields */
    /** @var  Factory $factory */
    protected $factory;
    /** @var  MockFactory $objectMocker */
    protected $objectMocker;
    protected $sourcePath;

    protected function setUp() {
        $this->objectMocker = new MockFactory();
        $this->resetMocks();
        $this->resetMockedObjects();
        $this->factory = new HfFactory();
        $this->sourcePath = realpath(__DIR__ . "/..");
    }

    private function resetMocks() {
        $fieldNames = $this->getFilteredFieldNames( [ $this, 'isMockField' ] );
        foreach ($fieldNames as $fieldName) {
            $className = substr( $fieldName, 4 );
            $this->$fieldName = $this->objectMocker->buildMock( "Hf$className" );
        }
    }

    private function isMockField( $key ) {
        return strpos( $key, 'mock' ) !== false && strpos( $key, 'mocked' ) === false;
    }

    private function resetMockedObjects() {
        $fieldNames = $this->getFilteredFieldNames( [ $this, "isMockedField" ] );
        foreach ($fieldNames as $fieldName) {
            $this->resetMockedObject( $fieldName );
        }
    }

    private function isMockedField( $key ) {
        return strpos( $key, "mocked" ) !== false;
    }

    private function getFilteredFieldNames( $callback )
    {
        return array_keys( array_filter( get_object_vars( $this ), $callback, ARRAY_FILTER_USE_KEY ) );
    }

    private function resetMockedObject( $fieldName )
    {
        $className = 'Hf' . substr( $fieldName, 6 );
        $params = $this->getConstructorParameters( $className );
        $mocks = $this->getMocksForParams( $params );
        $this->$fieldName = new $className( ...$mocks );
    }

    private function getConstructorParameters( $fullClassName )
    {
        $reflectionClass = new \ReflectionClass( $fullClassName );
        $constructor = $reflectionClass->getConstructor();
        return ( $constructor ) ? $constructor->getParameters() : [];
    }

    private function getMocksForParams( $params )
    {
        return array_map( [ $this, "getMockForParam" ], $params );
    }

    private function getMockForParam( $param ) {
        $class = $param->getClass()->name;
        $reflect = new \ReflectionClass( "\\$class" );
        $shortName = $reflect->getShortName();
        $mockName = 'mock' . substr($shortName, 2);
        return $this->$mockName;
    }

    protected function output( $data ) {
        fwrite(STDERR, print_r("\n" . var_export($data, true) . "\n", TRUE));
    }

    /* Assertions */
    protected function assertCalled( $mock, $method ) {
        $mockName = get_class( $mock );
        $error = "Failed asserting that $mockName->$method() was called.";
        $this->assertNotEmpty( $mock->getCalls( $method ), $error );
    }

    protected function assertNotCalled( $mock, $method ) {
        $mockName = get_class( $mock );
        $error = "Failed asserting that $mockName->$method() was not called.";
        $this->assertEmpty( $mock->getCalls( $method ), $error );
    }

    protected function assertCalledWith( $mock, $method, ...$arguments ) {
        $calls = $mock->getCalls( $method );
        $mockName = get_class( $mock );
        $nullError = "$mockName->$method() does not exist.";
        $this->assertNotNull( $calls, $nullError );
        $errorLines = [
            "Failed asserting that $mockName->$method() was called with specified args.",
            "Needle:",
            var_export( $arguments, TRUE ),
            "Haystack:",
            var_export( $calls, TRUE )
        ];
        $error = implode( "\r\n\r\n", $errorLines );
        $this->assertTrue( in_array( $arguments, $calls, TRUE ), $error );
    }

    /* Helper Functions */

    protected function makeMockUsers()
    {
        $user = new stdClass();
        $user->ID = 7;
        return [$user];
    }
}