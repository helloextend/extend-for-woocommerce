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


$logs = Extend_Protection_Logger::extend_logger_get_all_logs();

?>


<div class="wrap" id="error-log">

    <hr style="margin-bottom: 15px;">

    <div id="extend_logger-ajax-message"></div>

    <?php

    /* If there are any logs create the log table... */
    if ($logs && $logs['logs'] ) {

        $nonce = wp_create_nonce('extend_logger_nonce');

        /* If there are both notices and errors output filter buttons... */
        if ($logs['have_both'] == true || $logs['have_many'] == true ) {
            ?>

            <a class="extend_logger-log-filter" filter="all" nonce="<?php echo esc_attr($nonce); ?>">

            <?php _e('All', 'extend-protection'); ?>

            </a> |

            <a class="extend_logger-log-filter" filter="error" nonce="<?php echo esc_attr($nonce); ?>">

            <?php _e('Errors', 'extend-protection'); ?>

            </a> |

            <a class="extend_logger-log-filter" filter="notice" nonce="<?php echo esc_attr($nonce); ?>">

            <?php _e('Notices', 'extend-protection'); ?>

            </a> |

            <a class="extend_logger-log-filter" filter="debug" nonce="<?php echo esc_attr($nonce); ?>">

            <?php _e('Debugs', 'extend-protection'); ?>

            </a>

        <?php } ?>

        <a class="extend_logger-delete-all" data-nonce="<?php echo esc_attr($nonce); ?>"><?php _e('Clear Log', 'extend-protection'); ?></a>

        <table class="extend_logger-table">

            <thead>

            <tr>

                <th class="extend_logger-type"></th>

                <th class="extend_logger-date"><?php _e('Date', 'extend-protection'); ?></th>

                <th class="extend_logger-time"><?php _e('Time', 'extend-protection'); ?></th>

                <th class="extend_logger-message"><?php _e('Message', 'extend-protection'); ?></th>

                <th class="extend_logger-delete"></th>

            </tr>

            </thead>

            <tbody>

        <?php

        /* Output all logs into the table... */
        printf(Extend_Protection_Logger::extend_logger_format_logs($logs, $nonce));

        ?>

            </tbody>

        </table>

        <?php

    }

    /* If there are no logs output the introduction text from introduction.php... */
    else {

        include EXTEND_LOGGER_DIR . '/admin/extend_logger_introduction.php';

    }

    ?>

</div>
