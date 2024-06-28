<?php
/*
@package Custom Error Log
@subpackage Includes

This file does the main work of the plugin

log_error() function, this allows developers to log custom errors in their theme/plugin...
*/
class Extend_Protection_Logger
{

    public static function extend_log_error( $message )
    {

        /* Get error logs from the wp_options table... */
        $error_log = get_option('custom_error_log');

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
        $update = update_option('custom_error_log', $error_log);

        /* Add to list of new logs... */
        if ($update ) {
            self::extend_logger_add_to_new_logs($error_id, 'errors');
        }

    }

    /*
    log_notice() function, this allows developers to log custom notices in their theme/plugin...
    */

    public static function extend_log_notice( $message )
    {

        /* Get notice logs from the wp_options table... */
        $notice_log = get_option('custom_notice_log', true);
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
        $update = update_option('custom_notice_log', $notice_log);

        /* Add to list of new logs... */
        if ($update ) {

            self::extend_logger_add_to_new_logs($notice_id, 'notices');

        }

    }

    /*
    log_notice() function, this allows developers to log custom notices in their theme/plugin...
    */

    public static function extend_log_debug( $message )
    {

        /* Get notice logs from the wp_options table... */
        $debug_log = get_option('custom_debug_log', true);
        if (! $debug_log ) {

            $debug_log = array(

            'debugs'     => array(),
            'next_debug' => 1,

            );

        }

        $debug_id = $debug_log['next_debug'];

        /* Insert new debug into array... */
        $debug_log['debugs'][ $debug_id ] = array(

        'type'    => 'debug',
        'date'    => current_time('timestamp'),
        'id'      => $debug_id,
        'message' => sanitize_text_field($message),

        );

        /* Increase the debug code to use for the next debug logged... */
        $debug_log['next_debug']++;

        /* Update the debug log in the wp_options table... */
        $update = update_option('custom_debug_log', $debug_log);

        /* Add to list of new logs... */
        if ($update ) {

            self::extend_logger_add_to_new_logs($debug_id, 'debugs');

        }

    }

    /*
    extend_logger_delete_single() gets used by the error log table to delete a single error or notice from the array...
    */

    public static function extend_logger_delete_single()
    {

        /* Check that the nonce is correct to avoid safety issues... */
        if (! wp_verify_nonce($_POST['nonce'], 'extend_logger_nonce') ) {

            exit('Wrong nonce - delete single');

        }

        /* Get information about the error to delete from the ajax POST... */
        $error_code = $_POST['error_code'];
        $log_type   = $_POST['log_type'];

        /* Get the correct log from the wp_options table... */
        $logs = get_option('custom_' . $log_type . '_log', true);

        /* Unset the correct error/notice from the array... */
        foreach ( $logs[ $log_type . 's' ] as $key => $log ) {

            if ($log['id'] == $error_code ) {

                unset($logs[ $log_type . 's' ][ $key ]);

            }
        }

        /* Update the log in the wp_options table... */
        $update = update_option('custom_' . $log_type . '_log', $logs);

        /* Build the response... */
        if ($update ) {

            $return  = '<div class="updated  ajax-response">';
            $return .= sprintf(__('%1$s %2$d has been successfully deleted', 'extend-protection'), $log_type, $error_code);
            $return .= '.</div>';

        } else {

            $return  = '<div class="error  ajax-response">';
            $return .= sprintf(__('%1$s %2$d could not be deleted', 'extend-protection'), $log_type, $error_code);
            $return .= '.</div>';

        }

        /* Send the response back to the ajax call... */
        die($return);

    }

    /*
    extend_logger_delete_all() gets used by the error log table to clear all errors and notices...
    */

    public static function extend_logger_delete_all()
    {
        /* Check that the nonce is correct to avoid safety issues... */
        if (! wp_verify_nonce($_POST['nonce'], 'extend_logger_nonce') ) {
            exit('Wrong nonce - delete all');
        }

        /*
        Empty fields stored in the wp_options table... */

        $error_log_empty  = array();
        $notice_log_empty = array();
        $debug_log_empty  = array();

        $deleted_errors  = update_option('custom_error_log', $error_log_empty);
        $deleted_notices = update_option('custom_notice_log', $notice_log_empty);
        $deleted_debugs  = update_option('custom_debug_log', $debug_log_empty);

        /* Build the response */
        if ($deleted_errors || $deleted_notices || $deleted_debugs ) {

            $return  = '<div class="updated  ajax-response">';
            $return .= __('All errors have been deleted', 'extend-protection');
            $return .= '.</div>';

        } else {

            $return  = '<div class="error  ajax-response">';
            $return .= __('Errors could not be deleted', 'extend-protection');
            $return .= '.</div>';

        }

        /* Send response back to ajax call... */
        die($return);

    }

    /*
    extend_logger_sort_by_date() gets used by the error log table to sort all errors and notices by date...
    */

    public static function extend_logger_sort_by_date( $a, $b ): int
    {

        if ($a['date'] == $b['date'] ) {
            return 0;
        }

        return ( $a['date'] < $b['date'] ) ? 1 : -1;

    }

    /*
    extend_logger_get_all_logs() retreives all logs and returns them...
    */

    public static function extend_logger_get_all_logs()
    {

        $errors  = get_option('custom_error_log', true);
        $notices = get_option('custom_notice_log', true);
        $debugs  = get_option('custom_debug_log', true);

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
    extend_logger_get_these_logs() gets and returns only one type of log specified by $type...
    $type can be either 'error' or 'notice'...
    */

    public static function extend_logger_get_these_logs( $type )
    {

        $logs = get_option('custom_' . $type . '_log', true);

        /* Get one step further down the array... */
        if ($logs ) {

            $logs = $logs[ $type . 's' ];

        }

        /* These variables are used so that the output is the same as extend_logger_get_all_logs()... */
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
    extend_logger_filter_log() filters the error log table so that only errors or notices are displayed...
    */

    public static function extend_logger_filter_log()
    {

        /*
        Check that the nonce is correct to avoid safety issues...
        The nonce is passed via a POST from the ajax call...
        */
        if (! wp_verify_nonce($_POST['nonce'], 'extend_logger_nonce') ) {
            exit('Wrong nonce filter log');
        }

        /*
        The filter is posted by the ajax call to tell this function which
        type of logs it wants...
        */
        $filter = $_POST['filter'];

        /* If there is no filter get all logs... */
        if ($filter == 'all' ) {
            $logs = self::extend_logger_get_all_logs();
        } /* Else filter logs based on specific type... */
        else {
            $logs = self::extend_logger_get_these_logs($filter);
        }

        /* Format the logs... */
        $return = self::extend_logger_format_logs($logs, $_POST['nonce']);

        /* Send output back to ajax call... */
        die($return);

    }

    /*
    extend_logger_format_logs() outputs the logs in the right format to go in the log table...
    */

    public static function extend_logger_format_logs( &$logs, $nonce )
    {

        if (! $logs ) {
            return __('No logs could be found', 'extend-protection');
        }

        /* Sort logs into date order... */
        uasort($logs['logs'], 'self::extend_logger_sort_by_date');

        /* Get the list of new logs so we can mark unseen logs as new... */
        $new_logs = get_option('extend_logger_new_logs', true);

        /* Create output for each log... */
        $return    = '';
        $count     = 1;
        $row_class = 'extend_logger-table-row';

        /* Start the loop... */
        foreach ( $logs['logs'] as $log ) {

            /* Check if the log is new... */
            if ($new_logs ) {

                /* If the current log is in the new logs then add an extra class... */
                if (in_array($log['id'], $new_logs[ $log['type'] . 's' ]) ) {
                    $row_class .= ' extend_logger-new-log';
                }
            }

            /* Build the output for each table row... */
            $return .= '<tr class="' . $row_class . ' extend_logger-' . $log['type'] . '" id="' . $log['type'] . '-' . $log['id'] . '">';
            $return .= '<td class="extend_logger-type-' . $log['type'] . '"></td>';
            $return .= '<td class="extend_logger-date">' . date_i18n('d/m/y', $log['date']) . '</td>';
            $return .= '<td class="extend_logger-time">' . date_i18n('g.i a', $log['date']) . '</td>';
            $return .= '<td class="extend_logger-message">' . $log['message'] . '</td>';
            $return .= '<td class="extend_logger-delete">';
            $return .= '<a class="extend_logger-delete-button" rel="' . $log['id'] . '" data-error-code="' . $log['id'] . '" data-nonce="' . $nonce . '">';
            $return .= '</a></td></tr>';

            /*
            Now we can alternate the class of the next table row to give that
            pretty stripey effect...
            */
            if ($count == 1 ) {

                $row_class = 'extend_logger-table-row extend_logger-dark';
                $count++;

            } else {

                $count     = 1;
                $row_class = 'extend_logger-table-row';

            }
        }
        /* End the loop... */

        return $return;

    }

    /*
    extend_logger_ab_toggle() toggles on/off the admin bar item...
    */

    public static function extend_logger_ab_toggle()
    {
        $value  = $_POST['update'];
        $update = update_option('extend_logger_ab_show', $value);
        die();
    }

    /*
    extend_logger_add_to_new_logs() adds a new log to the extend_logger_new_logs option...
    Currently used for displaying the amount of unmoderated logs in the admin bar...
    */
    public static function extend_logger_add_to_new_logs( $id, $type )
    {
        $new_logs = get_option('extend_logger_new_logs');
        if (! $new_logs ) {
            $new_logs = array(
            'errors'  => array(),
            'notices' => array(),
            'debugs'  => array(),
            );
        }

        $new_logs[ $type ][] = $id;
        $update              = update_option('extend_logger_new_logs', $new_logs);

    }
}

$extendProtectionLogger = new Extend_Protection_Logger();

add_action('wp_ajax_nopriv_extend_logger_delete_all', array( $extendProtectionLogger, 'extend_logger_delete_all' ));
add_action('wp_ajax_extend_logger_delete_all', array( $extendProtectionLogger, 'extend_logger_delete_all' ));

add_action('wp_ajax_nopriv_extend_logger_filter_log', array( $extendProtectionLogger, 'extend_logger_filter_log' ));
add_action('wp_ajax_extend_logger_filter_log', array( $extendProtectionLogger, 'extend_logger_filter_log' ));

add_action('wp_ajax_nopriv_extend_logger_ab_toggle', array( $extendProtectionLogger, 'extend_logger_ab_toggle' ));
add_action('wp_ajax_extend_logger_ab_toggle', array( $extendProtectionLogger, 'extend_logger_ab_toggle' ));

add_action('wp_ajax_nopriv_extend_logger_delete_single', array( $extendProtectionLogger, 'extend_logger_delete_single' ));
add_action('wp_ajax_extend_logger_delete_single', array( $extendProtectionLogger, 'extend_logger_delete_single' ));

