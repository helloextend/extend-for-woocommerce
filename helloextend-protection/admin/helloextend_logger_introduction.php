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


$output = __('There is no Extend Log error, debug or notice yet.', 'helloextend-protection');

$output .= '<hr style="margin-top:30px;"><h3 style="margin-top:30px;">' . __('Usage', 'helloextend-protection') . '</h3>';
$output .= '<h4 style="margin-bottom: 0px;">'.__('Errors', 'helloextend-protection').'</h4><pre>HelloExtend_Protection_Logger::helloextend_log_error( $message );</pre>';
$output .= '<h4 style="margin-bottom: 0px;">'.__('Notices', 'helloextend-protection').'</h4><pre>HelloExtend_Protection_Logger::helloextend_log_notice( $message );</pre>';
$output .= '<h4 style="margin-bottom: 0px;">'.__('Debugs', 'helloextend-protection').'</h4><pre>HelloExtend_Protection_Logger::helloextend_log_debug( $message );</pre>';

$output .= 'Errors, debugs and notices behave in exactly the same way but you can filter the error log to show only errors or only notices. Once you have logged an error or notice you can return to this page to see a log of all your errors, debugs and notices.<br /><br />';


/*
sample usage...
*/

$output .= '<hr style="margin-top:30px;"><h3 style="margin-top:30px;">' . __('Sample Usage', 'helloextend-protection') . '</h3>';

$output .= __('Log an error', 'helloextend-protection') . ':';

$output .= '<pre>HelloExtend_Protection_Logger::helloextend_log_error( \'' . __('There was an error with my theme', 'helloextend-protection') . '\' );</pre>';

$output .= __('Use dynamic content to log an error', 'helloextend-protection') . ':';

$output .= '<pre>$message = \'' . __('There was an error with', 'helloextend-protection') . ' \' . $foo . \' ' . __('in my theme\'', 'helloextend-protection') . ';

HelloExtend_Protection_Logger::helloextend_log_error( $message );</pre>';


$allowedtags = array(
	'hr' => array(
		'style' => true,
	),
	'h3'=>array(
		'style' =>true,
		'class' =>true
	),
	'h4'=>array(
		'style' =>true,
		'class' =>true
	),
	'pre'=>array(
		'style' =>true,
		'class' =>true
	)
);

echo wp_kses($output, $allowedtags);


