<?php

/**
 * Set up environment for my plugin's tests suite.
 */

system('mysql -h127.0.0.1 -P3306 -pqwerqwer -uroot -e"DROP DATABASE IF EXISTS wp_test; CREATE DATABASE wp_test;"');

    /**
     * The path to the WordPress tests checkout.
     */
define('WP_TESTS_DIR', dirname( __FILE__ ) . '/wordpress-dev/trunk/tests/phpunit/');

/**
 * The path to the main file of the plugin to test.
 */
define('TEST_PLUGIN_FILE', dirname(dirname( __FILE__ )) . '/hf-accountability.php');

/**
 * The WordPress tests functions.
 *
 * We are loading this so that we can add our tests filter
 * to load the plugin, using tests_add_filter().
 */
require_once WP_TESTS_DIR . 'includes/functions.php';

/**
 * Manually load the plugin main file.
 *
 * The plugin won't be activated within the test WP environment,
 * that's why we need to load it manually.
 *
 * You will also need to perform any installation necessary after
 * loading your plugin, since it won't be installed.
 */
function _manually_load_plugin()
{

    require TEST_PLUGIN_FILE;

    // Make sure plugin is installed here ...

    add_action('init', 'hfDoStuffOnInit');

    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER["SERVER_PORT"] = 8080;

    hfActivate();
}

tests_add_filter('muplugins_loaded', '_manually_load_plugin');

function hfDoStuffOnInit()
{
    wp_create_user('hare', 'boundless', 'taken@taken.com');

    $settingsPage = array(
        'post_title' => 'Settings',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 1
    );

    $logInPage = array(
        'post_title' => 'Log In',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => 1
    );

    wp_insert_post($settingsPage);
    wp_insert_post($logInPage);
}

/**
 * Sets up the WordPress test environment.
 *
 * We've got our action set up, so we can load this now,
 * and viola, the tests begin.
 */
require WP_TESTS_DIR . 'includes/bootstrap.php';