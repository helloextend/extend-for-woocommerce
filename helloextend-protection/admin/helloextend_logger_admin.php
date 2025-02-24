<?php

/*
@package Custom Error Log
@subpackage Admin

This file sets up a page in the admin area under 'Tools' -> 'Error Log'.

Set up the management page...
*/

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_enqueue_scripts', 'helloextend_logger_log_table_scripts');

/*
Require log-table.php which creates the output of the error log page...
*/

function helloextend_logger_admin()
{

    echo '<h2 class="helloextend_logger-title">' . esc_html('Error Log') . '</h2>';

    /* Import the main log table file... */
    include_once HELLOEXTEND_LOGGER_DIR . 'admin/helloextend_logger_log-table.php';

    /* Clear the new logs array as now all logs should have been seen... */
    update_option('helloextend_logger_new_logs', null);

}

/*
Load scripts for the log table page...
*/

function helloextend_logger_load_log_table_scripts()
{

    add_action('admin_enqueue_scripts', 'helloextend_logger_log_table_scripts');

}

function helloextend_logger_log_table_scripts()
{
	$lastmodtime= filemtime(HELLOEXTEND_LOGGER_URI . 'css/helloextend_logger.css');
    wp_register_style('mainStyle', HELLOEXTEND_LOGGER_URI . 'css/helloextend_logger.css', array(), $lastmodtime);

    /* Enqueue script for the error log table and pass translatable strings to it... */
	// phpcs:ignore
    wp_register_script('logTable', HELLOEXTEND_LOGGER_URI . 'js/helloextend_logger_logTable.js', array( 'jquery' ), '', true);

    $data_array = array(

    'ajaxurl'  => admin_url('admin-ajax.php'),
    'deleting' => __('Deleting', 'helloextend-protection') . '...',

    );

    wp_localize_script('logTable', 'errorAjax', $data_array);

    if (is_admin() ) {

        wp_enqueue_style('mainStyle');
        wp_enqueue_script('logTable');

    }

}
