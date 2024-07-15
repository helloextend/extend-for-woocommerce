<?php

/**
 * Fired during plugin deactivation
 *
 * @link  http://example.com
 * @since 1.0.0
 *
 * @package    Extend_Protection
 * @subpackage Extend_Protection/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Extend_Protection
 * @subpackage Extend_Protection/includes
 * @author     partner-engineering@extend.com
 */
class Extend_Protection_Deactivator
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
        delete_option('custom_error_log');
        delete_option('custom_notice_log');
        delete_option('custom_debug_log');
        delete_option('extend_logger_new_logs');
        delete_option('extend_logger_ab_show');

        // Extend Oauth token fields
        delete_option('extend_live_token_date');
        delete_option('extend_sandbox_token_date');
        delete_option('extend_live_token');
        delete_option('extend_sandbox_token');
    }
}
