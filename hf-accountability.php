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

register_activation_hook(__FILE__,"my_plugin_activate");
register_deactivation_hook(__FILE__,"my_plugin_deactivate");

function my_plugin_activate() {
	error_log("my plugin activated", 0);
	wp_clear_scheduled_hook('emailCronHook');
	wp_schedule_event(time(), 'daily', 'emailCronHook');
	$HfDbMain = new HfDbManager();
	$HfDbMain->installDb();
	$HfUserMain = new HfUserManager();
	$HfUserMain->processAllUsers();
}

function my_plugin_deactivate() {
	wp_clear_scheduled_hook('emailCronHook');
}

require_once(dirname(__FILE__) . '/php/class-HfAccountability.php');
require_once(dirname(__FILE__) . '/php/class-HfMailer.php');
require_once(dirname(__FILE__) . '/php/class-HfDbManager.php');
require_once(dirname(__FILE__) . '/php/class-HfUserManager.php');

if (class_exists("HfAccountability")) {
	$HfMain = new HfAccountability();
}

//Actions and Filters
if (isset($HfMain)) {

	//Actions
	add_action( 'emailCronHook', array(new HfMailer(), 'sendEmailUpdates') );
	add_action( 'user_register', array(new HfUserManager(), 'processNewUser') );
	
	//Filters
	add_filter( 'cron_schedules', array(new HfMailer(), 'cronAdd5min') );
}