<?php

/**
 * Fired during plugin deactivation
 *
 * @link  http://example.com
 * @since 1.0.0
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/includes
 * @author     partner-engineering@extend.com
 */

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

class HelloExtend_Protection_Deactivator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since 1.0.0
     */
    public static function deactivate()
    {

        /* Extend Logging: On deactivation clear errors and notices from the database. */
        delete_option('helloextend_plugin_error_log');
        delete_option('helloextend_plugin_notice_log');
        delete_option('helloextend_plugin_debug_log');
        delete_option('helloextend_logger_new_logs');
        delete_option('helloextend_logger_ab_show');

        // Extend Oauth token fields
        delete_option('helloextend_live_token_date');
        delete_option('helloextend_sandbox_token_date');
        delete_option('helloextend_live_token');
        delete_option('helloextend_sandbox_token');
    }
}
