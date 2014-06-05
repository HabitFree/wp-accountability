<?php
/*
Plugin Name: HabitFree Accountability
Description: Keeps people accountable.
Author: Nathan Arthur
Version: 1.0
Author URI: http://NathanArthur.com/
*/

register_activation_hook(__FILE__,"hfActivate");
register_deactivation_hook(__FILE__,"hfDeactivate");

function hfActivate() {
    $Factory        = new HfFactory();

    $Database       = $Factory->makeDatabase();
    $UserManager    = $Factory->makeUserManager();

    wp_clear_scheduled_hook('hfEmailCronHook');
    wp_schedule_event(time(), 'daily', 'hfEmailCronHook');

    $Database->installDb();
	$UserManager->processAllUsers();

    error_log("my plugin activated", 0);
}

function hfDeactivate() {
	wp_clear_scheduled_hook('hfEmailCronHook');
}

require_once(dirname(__FILE__) . '/interfaces/interface-Hf_iDisplayCodeGenerator.php');
require_once(dirname(__FILE__) . '/interfaces/interface-Hf_iContentManagementSystem.php');
require_once(dirname(__FILE__) . '/interfaces/interface-Hf_iCodeLibrary.php');
require_once(dirname(__FILE__) . '/interfaces/interface-Hf_iView.php');
require_once(dirname(__FILE__) . '/interfaces/interface-Hf_iShortcode.php');

require_once(dirname(__FILE__) . '/abstractClasses/abstractClass-HfForm.php');

require_once(dirname(__FILE__) . '/classes/class-HfUrlFinder.php');
require_once(dirname(__FILE__) . '/classes/class-HfUrlGenerator.php');
require_once(dirname(__FILE__) . '/classes/class-HfSecurity.php');
require_once(dirname(__FILE__) . '/classes/class-HfMailer.php');
require_once(dirname(__FILE__) . '/classes/class-HfDatabase.php');
require_once(dirname(__FILE__) . '/classes/class-HfUserManager.php');
require_once(dirname(__FILE__) . '/classes/class-HfAdminPanel.php');
require_once(dirname(__FILE__) . '/classes/class-HfHtmlGenerator.php');
require_once(dirname(__FILE__) . '/classes/class-HfWordPressInterface.php');
require_once(dirname(__FILE__) . '/classes/class-HfGoals.php');
require_once(dirname(__FILE__) . '/classes/class-HfPhpLibrary.php');
require_once(dirname(__FILE__) . '/classes/class-HfGenericForm.php');
require_once(dirname(__FILE__) . '/classes/class-WebView.php');
require_once(dirname(__FILE__) . '/classes/class-HfSettingsShortcode.php');
require_once(dirname(__FILE__) . '/classes/class-HfFactory.php');
require_once(dirname(__FILE__) . '/classes/class-HfGoalsShortcode.php');
require_once(dirname(__FILE__) . '/classes/class-HfAccountabilityForm.php');
require_once(dirname(__FILE__) . '/classes/class-HfRegisterShortcode.php');

date_default_timezone_set('America/Chicago');

$HfFactory      = new HfFactory();
$HfGoals        = $HfFactory->makeGoals();
$HfUserManager  = $HfFactory->makeUserManager();
$HfAdminPanel   = $HfFactory->makeAdminPanel();

add_action( 'hfEmailCronHook', array( $HfGoals, 'sendReportRequestEmails' ) );
add_action( 'user_register', array( $HfUserManager, 'processNewUser' ) );
add_action( 'admin_menu', array( $HfAdminPanel, 'registerAdminPanel' ) );
add_action( 'admin_head', array( $HfAdminPanel, 'addToAdminHead' ) );
add_action( 'init', 'hfRegisterShortcodes' );

function hfRegisterShortcodes() {
    $Factory            = new HfFactory();
    $UserManager        = $Factory->makeUserManager();
    $SettingsShortcode  = $Factory->makeSettingsShortcode();
    $GoalsShortcode     = $Factory->makeGoalsShortcode();
    $RegisterShortcode  = $Factory->makeRegisterShortcode();

    add_shortcode( 'hfSettings', array($SettingsShortcode, 'getOutput') );
    add_shortcode( 'hfGoals', array($GoalsShortcode, 'getOutput') );
    add_shortcode( 'userButtons', array($UserManager, 'userButtonsShortcode') );
    add_shortcode( 'hfRegister', array($RegisterShortcode, 'getOutput') );
}