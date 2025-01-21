/*
@package Custom Error Log
@subpackage Includes

This file handles all the ajax used by the delete buttons in the error_log table.
*/

var $ = jQuery.noConflict();

/*
Load all functions when document is ready...
*/

$(document).ready(
    function () {

        helloextend_loggerDeleteSingle();
        helloextend_loggerDeleteAll();
        helloextend_loggerLogFilter();
        helloextend_loggerAbToggle();

    }
);

/*
Delete a single error when a delete button is clicked...
*/

function helloextend_loggerDeleteSingle()
{

    $('.helloextend_logger-delete-button').on(
        'click', function () {

            /* Get log type... */
            if($(this).parent().parent().hasClass('helloextend_logger-error') ) {
                log_type = 'error';
            }
            else if($(this).parent().parent().hasClass('helloextend_logger-debug') ) {
                log_type = 'debug';
            }
            else {
                log_type = 'notice';
            }

            /* Delete the error visibily from the error log table... */
            var error_code = $(this).attr('rel');
            var deleted = '#'+log_type+'-'+error_code;

            /* Toggle class of all table rows after current to maintain the nice stripes... */
            $(deleted).nextAll().toggleClass('helloextend_logger-dark');
            $(deleted).hide();

            $('#helloextend_logger-ajax-message').html('').append('<div class="update-nag ajax-response">'+errorAjax.deleting+'</div>');

            var nonce = $(this).attr('data-nonce');

            /* Now use an ajax call to delete the error from the wp_options table... */
            $.ajax(
                {

                    type: 'POST',
                    url: errorAjax.ajaxurl,
                    data: {

                        action: 'helloextend_logger_delete_single',
                        error_code: error_code,
                        log_type: log_type,
                        nonce: nonce

                    },
                    success: function ( data, textStatus, XMLHttpRequest ) {

                        $('#helloextend_logger-ajax-message').html('');
                        $('#helloextend_logger-ajax-message').append(data);

                    },
                    error: function ( MLHttpRequest, textStatus, errorThrown ) {

                        alert(errorThrown);

                    }

                }
            );

        }
    );

}

/*
Clear all errors when the delete all button is clicked
*/

function helloextend_loggerDeleteAll()
{

    $('.helloextend_logger-delete-all').on(
        'click', function () {

            /* Delete all errors visibily from the error log table... */
            $('.helloextend_logger-table-row').hide();

            $('#helloextend_logger-ajax-message').html('').append('<div class="update-nag ajax-response">'+errorAjax.deleting+'</div>');

            var nonce = $(this).attr('data-nonce');

            /* Now use an ajax call to delete all errors from the wp_options table... */
            $.ajax(
                {

                    type: 'POST',
                    url: errorAjax.ajaxurl,
                    data: {

                        action: 'helloextend_logger_delete_all',
                        nonce: nonce

                    },
                    success: function ( data, textStatus, XMLHttpRequest ) {

                        $('#helloextend_logger-ajax-message').html('');
                        $('#helloextend_logger-ajax-message').append(data);

                    },
                    error: function ( MLHttpRequest, textStatus, errorThrown ) {
                        alert('error: ' +errorThrown);
                    }

                }
            );

        }
    );

}

/*
Filter log to show only errors or notices
*/

function helloextend_loggerLogFilter()
{

    $('.helloextend_logger-log-filter').on(
        'click', function () {

            /* Empty the log table... */
            $('.helloextend_logger-table tbody').html('').append('<tr><td>Filtering...</td></tr>');

            /* Pass filter and nonce to ajax call... */
            var nonce = $(this).attr('nonce');
            var filter = $(this).attr('filter');

            $.ajax(
                {

                    type: 'POST',
                    url: errorAjax.ajaxurl,
                    data: {

                        action: 'helloextend_logger_filter_log',
                        nonce: nonce,
                        filter: filter

                    },
                    success: function ( data, textStatus, XMLHttpRequest ) {

                        $('.helloextend_logger-table tbody').html('');
                        $('.helloextend_logger-table tbody').append(data);

                        /* Rebind event to delete buttons after ajax call */
                        helloextend_loggerDeleteSingle();

                    },
                    error: function ( MLHttpRequest, textStatus, errorThrown ) {

                        alert(errorThrown);

                    }

                }
            );

        }
    );

}

/*
Toggles on and off the admin bar button...
*/

function helloextend_loggerAbToggle()
{

    $('#helloextend_logger_ab_show').change(
        function () {

            if($(this).is(":checked") ) {

                  var toggle_value = 1;
                  $('#wp-admin-bar-error-log').show();

            }

            else {

                var toggle_value = 0;
                $('#wp-admin-bar-error-log').hide();

            }

            $.ajax(
                {

                    type: 'POST',
                    url: errorAjax.ajaxurl,
                    data: {

                        action: 'helloextend_logger_ab_toggle',
                        update: toggle_value,

                    },
                    success: function ( data, textStatus, XMLHttpRequest ) {

                    },
                    error: function ( MLHttpRequest, textStatus, errorThrown ) {

                        alert(errorThrown);

                    }

                }
            );

        }
    );

}

/*
Load function when the window has loaded...
*/

$(window).on(
    'load', function () {

        helloextend_loggerHighlightNewLogs();

    }
);

/*
extend_loggerHighlightNewLogs() adds a bit of a flourish to new logs...
*/

function helloextend_loggerHighlightNewLogs()
{

    $('.helloextend_logger-new-log').each(
        function () {

            /* Find out if this is a dark row or not... */
            if($(this).is('.helloextend_logger-dark') ) {

                  var color = '#e6e6e6';

            }

            else {

                var color = '#f1f1f1';

            }

            /* Store current item as var so we can use it in a timeout function... */
            var current = this;

            /* after 800ms convert the background color back to normal... */
            setTimeout(
                function () {

                    $(current).find('td').css('background', color);

                }, 800 
            );

        }
    )

}
