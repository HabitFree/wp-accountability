<?php

//( PHP_SAPI === 'cli' ) || die( 'Access Denied' );
//
//define( 'PHPUNIT_DB_PREFIX', 'phpunit_' );
//
//global $wp_rewrite, $wpdb;
//
////Required for code coverage to run.
////define( 'WP_MAX_MEMORY_LIMIT', '1024M' );
//define( 'WP_MEMORY_LIMIT', '100M' );
//
//require_once( dirname( __FILE__ ) . '/../../../../wp-load.php' );
//require_once( ABSPATH . 'wp-admin/includes/admin.php' );
//
//wp_set_current_user( 1 );

//global $current_blog, $current_site, $wp_rewrite, $wpdb;
//$_SERVER['HTTP_HOST'] = 'localhost';
//
//$wp_load = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';
//if (!is_readable($wp_load)) {
//    die("The plugin must be in the 'wp-content/plugins' directory of a working WordPress installation.\n");
//}
//require_once $wp_load;
//
//require_once( dirname(dirname( __FILE__ )) . '/hf-accountability.php' );

var_dump(shortcode_exists( 'hfSettings' ));

class TestPHPUnit extends \PHPUnit_Framework_TestCase {
    protected $backupGlobals = false;
    private $Factory;

    public function __construct() {
        $this->Factory = new HfFactory();
    }

    //    Helper Functions

    private function makeUserManagerMockDependencies() {
        Mock::generate( 'HfMysqlDatabase' );
        Mock::generate( 'HfMailer' );
        Mock::generate( 'HfUrlFinder' );
        Mock::generate( 'HfWordPressInterface' );
        Mock::generate( 'HfPhpLibrary' );

        $UrlFinder = new MockHfUrlFinder();
        $Database  = new MockHfMysqlDatabase();
        $Messenger = new MockHfMailer();
        $Cms       = new MockHfWordPressInterface();
        $PhpApi    = new MockHfPhpLibrary();

        return array($UrlFinder, $Database, $Messenger, $Cms, $PhpApi);
    }

    private function makeRegisterShortcodeMockDependencies() {
        Mock::generate( 'HfUrlFinder' );
        Mock::generate( 'HfMysqlDatabase' );
        Mock::generate( 'HfPhpLibrary' );
        Mock::generate( 'HfWordPressInterface' );
        Mock::generate( 'HfUserManager' );

        $UrlFinder   = new MockHfUrlFinder();
        $Database    = new MockHfMysqlDatabase();
        $PhpLibrary  = new MockHfPhpLibrary();
        $Cms         = new MockHfWordPressInterface();
        $UserManager = new MockHfUserManager();

        return array($UrlFinder, $Database, $PhpLibrary, $Cms, $UserManager);
    }

    private function makeDatabaseMockDependencies() {
        Mock::generate( 'HfWordPressInterface' );
        Mock::generate( 'HfPhpLibrary' );

        $Cms         = new MockHfWordPressInterface();
        $CodeLibrary = new MockHfPhpLibrary();

        return array($Cms, $CodeLibrary);
    }

    private function makeLogInShortcodeMockDependencies() {
        Mock::generate( 'HfUrlFinder' );
        Mock::generate( 'HfPhpLibrary' );
        Mock::generate( 'HfWordPressInterface' );
        Mock::generate( 'HfUserManager' );

        $UrlFinder   = new MockHfUrlFinder();
        $PhpLibrary  = new MockHfPhpLibrary();
        $Cms         = new MockHfWordPressInterface();
        $UserManager = new MockHfUserManager();

        return array($UrlFinder, $PhpLibrary, $Cms, $UserManager);
    }

    private function classImplementsInterface( $class, $interface ) {
        $interfacesImplemented = class_implements( $class );

        return in_array( $interface, $interfacesImplemented );
    }

    // Tests

    public function testTestingFramework() {
        $this->assertEquals( 1, 1 );
    }

    public function testGettingCurrentUserLogin() {
        $UserManager = $this->Factory->makeUserManager();
        $user         = wp_get_current_user();

        $this->assertEquals( $UserManager->getCurrentUserLogin(), $user->user_login );
    }

    public function testShortcodeRegistration() {
        var_dump(shortcode_exists( 'hfSettings' ));
        $this->assertEquals( true, shortcode_exists( 'hfSettings' ) );
    }
}