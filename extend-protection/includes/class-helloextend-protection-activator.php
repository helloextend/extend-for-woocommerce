<?php

/**
 * Fired during plugin activation
 *
 * @link  http://example.com
 * @since 1.0.0
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/includes
 * @author     support@extend.com
 */

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

class HelloExtend_Protection_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since 1.0.0
     */
    public static function activate()
    {

        /* Extend  Logging : On activation create two fields in the wp_options table to store our errors, debugs and notices. */
        add_option('custom_error_log');
        add_option('custom_notice_log');
        add_option('custom_debug_log');
        add_option('extend_logger_new_logs');
        add_option('extend_logger_ab_show', true);

        // Extend Oauth token fields
        add_option('extend_live_token_date');
        add_option('extend_sandbox_token_date');
        add_option('extend_live_token');
        add_option('extend_sandbox_token');
    }
}
