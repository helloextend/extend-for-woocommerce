(function ( $ ) {
    'use strict';
    $(document).ready(
        function ($) {
            if(!ExtendWooCommerce) { return;
            }

            const { store_id: storeId, ajaxurl, environment, nonce, extend_sync_batch } = ExtendWooCommerce;

            Extend.config(
                {
                    storeId,
                    environment
                }
            );

            var newProductSyncDate = 'Never';
            var currentIndex = 0;
            var batchSize = extend_sync_batch > 0 ? extend_sync_batch  : 100; // Number of products to process per AJAX call

            //reset last sync button event
            $('#helloextend-catalog-sync-reset').on(
                "click", function () {
                    $('#progress-bar-container').hide();
                    $('#progress-bar').css('width', '0% !important');

                    $.ajax(
                        {
                            type:   'POST',
                            url:    ajaxurl,
                            data: {
                                action: 'extend_catalog_sync_reset'
                            },
                            success: function () {
                                $("span#last_sync_field").text(newProductSyncDate)
                            }
                        }
                    );
                }
            );

            // update progress bar
            function updateProgressBar(batch_number, batch_total)
            {
                let percentage = (batch_number / batch_total) * 100;
                console.log('Processing batch ' + batch_number + '/'+batch_total);
                $('#progress-bar').css('width', percentage + '%');
            }

            //run new sync
            function processBatchProducts()
            {
                $('#progress-bar-container').show();
                $.ajax(
                    {
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action:     'extend_catalog_sync_run',
                            nonce:      ExtendWooCommerce.nonce,
                            index:      currentIndex,
                            batch_size: batchSize
                        },
                        success: function (response) {
                            var batch_total     = response.data['batch_total']  ;
                            var batch_number    = response.data['batch_number']  ;

                            if (response.success) {
                                if (batch_number < batch_total) {
                                    updateProgressBar(batch_number, batch_total);
                                    currentIndex += batchSize;
                                    processBatchProducts();
                                } else {
                                    // All batches processed
                                    updateProgressBar(batch_number, batch_total);
                                    console.log('All batches processed');
                                    currentIndex = 0;

                                    //hide progress bar after 2s
                                    setTimeout(
                                        function () {
                                            $('#progress-bar-container').fadeOut('fast');
                                            $('#progress-bar').css('width', '0');
                                        }, 2000
                                    ); // <-- time in milliseconds

                                    //update last sync date
                                    $.ajax(
                                        {
                                            type: 'POST',
                                            url: ajaxurl,
                                            data: {
                                                action: 'update_last_run_sync',
                                                nonce: ExtendWooCommerce.nonce
                                            },
                                            success: function (response) {
                                                $("span#last_sync_field").text(response.data.time);
                                                $("input#helloextend_last_product_sync").val(response.data.sync_unixtime);
                                            }
                                        }
                                    );
                                }
                            } else {
                                // Handle error
                                console.error('Error processing batch ' + batch_number);
                            }
                        }
                    }
                ) //end ajax
            } //end function processBatchProducts

            // Event handler for a Run Manual Sync
            $('#helloextend-catalog-sync-run').on(
                "click", function () {
                    $('#progress-bar').css('width', '0');
                    processBatchProducts();
                }
            );
        }
    )
})(jQuery);



