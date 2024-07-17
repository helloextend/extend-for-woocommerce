<?php

/*
@package Custom Error Log
@subpackage Admin

When no errors are logged the contents of this file appear on the admin error log page
It explains to new users how to start logging errors

Introduction...
*/


// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}


$output = __('There is no Extend Log error, debug or notice yet.', 'extend-protection');

$output .= '<hr style="margin-top:30px;"><h3 style="margin-top:30px;">' . __('Usage', 'extend-protection') . '</h3>';
$output .= '<h4 style="margin-bottom: 0px;">'.__('Errors', 'extend-protection').'</h4><pre>Extend_Protection_Logger::extend_log_error( $message );</pre>';
$output .= '<h4 style="margin-bottom: 0px;">'.__('Notices', 'extend-protection').'</h4><pre>Extend_Protection_Logger::extend_log_notice( $message );</pre>';
$output .= '<h4 style="margin-bottom: 0px;">'.__('Debugs', 'extend-protection').'</h4><pre>Extend_Protection_Logger::extend_log_debug( $message );</pre>';

$output .= __('Errors, debugs and notices behave in exactly the same way but you can filter the error log to show only errors or only notices. Once you have logged an error or notice you can return to this page to see a log of all your errors, debugs and notices.', 'extend-protection') . '<br /><br />';


/*
sample usage...
*/

$output .= '<hr style="margin-top:30px;"><h3 style="margin-top:30px;">' . __('Sample Usage', 'extend-protection') . '</h3>';

$output .= __('Log an error', 'extend-protection') . ':';

$output .= '<pre>Extend_Protection_Logger::extend_log_error( \'' . __('There was an error with my theme', 'extend-protection') . '\' );</pre>';

$output .= __('Use dynamic content to log an error', 'extend-protection') . ':';

$output .= '<pre>$message = \'' . __('There was an error with', 'extend-protection') . ' \' . $foo . \' ' . __('in my theme\'', 'extend-protection') . ';

Extend_Protection_Logger::extend_log_error( $message );</pre>';

_e($output);
