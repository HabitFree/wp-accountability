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
	$HfDbMain = new HfDbManager();
	$HfDbMain->installDb();
	$HfUserMain = new HfUserManager();
	$HfUserMain->processAllUsers();
}

function hfDeactivate() {
	wp_clear_scheduled_hook('hfEmailCronHook');
}

require_once(dirname(__FILE__) . '/php/class-HfAccountability.php');
require_once(dirname(__FILE__) . '/php/class-HfMailer.php');
require_once(dirname(__FILE__) . '/php/class-HfDbManager.php');
require_once(dirname(__FILE__) . '/php/class-HfUserManager.php');
require_once(dirname(__FILE__) . '/php/class-HfAdminPanel.php');

if (class_exists("HfAccountability")) {
	$HfMain = new HfAccountability();
}

//Actions and Filters
if (isset($HfMain)) {
	date_default_timezone_set('America/Chicago');

	//Actions
	add_action( 'hfEmailCronHook', array( new HfMailer(), 'sendReportRequestEmails' ) );
	add_action( 'user_register', array( new HfUserManager(), 'processNewUser' ) );
	add_action( 'admin_menu', array( new HfAdminPanel(), 'registerAdminPanel' ) );
	
	//Filters
}