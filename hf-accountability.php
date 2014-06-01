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
    $WebsiteApi     = new HfWordPressInterface();
    $PHPAPI         = new HfPhpInterface();
	$DbConnection   = new HfDatabase($WebsiteApi, $PHPAPI);
    $HtmlGenerator  = new HfHtmlGenerator();
    $UrlFinder      = new HfUrlFinder();
    $UrlGenerator   = new HfUrlGenerator();
    $Security       = new HfSecurity();
    $Messenger      = new HfMailer($UrlFinder, $UrlGenerator, $Security, $DbConnection, $WebsiteApi);
	$HfUserMain     = new HfUserManager($DbConnection, $HtmlGenerator, $Messenger, $UrlFinder, $WebsiteApi);

    wp_clear_scheduled_hook('hfEmailCronHook');
    wp_schedule_event(time(), 'daily', 'hfEmailCronHook');

    $DbConnection->installDb();
	$HfUserMain->processAllUsers();

    error_log("my plugin activated", 0);
}

function hfDeactivate() {
	wp_clear_scheduled_hook('hfEmailCronHook');
}

require_once(dirname(__FILE__) . '/php/class-HfUrlFinder.php');
require_once(dirname(__FILE__) . '/php/class-HfUrlGenerator.php');
require_once(dirname(__FILE__) . '/php/class-HfSecurity.php');
require_once(dirname(__FILE__) . '/php/class-HfMain.php');
require_once(dirname(__FILE__) . '/php/class-HfMailer.php');
require_once(dirname(__FILE__) . '/php/class-HfDatabase.php');
require_once(dirname(__FILE__) . '/php/class-HfUserManager.php');
require_once(dirname(__FILE__) . '/php/class-HfAdminPanel.php');
require_once(dirname(__FILE__) . '/php/class-HfHtmlGenerator.php');
require_once(dirname(__FILE__) . '/php/class-HfWordPressInterface.php');
require_once(dirname(__FILE__) . '/php/class-HfGoals.php');
require_once(dirname(__FILE__) . '/php/class-HfPhpInterface.php');

if (class_exists("HfMain")) {
    $HfWordPressInterface   = new HfWordPressInterface();
    $HfPhpApi               = new HfPhpInterface();
    $HfDbConnection         = new HfDatabase($HfWordPressInterface, $HfPhpApi);
    $HfHtmlGenerator        = new HfHtmlGenerator();
    $HfUrlFinder            = new HfUrlFinder();
    $HfSecurity             = new HfSecurity();
    $HfUrlGenerator         = new HfUrlGenerator();
    $HfMailer               = new HfMailer($HfUrlFinder, $HfUrlGenerator, $HfSecurity, $HfDbConnection, $HfWordPressInterface);
    $HfUserManager          = new HfUserManager($HfDbConnection, $HfMailer, $HfUrlFinder, $HfWordPressInterface);
    $HfGoals                = new HfGoals($HfMailer, $HfWordPressInterface, $HfHtmlGenerator, $HfDbConnection);
	$HfMain                 = new HfMain($HfHtmlGenerator, $HfUserManager, $HfMailer, $HfUrlFinder, $HfDbConnection, $HfGoals);
}

//Actions and Filters
if (isset($HfMain)) {
	date_default_timezone_set('America/Chicago');

	//Actions
	add_action( 'hfEmailCronHook', array( $HfGoals, 'sendReportRequestEmails' ) );
	add_action( 'user_register', array( $HfUserManager, 'processNewUser' ) );
	add_action( 'admin_menu', array( new HfAdminPanel($HfMailer, $HfUrlFinder, $HfDbConnection), 'registerAdminPanel' ) );
	add_action( 'admin_head', array( new HfAdminPanel($HfMailer, $HfUrlFinder, $HfDbConnection), 'addToAdminHead' ) );
	
	//Filters
}