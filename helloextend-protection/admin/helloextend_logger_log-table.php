<?php

/*
@package Custom Error Log
@subpackage Admin

This file holds the output for the admin error log under the 'Tools' menu.
*/


// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}


$logs = HelloExtend_Protection_Logger::helloextend_logger_get_all_logs();

?>


<div class="wrap" id="error-log">

    <hr style="margin-bottom: 15px;">

    <div id="helloextend_logger-ajax-message"></div>

    <?php

    /* If there are any logs create the log table... */
    if ($logs && $logs['logs'] ) {

        $nonce = wp_create_nonce('helloextend_logger_nonce');

        /* If there are both notices and errors output filter buttons... */
        if ($logs['have_both'] == true || $logs['have_many'] == true ) {
            ?>

            <a class="helloextend_logger-log-filter" filter="all" nonce="<?php echo esc_attr($nonce); ?>">

            <?php esc_html('All'); ?>

            </a> |

            <a class="helloextend_logger-log-filter" filter="error" nonce="<?php echo esc_attr($nonce); ?>">

            <?php esc_html('Errors'); ?>

            </a> |

            <a class="helloextend_logger-log-filter" filter="notice" nonce="<?php echo esc_attr($nonce); ?>">

            <?php esc_html('Notices'); ?>

            </a> |

            <a class="helloextend_logger-log-filter" filter="debug" nonce="<?php echo esc_attr($nonce); ?>">

            <?php  esc_html('Debugs'); ?>

            </a>

        <?php } ?>

        <a class="helloextend_logger-delete-all" data-nonce="<?php echo esc_attr($nonce); ?>"><?php esc_html('Clear Log'); ?></a>

        <table class="helloextend_logger-table">

            <thead>

            <tr>

                <th class="helloextend_logger-type"></th>

                <th class="helloextend_logger-date"><?php esc_html('Date'); ?></th>

                <th class="helloextend_logger-time"><?php esc_html('Time'); ?></th>

                <th class="helloextend_logger-message"><?php esc_html('Message'); ?></th>

                <th class="helloextend_logger-delete"></th>

            </tr>

            </thead>

            <tbody>

        <?php

        /* Output all logs into the table... */
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
		        'td' =>array('class' =>true)
            );


        echo wp_kses(HelloExtend_Protection_Logger::helloextend_logger_format_logs($logs, $nonce), $allowedtags);

        ?>

            </tbody>

        </table>

        <?php

    }

    /* If there are no logs output the introduction text from introduction.php... */
    else {

        include HELLOEXTEND_LOGGER_DIR . '/admin/helloextend_logger_introduction.php';

    }

    ?>

</div>
