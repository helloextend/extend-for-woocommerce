<?php

/*
@package Custom Error Log
@subpackage Admin

When no errors are logged the contents of this file appear on the admin error log page
It explains to new users how to start logging errors

Introduction...
*/

$output = __( 'You haven\'t logged any errors yet. To log errors use the following function in your theme template files...', 'custom-error-log' );

$output .= '<h4 style="margin-bottom: 0px;">Errors</h4><pre>extend_log_error( $message );</pre>';

$output .= '<h4 style="margin-bottom: 0px;">Notices</h4><pre>extend_log_notice( $message );</pre>';

$output .= __( 'Errors and notices behave in exactly the same way but you can filter the error log to show only errors or only notices. Once you have logged an error or notice you can return to this page to see a log of all your errors and notices.', 'custom-error-log' ) . '<br /><br />';

/*
Parameters...
*/

$output .= '<hr><h3 style="margin-top:30px;">' . __( 'Parameters', 'custom-error-log' ) . '</h3>';

$output .=  '<h4 style="margin-bottom: 5px;">$message</h4>';

$output .= '(String)(' . __( 'required', 'custom-error-log' ) . ') ';

$output .= __( 'The error/notice message you want to log for internal use.', 'custom-error-log' );

$output .= '<br />&nbsp; &nbsp; &nbsp; &nbsp; <span class="default">' . __( 'Default', 'custom-error-log' ) . ': None</span>';

/*
sample usage...
*/

$output .= '<hr style="margin-top:30px;"><h3 style="margin-top:30px;">' . __( 'Sample Usage', 'custom-error-log' ) . '</h3>';

$output .= __( 'Log an error', 'custom-error-log' ) . ':';

$output .= '<pre>extend_log_error( \'' . __( 'There was an error with my theme', 'custom-error-log' ) . '\' );</pre>';

$output .= __( 'Use dynamic content to log an error', 'custom-error-log' ) . ':';

$output .= '<pre>$message = \'' . __( 'There was an error with', 'custom-error-log' ) . ' \' . $foo . \' ' . __( 'in my theme\'', 'custom-error-log' ) . ';
extend_log_error( $message );</pre>';

$output .= '<strong>' . __( 'A real life example', 'custom-error-log' ) . ':</strong> ';

$output .= __( 'error handling when creating a Wordpress user programatically', 'custom-error-log' ) . '.';

$output .= '<pre>
<span class="pre-comment">//Create user...</span>
$user_id = wp_create_user( $user_name, $password, $user_email );

<span class="pre-comment">//If user creation was unsuccessful...</span>
if( is_wp_error( $user_id ) ) {
	
	<span class="pre-comment">//Get error response</span>
	$error_response = $user_id->get_error_message();
	
	<span class="pre-comment">//Build custom error message</span>
	$mesage = \'Unable to create user with username: \' . $user_name;
	$message .= \' password: \' . $password;
	$message .= \' The following error occurred: \' . $error_response;
	
	<span class="pre-comment">//Log custom error</span>
	extend_log_error( $message );

}</pre>';

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