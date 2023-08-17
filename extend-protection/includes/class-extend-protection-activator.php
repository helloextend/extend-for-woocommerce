<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Extend_Protection
 * @subpackage Extend_Protection/includes
 * @author     support@extend.com
 */


class Extend_Protection_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        /* Extend  Logging : On activation create two fields in the wp_options table to store our errors, debugs and notices. */
        add_option( 'custom_error_log' );
        add_option( 'custom_notice_log' );
        add_option( 'custom_debug_log' );
        add_option( 'extend_logger_new_logs' );
        add_option( 'extend_logger_ab_show', true );
	}

}
