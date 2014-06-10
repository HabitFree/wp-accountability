<?php

class TestGeneral extends \PHPUnit_Framework_TestCase {
    protected $backupGlobals = false;
    private $Factory;

    public function __construct() {
        $this->Factory = new HfFactory();
    }

    // Helper Functions

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
        $this->assertEquals( true, shortcode_exists( 'hfSettings' ) );
    }

    public function testPHPandMySQLtimezonesMatch() {
        $phpTime = date( 'Y-m-d H:i:s' );
        global $wpdb;
        $mysqlTime = $wpdb->get_results( "SELECT NOW()", ARRAY_A );
        $this->assertEquals( $phpTime, $mysqlTime[0]['NOW()'] );
    }
}