<?php
/*
Plugin Name: HabitFree Accountability
Description: Keeps people accountable.
Author: Nathan Arthur
Version: 1.0
Author URI: http://NathanArthur.com/
*/
if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook( __FILE__, "hfActivate" );
register_deactivation_hook( __FILE__, "hfDeactivate" );

function hfActivate() {
    $Factory = new HfFactory();

    $Database    = $Factory->makeDatabase();
    $UserManager = $Factory->makeUserManager();

    wp_clear_scheduled_hook( 'hfEmailCronHook' );
    wp_schedule_event( time(), 'daily', 'hfEmailCronHook' );

    $Database->installDb();

    add_action( 'wp_loaded', array($UserManager, 'processAllUsers') );

    error_log( "my plugin activated", 0 );
}

function hfDeactivate() {
    wp_clear_scheduled_hook( 'hfEmailCronHook' );
}


function autoload($folder) {
    foreach ( scandir( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $folder ) as $filename ) {
        $path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $filename;
        if ( is_file( $path ) ) {
            require_once $path;
        }
    }
}

autoload('interfaces');
autoload('abstractClasses');
autoload('classes');

date_default_timezone_set( 'America/Chicago' );

$HfFactory     = new HfFactory();
$HfGoals       = $HfFactory->makeGoals();
$HfUserManager = $HfFactory->makeUserManager();
$HfAdminPanel  = $HfFactory->makeAdminPanel();

add_action( 'hfEmailCronHook', array($HfGoals, 'sendReportRequestEmails') );
add_action( 'user_register', array($HfUserManager, 'processNewUser') );
add_action( 'admin_menu', array($HfAdminPanel, 'registerAdminPanel') );
add_action( 'admin_head', array($HfAdminPanel, 'addToAdminHead') );
add_action( 'init', 'hfRegisterShortcodes' );
add_action('init', 'hfAddPostTypes');

add_filter('user_can_richedit', 'hfDisableWysiwygForQuotes');
add_filter( 'enter_title_here', 'hfChangeEditTitleLabelForQuotations' );

function hfRegisterShortcodes() {
    $Factory                = new HfFactory();
    $SettingsShortcode      = $Factory->makeSettingsShortcode();
    $GoalsShortcode         = $Factory->makeGoalsShortcode();
    $AuthenticateShortcode  = $Factory->makeAuthenticateShortcode();
    $UserButtonsShortcode   = $Factory->makeUserButtonsShortcode();
    $InvitePartnerShortcode = $Factory->makeInvitePartnerShortcode();
    $PartnerListShortcode   = $Factory->makePartnerListShortcode();

    add_shortcode( 'hfSettings', array($SettingsShortcode, 'getOutput') );
    add_shortcode( 'hfGoals', array($GoalsShortcode, 'getOutput') );
    add_shortcode( 'hfUserButtons', array($UserButtonsShortcode, 'getOutput') );
    add_shortcode( 'hfAuthenticate', array($AuthenticateShortcode, 'getOutput') );
    add_shortcode( 'hfInvitePartner', array($InvitePartnerShortcode, 'getOutput') );
    add_shortcode( 'hfPartnerList', array($PartnerListShortcode, 'getOutput') );
}

function hfAddPostTypes() {
    register_post_type( 'hf_quotation',
        array(
            'labels' => array(
                'name' => __( 'Quotations' ),
                'singular_name' => __( 'Quotation' ),
                'menu_name'           => __( 'Quotations', 'text_domain' ),
                'parent_item_colon'   => __( 'Parent Quotation:', 'text_domain' ),
                'all_items'           => __( 'All Quotations', 'text_domain' ),
                'view_item'           => __( 'View Quotation', 'text_domain' ),
                'add_new_item'        => __( 'Add New Quotation', 'text_domain' ),
                'edit_item'           => __( 'Edit Quotation', 'text_domain' ),
                'update_item'         => __( 'Update Quotation', 'text_domain' ),
                'search_items'        => __( 'Search Quotation', 'text_domain' )
            ),
            'public' => true,
            'has_archive' => true,
        )
    );

    register_taxonomy(
        'hfContext',
        'hf_quotation',
        array(
            'label' => __( 'Context' ),
            'rewrite' => array( 'slug' => 'context' ),
            'hierarchical' => true,
            'capabilities' => array(
                'manage_terms'=> 'noone',
                'edit_terms'=> 'noone',
                'delete_terms'=> 'noone',
                'assign_terms' => 'edit_posts'
            )
        )
    );

    $taxonomy = 'hfContext';

    wp_insert_term( 'For Success', $taxonomy, $args = array() );
    wp_insert_term( 'For Setback', $taxonomy, $args = array() );
    wp_insert_term( 'For Mentor', $taxonomy, $args = array() );
}

function hfDisableWysiwygForQuotes($default) {
    global $post;
    if ('hf_quotation' == get_post_type($post))
        return false;
    return $default;
}

function hfChangeEditTitleLabelForQuotations( $title ){
    $screen = get_current_screen();

    if  ( 'hf_quotation' == $screen->post_type ) {
        $title = 'Enter source / reference here';
    }

    return $title;
}

