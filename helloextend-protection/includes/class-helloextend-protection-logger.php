<?php
/*
@package Custom Error Log
@subpackage Includes

This file does the main work of the plugin

log_error() function, this allows developers to log custom errors in their theme/plugin...
*/

// Prevent direct access to the file
if (! defined('ABSPATH') ) {
    exit;
}

class HelloExtend_Protection_Logger
{

    public static function helloextend_log_error( $message )
    {

        /* Get error logs from the wp_options table... */
        $error_log = get_option('helloextend_error_log');

        if (! $error_log ) {
            $error_log = array(

                'errors'     => array(),
                'next_error' => 1,

            );

        }

        $error_id = $error_log['next_error'];

        /* Insert new error into array... */
        $error_log['errors'][ $error_id ] = array(

            'type'    => 'error',
            'date'    => current_time('timestamp'),
            'id'      => $error_id,
            'message' => sanitize_text_field($message),

        );

        /* Increase the error code to use for the next error logged... */
        $error_log['next_error']++;

        /* Update the error log in the wp_options table... */
        $update = update_option('helloextend_error_log', $error_log);

        /* Add to list of new logs... */
        if ($update ) {
            self::helloextend_logger_add_to_new_logs($error_id, 'errors');
        }

    }

    /*
    log_notice() function, this allows developers to log custom notices in their theme/plugin...
    */

    public static function helloextend_log_notice( $message )
    {

        /* Get notice logs from the wp_options table... */
        $notice_log = get_option('helloextend_notice_log', true);
        if (! $notice_log ) {

            $notice_log = array(

                'notices'     => array(),
                'next_notice' => 1,

            );

        }

        $notice_id = $notice_log['next_notice'];

        /* Insert new notice into array... */
        $notice_log['notices'][ $notice_id ] = array(

            'type'    => 'notice',
            'date'    => current_time('timestamp'),
            'id'      => $notice_id,
            'message' => sanitize_text_field($message),

        );

        /* Increase the notice code to use for the next error logged... */
        $notice_log['next_notice']++;

        /* Update the notice log in the wp_options table... */
        $update = update_option('helloextend_notice_log', $notice_log);

        /* Add to list of new logs... */
        if ($update ) {

            self::helloextend_logger_add_to_new_logs($notice_id, 'notices');

        }

    }

    /*
    log_notice() function, this allows developers to log custom notices in their theme/plugin...
    */

    public static function helloextend_log_debug( $message )
    {

        /* Get debug logs from the wp_options table... */
        $debug_log = get_option('helloextend_debug_log', true);

        // Ensure $debug_log is an array
        if (!is_array($debug_log)) {
            $debug_log = array();
        }

        // Ensure $debug_log['debugs'] is an array
        if (!isset($debug_log['debugs']) || !is_array($debug_log['debugs'])) {
            $debug_log['debugs'] = array();
        }

        // Ensure $debug_log['next_debug'] exists and is numeric
        if (!isset($debug_log['next_debug']) || !is_numeric($debug_log['next_debug'])) {
            $debug_log['next_debug'] = 1;
        }

        $debug_id = $debug_log['next_debug'];

        /* Insert new debug into array... */
        $debug_log['debugs'][ $debug_id ] = array(
            'type'    => 'debug',
            'date'    => current_time('timestamp'),
            'id'      => $debug_id,
            'message' => sanitize_text_field($message),
        );

        /* Increase the debug ID for the next log */
        $debug_log['next_debug']++;

        /* Update the debug log in the wp_options table... */
        $update = update_option('helloextend_debug_log', $debug_log);

        /* Add to list of new logs... */
        if ($update) {
            self::helloextend_logger_add_to_new_logs($debug_id, 'debugs');
        }
    }


    /*
    helloextend_logger_delete_single() gets used by the error log table to delete a single error or notice from the array...
    */

    public static function helloextend_logger_delete_single()
    {

        /* Check that the nonce is correct to avoid safety issues... */
        if (!isset( $_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ), 'helloextend_logger_nonce') ) {

            exit('Wrong nonce - delete single');

        }

        /* Get information about the error to delete from the ajax POST... */

        // Sanitize WordPress ajax post

        $error_code =  isset($_POST['error_code']) ? sanitize_key( wp_unslash( $_POST['error_code'] ) ) : null;
        $log_type   = isset($_POST['log_type']) ? sanitize_text_field( wp_unslash( $_POST['log_type'] ) ): null;

        /* Get the correct log from the wp_options table... */
        $logs = get_option('helloextend_' . $log_type . '_log', true);

        /* Unset the correct error/notice from the array... */
        foreach ( $logs[ $log_type . 's' ] as $key => $log ) {

            if ($log['id'] == $error_code ) {

                unset($logs[ $log_type . 's' ][ $key ]);

            }
        }

        /* Update the log in the wp_options table... */
        $update = update_option('helloextend_' . $log_type . '_log', $logs);

        /* Build the response... */
        if ($update ) {

            $return  = '<div class="updated  ajax-response">';
	        /* translators: 1: Log Type, 2: Error Code. */
            $return .= sprintf(__('%1$s %2$d has been successfully deleted', 'helloextend-protection'), $log_type, $error_code);
            $return .= '.</div>';

        } else {

            $return  = '<div class="error  ajax-response">';
	        /* translators: 1: Log Type, 2: Error Code. */
            $return .= sprintf(__('%1$s %2$d could not be deleted', 'helloextend-protection'), $log_type, $error_code);
            $return .= '.</div>';

        }

        /* Send the response back to the ajax call... */
	    $allowedtags = array(
 		    'div'=>array('class' =>true),
	    );

        die(wp_kses($return, $allowedtags));

    }

    /*
    helloextend_logger_delete_all() gets used by the error log table to clear all errors and notices...
    */

    public static function helloextend_logger_delete_all()
    {
        /* Check that the nonce is correct to avoid safety issues... */
        if (!isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ) , 'helloextend_logger_nonce') ) {
            exit('Wrong nonce - delete all');
        }

        /*
        Empty fields stored in the wp_options table... */

        $error_log_empty  = array();
        $notice_log_empty = array();
        $debug_log_empty  = array();

        $deleted_errors  = update_option('helloextend_error_log', $error_log_empty);
        $deleted_notices = update_option('helloextend_notice_log', $notice_log_empty);
        $deleted_debugs  = update_option('helloextend_debug_log', $debug_log_empty);

        /* Build the response */
        if ($deleted_errors || $deleted_notices || $deleted_debugs ) {

            $return  = '<div class="updated  ajax-response">';
            $return .= __('All errors have been deleted', 'helloextend-protection');
            $return .= '.</div>';

        } else {

            $return  = '<div class="error  ajax-response">';
            $return .= __('Errors could not be deleted', 'helloextend-protection');
            $return .= '.</div>';

        }

        /* Send response back to ajax call... */
	    $allowedtags = array(
		    'div'=>array('class' =>true),
	    );

	    die(wp_kses($return, $allowedtags));
    }

    /*
    helloextend_logger_sort_by_date() gets used by the error log table to sort all errors and notices by date...
    */

    public static function helloextend_logger_sort_by_date( $a, $b ): int
    {

        if ($a['date'] == $b['date'] ) {
            return 0;
        }

        return ( $a['date'] < $b['date'] ) ? 1 : -1;

    }

    /*
    helloextend_logger_get_all_logs() retreives all logs and returns them...
    */

    public static function helloextend_logger_get_all_logs()
    {

        $errors  = get_option('helloextend_error_log', true);
        $notices = get_option('helloextend_notice_log', true);
        $debugs  = get_option('helloextend_debug_log', true);

        /* These variables are used to see if errors, notices and debugs exist... */
        $have_errors  = false;
        $have_notices = false;
        $have_debugs  = false;
        $have_both    = false;
        $have_many    = false;

        /* Build the log array... */
        $logs = array();

        /* If there are any errors logged add them to the array... */

        if ($errors && is_array($errors) ) {
            if ($errors['errors'] ) {
                $errors      = $errors['errors'];
                $logs        = array_merge_recursive($logs, $errors);
                $have_errors = true;
            }
        }

        /* If there are any notices logged add them to the array... */
        if ($notices && is_array($notices) ) {
            if ($notices['notices'] ) {
                $notices      = $notices['notices'];
                $logs         = array_merge_recursive($logs, $notices);
                $have_notices = true;
            }
        }

        /* If there are any debugs logged add them to the array... */
        if ($debugs && is_array($debugs) ) {
            if ($debugs['debugs'] ) {
                $debugs      = $debugs['debugs'];
                $logs        = array_merge_recursive($logs, $debugs);
                $have_debugs = true;
            }
        }

        /* If  errors and notices and debugs exist switch $have_both to true... */
        if ($have_errors && $have_notices && $have_debugs ) {
            $have_both = true;
        }

        if ($have_errors && $have_notices || $have_errors && $have_debugs || $have_notices && $have_debugs ) {
            $have_many = true;
        }
        /* Return an array containing the logs and information of what types exist... */
        $return = array(

            'logs'         => $logs,
            'have_errors'  => $have_errors,
            'have_notices' => $have_notices,
            'have_debugs'  => $have_notices,
            'have_both'    => $have_both,
            'have_many'    => $have_many,

        );

        return $return;

    }

    /*
    helloextend_logger_get_these_logs() gets and returns only one type of log specified by $type...
    $type can be either 'error' or 'notice'...
    */

    public static function helloextend_logger_get_these_logs( $type )
    {

        $logs = get_option('helloextend_' . $type . '_log', true);

        /* Get one step further down the array... */
        if ($logs ) {

            $logs = $logs[ $type . 's' ];

        }

        /* These variables are used so that the output is the same as helloextend_logger_get_all_logs()... */
        $have_errors  = false;
        $have_notices = false;
        $have_debugs  = false;

        if ($type == 'error' ) {

            $have_errors = true;

        } elseif ($type == 'notice' ) {

            $have_notices = true;

        } elseif ($type == 'debug' ) {

            $have_notices = true;

        }
        /* If the $type parameter is not either 'error' or 'notice' or 'debug' return false... */
        else {

            return false;

        }

        /* Return an array containing the logs and information of what types exist... */
        $return = array(

            'logs'         => $logs,
            'have_errors'  => $have_errors,
            'have_notices' => $have_notices,
            'have_debugs'  => $have_debugs,
            'have_both'    => false,
            'have_many'    => false,

        );

        return $return;

    }

    /*
    helloextend_logger_filter_log() filters the error log table so that only errors or notices are displayed...
    */

    public static function helloextend_logger_filter_log()
    {

        /*
        Check that the nonce is correct to avoid safety issues...
        The nonce is passed via a POST from the ajax call...
        */
        if (!isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ), 'helloextend_logger_nonce') ) {
            exit('Wrong nonce filter log');
        }

        /*
        The filter is posted by the ajax call to tell this function which
        type of logs it wants...
        */
        $filter = isset($_POST['filter']) ? sanitize_text_field( wp_unslash( $_POST['filter'] ) ) : null;

        /* If there is no filter get all logs... */
        if ($filter == 'all' ) {
            $logs = self::helloextend_logger_get_all_logs();
        } /* Else filter logs based on specific type... */
        else {
            $logs = self::helloextend_logger_get_these_logs($filter);
        }

        /* Format the logs... */
	    $nonce_logs =  isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) : null;
        $return = self::helloextend_logger_format_logs($logs, $nonce_logs );

        /* Send output back to ajax call... */
	    $allowedtags = array(
		    'a' => array(
			    'href' => true,
			    'title' => true,
			    'data-nonce' =>true,
			    'class' =>true,
			    'rel' =>true,
			    'data-error-code' =>true
		    ),
		    'table'=>array('class' =>true),
		    'thead'=>array('class' =>true),
		    'tbody'=>array('class' =>true),
		    'tr' =>array(
			    'id' =>true,
			    'class' =>true),
		    'th' =>array('class' =>true),
		    'td' =>array('class' =>true),
		    'div' =>array('class' => true)
	    );

	    die(wp_kses($return, $allowedtags));
      
    }

    /*
    helloextend_logger_format_logs() outputs the logs in the right format to go in the log table...
    */

    public static function helloextend_logger_format_logs( &$logs, $nonce )
    {

        if (! $logs ) {
            return __('No logs could be found', 'helloextend-protection');
        }

        /* Sort logs into date order... */
        uasort($logs['logs'], 'self::helloextend_logger_sort_by_date');

        /* Get the list of new logs so we can mark unseen logs as new... */
        $new_logs = get_option('helloextend_logger_new_logs', true);

        /* Create output for each log... */
        $return    = '';
        $count     = 1;
        $row_class = 'helloextend_logger-table-row';

        /* Start the loop... */
        foreach ( $logs['logs'] as $log ) {

            /* Check if the log is new... */
            if ($new_logs ) {

                /* If the current log is in the new logs then add an extra class... */
                if (in_array($log['id'], $new_logs[ $log['type'] . 's' ]) ) {
                    $row_class .= ' helloextend_logger-new-log';
                }
            }

            /* Build the output for each table row... */
            $return .= '<tr class="' . $row_class . ' helloextend_logger-' . $log['type'] . '" id="' . $log['type'] . '-' . $log['id'] . '">';
            $return .= '<td class="helloextend_logger-type-' . $log['type'] . '"></td>';
            $return .= '<td class="helloextend_logger-date">' . date_i18n('d/m/y', $log['date']) . '</td>';
            $return .= '<td class="helloextend_logger-time">' . date_i18n('g.i a', $log['date']) . '</td>';
            $return .= '<td class="helloextend_logger-message">' . $log['message'] . '</td>';
            $return .= '<td class="helloextend_logger-delete">';
            $return .= '<a class="helloextend_logger-delete-button" rel="' . $log['id'] . '" data-error-code="' . $log['id'] . '" data-nonce="' . $nonce . '">';
            $return .= '</a></td></tr>';

            /*
            Now we can alternate the class of the next table row to give that
            pretty stripey effect...
            */
            if ($count == 1 ) {

                $row_class = 'helloextend_logger-table-row helloextend_logger-dark';
                $count++;

            } else {

                $count     = 1;
                $row_class = 'helloextend_logger-table-row';

            }
        }
        /* End the loop... */

        return $return;

    }

    /*
    helloextend_logger_ab_toggle() toggles on/off the admin bar item...
    */

    public static function helloextend_logger_ab_toggle()
    {
        $value  = isset($_POST['update']) ? sanitize_text_field( wp_unslash( $_POST['update'] ) ) : null;
        $update = update_option('helloextend_logger_ab_show', $value);
        die();
    }

    /*
    helloextend_logger_add_to_new_logs() adds a new log to the helloextend_logger_new_logs option...
    Currently used for displaying the amount of unmoderated logs in the admin bar...
    */
    public static function helloextend_logger_add_to_new_logs( $id, $type )
    {
        $new_logs = get_option('helloextend_logger_new_logs');
        if (! $new_logs ) {
            $new_logs = array(
                'errors'  => array(),
                'notices' => array(),
                'debugs'  => array(),
            );
        }

        $new_logs[ $type ][] = $id;
        $update              = update_option('helloextend_logger_new_logs', $new_logs);

    }
}

$helloextendProtectionLogger = new HelloExtend_Protection_Logger();

add_action('wp_ajax_nopriv_helloextend_logger_delete_all', array( $helloextendProtectionLogger, 'helloextend_logger_delete_all' ));
add_action('wp_ajax_helloextend_logger_delete_all', array( $helloextendProtectionLogger, 'helloextend_logger_delete_all' ));

add_action('wp_ajax_nopriv_helloextend_logger_filter_log', array( $helloextendProtectionLogger, 'helloextend_logger_filter_log' ));
add_action('wp_ajax_helloextend_logger_filter_log', array( $helloextendProtectionLogger, 'helloextend_logger_filter_log' ));

add_action('wp_ajax_nopriv_helloextend_logger_ab_toggle', array( $helloextendProtectionLogger, 'helloextend_logger_ab_toggle' ));
add_action('wp_ajax_helloextend_logger_ab_toggle', array( $helloextendProtectionLogger, 'helloextend_logger_ab_toggle' ));

add_action('wp_ajax_nopriv_helloextend_logger_delete_single', array( $helloextendProtectionLogger, 'helloextend_logger_delete_single' ));
add_action('wp_ajax_helloextend_logger_delete_single', array( $helloextendProtectionLogger, 'helloextend_logger_delete_single' ));

