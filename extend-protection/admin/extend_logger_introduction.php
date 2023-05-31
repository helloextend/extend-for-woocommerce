<?php

/*
@package Custom Error Log
@subpackage Admin

When no errors are logged the contents of this file appear on the admin error log page
It explains to new users how to start logging errors

Introduction...
*/

$output = __( 'There is no Extend Log error or notice yet.', 'custom-error-log' );

$output .= '<hr style="margin-top:30px;"><h3 style="margin-top:30px;">' . __( 'Usage', 'custom-error-log' ) . '</h3>';

$output .= '<h4 style="margin-bottom: 0px;">Errors</h4><pre>extend_log_error( $message );</pre>';

$output .= '<h4 style="margin-bottom: 0px;">Notices</h4><pre>extend_log_notice( $message );</pre>';

$output .= __( 'Errors and notices behave in exactly the same way but you can filter the error log to show only errors or only notices. Once you have logged an error or notice you can return to this page to see a log of all your errors and notices.', 'custom-error-log' ) . '<br /><br />';


/*
sample usage...
*/

$output .= '<hr style="margin-top:30px;"><h3 style="margin-top:30px;">' . __( 'Sample Usage', 'custom-error-log' ) . '</h3>';

$output .= __( 'Log an error', 'custom-error-log' ) . ':';

$output .= '<pre>extend_log_error( \'' . __( 'There was an error with my theme', 'custom-error-log' ) . '\' );</pre>';

$output .= __( 'Use dynamic content to log an error', 'custom-error-log' ) . ':';

$output .= '<pre>$message = \'' . __( 'There was an error with', 'custom-error-log' ) . ' \' . $foo . \' ' . __( 'in my theme\'', 'custom-error-log' ) . ';
extend_log_error( $message );</pre>';

/*
Make it theme ready...
*/

$output .= '<hr style="margin-top:30px;"><h3 style="margin-top:30px;">' . __( 'Make it theme ready', 'custom-error-log' ) . '</h3>';

$output .= __( 'If you want to include the basic functions of custom error log in themes that will be used by other people make sure to check if the function exists first to avoid errors', 'custom-error-log' ) . ':';

$output .= '<pre>
<span class="pre-comment">//If custom error log is active...</span>
if( function_exists( \'extend_log_error\' ) {

	<span class="pre-comment">//Log the error...</span>
	extend_log_error( \'' . __( 'There was an error with my theme', 'custom-error-log' ) . '\' );

}
</pre>';

echo $output;