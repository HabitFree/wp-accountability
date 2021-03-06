<?php

if ( ! defined( 'ABSPATH' ) ) exit;
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

    public function testShortcodeRegistration() {
        $this->assertEquals( true, shortcode_exists( 'hfSettings' ) );
    }

    public function PASStestPHPandMySQLtimezonesMatch() {
        $phpTime = date( 'Y-m-d H:i:s' );
        global $wpdb;
        $mysqlTime = $wpdb->get_results( "SELECT NOW()", ARRAY_A );
        $this->assertEquals( $phpTime, $mysqlTime[0]['NOW()'] );
    }

    public function testQuotationPostTypeExists() {
        $this->assertTrue(post_type_exists( 'hf_quotation' ));
    }
}