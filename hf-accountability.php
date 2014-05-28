<?php
/*
Plugin Name: HabitFree Accountability
Description: Keeps people accountable.
Author: Nathan Arthur
Version: 1.0
Author URI: http://NathanArthur.com/
*/

/*global $wp_version;

if ( !version_compare($wp_version,"3.0",">=") ) {
	die("You need at least version 3.0 of Wordpress to use the copyright plugin");
}*/

#error_log("hello", 0, "/home/habitfree1/wp.habitfree.org/logs/debug.log");

register_activation_hook(__FILE__,"hfActivate");
register_deactivation_hook(__FILE__,"hfDeactivate");

function hfActivate() {
	error_log("my plugin activated", 0);
	wp_clear_scheduled_hook('hfEmailCronHook');
	wp_schedule_event(time(), 'daily', 'hfEmailCronHook');
	$HfDbMain = new HfDbConnection();
	$HfDbMain->installDb();
	$HfUserMain = new HfUserManager($HfDbMain);
	$HfUserMain->processAllUsers();
}

function hfDeactivate() {
	wp_clear_scheduled_hook('hfEmailCronHook');
}

require_once(dirname(__FILE__) . '/php/class-HfUrl.php');
require_once(dirname(__FILE__) . '/php/class-HfSecurity.php');
require_once(dirname(__FILE__) . '/php/class-HfEmail.php');
require_once(dirname(__FILE__) . '/php/class-HfAccountability.php');
require_once(dirname(__FILE__) . '/php/class-HfMailer.php');
require_once(dirname(__FILE__) . '/php/class-HfDbConnection.php');
require_once(dirname(__FILE__) . '/php/class-HfUserManager.php');
require_once(dirname(__FILE__) . '/php/class-HfAdminPanel.php');
require_once(dirname(__FILE__) . '/php/class-HfHtmlGenerator.php');

if (class_exists("HfAccountability")) {
    $HfDbConnection = new HfDbConnection();
    $HfUserManager = new HfUserManager($HfDbConnection);
    $HfURLFinder = new HfUrl();
    $HfSecurity = new HfSecurity();
    $HfMailer = new HfMailer($HfURLFinder, $HfUserManager, $HfSecurity, $HfDbConnection);
    $HfHtmlGenerator = new HfHtmlGenerator();
	$HfMain = new HfAccountability($HfHtmlGenerator, $HfUserManager, $HfMailer, $HfURLFinder, $HfDbConnection);
}

//Actions and Filters
if (isset($HfMain)) {
	date_default_timezone_set('America/Chicago');

	//Actions
	add_action( 'hfEmailCronHook', array( $HfMailer, 'sendReportRequestEmails' ) );
	add_action( 'user_register', array( $HfUserManager, 'processNewUser' ) );
	add_action( 'admin_menu', array( new HfAdminPanel($HfMailer, $HfURLFinder, $HfDbConnection), 'registerAdminPanel' ) );
	add_action( 'admin_head', array( new HfAdminPanel($HfMailer, $HfURLFinder, $HfDbConnection), 'addToAdminHead' ) );
	
	//Filters
}