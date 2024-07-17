<?php

/**
 * Extend For WooCommerce Product Integration.
 *
 * @since   1.0.0
 * @package Extend_Protection
 *
 * @package    Extend_Protection
 * @subpackage Extend_Protection/admin
 */

// Prevent direct access to the file
if (! defined('ABSPATH') ) {
    exit;
}

/**
 * The Product Sync functionality of the plugin.
 *
 * Allows syncing of your catalog to Extend's mapping database
 * to determine proper offers per item. Functionality from the admin.
 *
 * @package Extend_Protection
 * @author  Extend, Inc.
 */
class Extend_Protection_Sync
{

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $extend_protection The ID of this plugin.
     */
    private string $extend_protection;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $version The current version of this plugin.
     */
    private string $version;
    private array $settings;
    private string $directory;

    public function __construct( $extend_protection, $version )
    {
        $this->extend_protection = $extend_protection;
        $this->version           = $version;
        $this->settings          = Extend_Protection_Global::get_extend_settings();
        $this->directory         = ABSPATH . 'wp-content/extend/sync';
        $this->hooks();

    }

    /**
     * Initiate our hooks.
     *
     * @since 0.0.0
     */
    public function hooks()
    {

        /* catalog sync admin events */
        add_action('wp_ajax_extend_catalog_sync_reset', [ $this, 'extend_catalog_sync_reset' ], 10);
        add_action('wp_ajax_nopriv_extend_catalog_sync_reset', [ $this, 'extend_catalog_sync_reset' ], 10);
        add_action('wp_ajax_extend_catalog_sync_run', [ $this, 'extend_catalog_sync_run' ], 10);
        add_action('wp_ajax_nopriv_extend_catalog_sync_run', [ $this, 'extend_catalog_sync_run' ], 10);
        add_action('wp_ajax_update_last_run_sync', [ $this, 'update_last_run_sync' ], 10);
        add_action('wp_ajax_nopriv_update_last_run_sync', [ $this, 'update_last_run_sync' ], 10);

        /* 'save_post' action for WooCommerce products, start sync on save_post event. */
        add_action('save_post', [ $this, 'sync_products_callback' ], 10, 2);

        // Hook the cron job functions.
        add_action('sync_products_hourly', [ $this, 'sync_products_cron_job' ], 10, 2);
        add_action('sync_products_daily', [ $this, 'sync_products_cron_job' ], 10, 2);
        add_action('sync_products_weekly', [ $this, 'sync_products_cron_job' ], 10, 2);
    }

    /*
    * Reset the last sync date
    */
    public function extend_catalog_sync_reset()
    {
        if (! defined('DOING_AJAX') ) {
            return;
        }

        $sync_options                             = get_option('extend_protection_for_woocommerce_catalog_sync_settings');
        $sync_options['extend_last_product_sync'] = null;

        update_option('extend_protection_for_woocommerce_catalog_sync_settings', $sync_options);
        wp_die();
    }

    /*
    * Run the product catalog sync in batches
    */
    public function extend_catalog_sync_run()
    {

        if (! defined('DOING_AJAX') ) {
            return;
        }

        check_ajax_referer('extend_sync_nonce', 'nonce');

        $batch_size = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 100;
        $index      = isset($_POST['index']) ? intval($_POST['index']) : 0;

        /*
        *  build WP Query to retrieve all products, non virtual
        *  order by product name and if there is a last sync date,
        *  filter out any product not updated since
        */
        $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'meta_query'     => array(
        array(
                    'key'     => '_virtual',
                    'value'   => 'yes',
                    'compare' => '!=',
        ),
        ),
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        );

        // if there is a last sync date, adjust the filter
        if (strtolower($this->settings['extend_last_product_sync']) != 'never' && ! is_null($this->settings['extend_last_product_sync']) ) {
            $args['date_query'] = array(
            array(
            'after'     => date('Y-m-d h:i:s', strtolower($this->settings['extend_last_product_sync'])),
            'inclusive' => 'true',
            ),
            );
        }
        // run the query once  to retrieve the number of products to calculate total batches
        $productsTotal = new WP_Query($args);
        $totalCount    = $productsTotal->post_count;
        $batch_total   = ceil($totalCount / $batch_size);
        $batch_current = ( $index + $batch_size ) / $batch_size;

        // run the query again but with the proper offset if present, to process a given batch
        if (isset($batch_size) ) {
            $args['posts_per_page'] = $batch_size;
        }
        if (isset($_POST['index']) ) {
            $args['offset'] = $index;
        }

        $products = new WP_Query($args);

        // Debug : show the actual SQL query to see what was processed
        // Extend_Protection_Logger::extend_log_debug('Query :' .$products->request);

        $batch_data = array();

        if ($products->have_posts() ) {
            while ( $products->have_posts() ) {
                $products->the_post();
                $product_id = get_the_ID();

                // build the batch data from the product
                $batch_data[] = $this->process_product_data($product_id);
            }

            if ($this->settings['enable_extend_debug'] == 1 ) {
                Extend_Protection_Logger::extend_log_debug('DEBUG: batchdata for batch #' . $batch_current . ' >>> ' . print_r($batch_data, true));
            }
            // batch_data is the payload we send to extend
            $request_args = $this->buildRequest($batch_data);
            $response     = wp_remote_request($this->settings['api_host'] . '/stores/' . $this->settings['store_id'] . '/products?batch=true', $request_args);

            if ($this->settings['enable_extend_debug'] == 1 ) {
                Extend_Protection_Logger::extend_log_debug('DEBUG response: ' . print_r($response, true));
            }

            if (is_wp_error($response) ) {
                $error_message = $response->get_error_message();
                Extend_Protection_Logger::extend_log_error(' Catalog Sync Batch #' . $batch_current . ' : POST request failed: ' . $error_message);
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                if ($response_code === 201 ) {
                    // success
                    Extend_Protection_Logger::extend_log_notice('Catalog Sync Batch #' . $batch_current . ' was successful');

                    $data = json_decode(wp_remote_retrieve_body($response));

                    // write to logs and log summary with the result from the response
                    $this->process_response($data, $batch_current);

                }
            }
            $this->log_syncs($response, $batch_data, $batch_current);
            $this->send_sync_success($batch_current, $batch_data, $batch_total);

        } else {
            Extend_Protection_Logger::extend_log_error('No recently updated products found to sync.');
            $batch_data_empty = array();
            $this->send_sync_success(1, $batch_data_empty, 1);
        }
    }

    /*
    * Json response ajax success
    */
    function send_sync_success( $batch_number, $batch_data, $batch_total )
    {
        wp_send_json_success(
            array(
            'batch_number' => $batch_number,
            'batch_data'   => $batch_data,
            'batch_total'  => $batch_total,
            )
        );
    }

    /*
    *  build the payload arrays
    */
    function process_product_data( $product_id )
    {
        $product     = wc_get_product($product_id);
        $price       = $this->get_product_price($product);
        $description = $product->get_short_description();
        $category    = null;
        $terms       = get_the_terms($product_id, 'product_cat');

        // retrieve category name
        foreach ( $terms as $term ) {
            $category = $term->name;
            break;
        }

        // trim description if too big
        if (strlen($description) > 2000 ) {
            $description = substr($description, 0, 2000);
        }

        // retrieve image url
        if (has_post_thumbnail($product_id) ) {
            $image_url = wp_get_attachment_url(get_post_thumbnail_id($product_id));
        } else {
            $image_url = '';
        }

        $payload = array(
        'category'    => $category ?? 'No Category',
        'title'       => $product->get_name(),
        'referenceId' => $this->get_sku_or_id($product),
        'price'       => array(
        'amount'       => $price * 100,
        'currencyCode' => get_option('woocommerce_currency'),
        ),
        'identifiers' => array(
        'sku'  => $this->get_sku_or_id($product),
        'type' => $product->get_type(),
        ),
        'description' => $description ?? 'No Description',
        'imageUrl'    => $image_url,
        );

        // is product a variant ?
        if ($product->get_parent_id() ) {
            $parent_product                      = wc_get_product($product->get_parent_id());
            $payload['parentReferenceId']        = $this->get_sku_or_id($parent_product);         // $parent_product->get_sku() ?? $parent_product->get_ID();
            $payload['identifiers']['parentSku'] = $this->get_sku_or_id($parent_product);  // $parent_product->get_sku() ?? $parent_product->get_ID();
            $payload['identifiers']['type']      = $product->get_type();
        }

        /*
        *  for each variable product, recursively run the same function with all the variations
        *  and feed it in the payload array
        */
        if ($product->get_type() == 'variable' ) {
            $variations    = $product->get_available_variations();
            $variations_id = wp_list_pluck($variations, 'variation_id');

            foreach ( $variations_id as $variation ) {
                $payload = $this->process_product_data($variation);
            }
        }

        if ($this->settings['enable_extend_debug'] == 1 ) {
            Extend_Protection_Logger::extend_log_debug('DEBUG : Catalog Sync Payload :' . print_r($payload, true));
        }

        return $payload;
    }

    /*
    * For syncs, write all responses in a log file
    */
    public function log_syncs( $response, $payload, $batchnumber )
    {
        $this->check_directory();

        // Step 2 : generate a file based on timestamp and write into it
        $log_file = fopen($this->directory . '/' . 'sync-' . date('m-d-y') . '.log', 'a');
        fwrite($log_file, "\n" . 'Batch number : ' . $batchnumber);
        fwrite($log_file, "\n" . '----------------------------------------------------------------------------------------------------------------------------');
        fwrite($log_file, "\n" . 'Payload: ');
        fwrite($log_file, "\n" . '----------------------------------------------------------------------------------------------------------------------------');
        fwrite($log_file, print_r($payload, true));
        fwrite($log_file, "\n" . '----------------------------------------------------------------------------------------------------------------------------');
        fwrite($log_file, "\n" . 'Response: ');
        fwrite($log_file, print_r($response, true));
        fwrite($log_file, "\n" . '****************************************************************************************************************************');
        fclose($log_file);
    }

    public function log_sync_summary( $summary )
    {
        $this->check_directory();
        $sync_time_stamp = '---[' . date('m-d-y H:i:s') . " ]----------\n";

        // generate a file based on timestamp and write into it
        $log_file = fopen($this->directory . '/' . 'sync-summary-' . date('m-d-y') . '.log', 'a');
        fwrite($log_file, $sync_time_stamp);
        fwrite($log_file, print_r($summary, true));
        fwrite($log_file, "\n");
        fclose($log_file);
    }

    /*
    set last_run_sync to current date and time
    can be both an ajax call and a direct call by using the $schedule var
    */
    public function update_last_run_sync( $schedule = null )
    {
        if (! defined('DOING_AJAX') && ! $schedule ) {
            return;
        }

        $sync_options                             = get_option('extend_protection_for_woocommerce_catalog_sync_settings');
        $sync_time                                = time();
        $sync_options['extend_last_product_sync'] = $sync_time;

        update_option('extend_protection_for_woocommerce_catalog_sync_settings', $sync_options);
        Extend_Protection_Logger::extend_log_notice(
            "Catalog sync completed. 
        Please refer to the log in <a href='" . site_url() . 'wp-content/extend/sync/sync-' . date('m-d-y') . ".log'>wp-content/extend/sync/sync-" . date('m-d-y') . '.log</a>'
        );

        if (defined('DOING_AJAX') ) {
            wp_send_json_success(
                array(
                'time'          => date('Y-m-d h:i:s A', $sync_time),
                'sync_unixtime' => $sync_time,
                )
            );
        }

    }

    public function get_product_price( $product )
    {
        if ($this->settings['extend_use_special_price'] == '0' ) {
            $price = $product->get_price();
        } else {
            $price = $product->get_sale_price() <> '' ? $product->get_sale_price() : $product->get_price();
        }

        $price = $price <> '' ? $price : 0;

        return $price;
    }

    public function get_sku_or_id( $product )
    {
        if ($this->settings['extend_use_skus'] == 1 ) {
            // return sku if present otherwise return product ID
            $ref_id = $product->get_sku() ?? $product->get_ID();
        } else {
            $ref_id = $product->get_ID();
        }
        return $ref_id;
    }

    // Callback function to sync single products on update.
    function sync_products_callback( $post_id, $post )
    {
        // run only if settings allow for automatic update
        if ($this->settings['extend_sync_on_update'] == '1' ) {
            // Check if the post being updated is a woocommerce product.
            if ($post->post_type === 'product' ) {
                $this->extend_sync_one_product($post_id);
            }
        }
    }

    /*
    * Run the product sync for one single item
    */
    public function extend_sync_one_product( $id )
    {
        /*
        *  build WP Query to retrieve the product id
        */
        $args = array(
        'post_type'      => 'product',
        'p'              => $id,         // Specific product ID
        'post_status'    => 'publish',   // Ensure the product is published
        'posts_per_page' => 1,           // Limit to one result
        'meta_query'     => array(
        array(
        'key'     => '_virtual',
        'value'   => 'yes',
        'compare' => '!=',
        ),
        ),
        );

        $product_query = new WP_Query($args);
        if ($product_query->have_posts() ) {
            while ( $product_query->have_posts() ) {
                $product_query->the_post();
                $product_id = get_the_ID();

                // build the batch data from the product
                $batch_data[] = $this->process_product_data($product_id);
                $request_args = $this->buildRequest($batch_data);
                $response     = wp_remote_request($this->settings['api_host'] . '/stores/' . $this->settings['store_id'] . '/products?batch=true', $request_args);

                if ($this->settings['enable_extend_debug'] == 1 ) {
                    Extend_Protection_Logger::extend_log_debug('DEBUG response: ' . print_r($response, true));
                }

                if (is_wp_error($response) ) {
                    $error_message = $response->get_error_message();
                    Extend_Protection_Logger::extend_log_error('Product Sync : POST request failed: ' . $error_message);
                } else {
                    $response_code = wp_remote_retrieve_response_code($response);
                    if ($response_code === 201 ) {
                        // success
                        $data = json_decode(wp_remote_retrieve_body($response));
                        if (isset($data->added) && is_array($data->added) ) {
                               $batch_added_count = count($data->added);
                            if ($batch_added_count > 0 ) {
                                Extend_Protection_Logger::extend_log_notice('Single Product Sync : item ID ' . $product_id . ' added');
                            }
                        }

                        if (isset($data->updated) && is_array($data->updated) ) {
                            $batch_updated_count = count($data->updated);
                            if ($batch_updated_count > 0 ) {
                                Extend_Protection_Logger::extend_log_notice('Single Product Sync : item ID ' . $product_id . ' updated');
                            }
                        }

                        if (isset($data->errors) && is_array($data->errors) ) {
                            $batch_errors_count = count($data->errors);
                            if ($batch_errors_count > 0 ) {
                                Extend_Protection_Logger::extend_log_notice('Single Product Sync : item ID' . $product_id . ' returned an error');
                            }
                        }
                    } //end response 201
                    // restore the global post data after using WP_Query.
                    wp_reset_postdata();
                }//end if is_wp_error
            }//end while
            $this->log_syncs($response, $batch_data, '1');
        } else {
            Extend_Protection_Logger::extend_log_error('No recently updated products found to sync.');
        }
    }

    /*
    *  scheduled product sync
    */
    public function sync_products_cron_job()
    {
        // get calling function : debug_backtrace()[1]['function'] should return sync_product_cron_job;
        if ($this->settings['extend_automated_product_sync'] <> 'never' ) {
            Extend_Protection_Logger::extend_log_notice('Running Scheduled Product Sync on the schedule called : ' . $this->settings['extend_automated_product_sync']);

            $batch_size = $this->settings['extend_sync_batch'];
            $args       = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'meta_query'     => array(
            array(
            'key'     => '_virtual',
            'value'   => 'yes',
            'compare' => '!=',
            ),
            ),
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            );

            // dynamically build the date filter if relevant
            $args = $this->build_args_date_query($args);

            // run the query once  to retrieve the number of products to calculate total batches
            $productsTotal = new WP_Query($args);
            $totalCount    = $productsTotal->post_count;
            $batch_total   = ceil($totalCount / $batch_size);

            // run the query with the batch_size and offset
            for ( $page = 1; $page <= $batch_total; $page++ ) {
                $args = array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'meta_query'     => array(
                array(
                'key'     => '_virtual',
                'value'   => 'yes',
                'compare' => '!=',
                ),
                ),
                'posts_per_page' => $batch_size,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'paged'          => $page,
                );

                $product_query = new WP_Query($args);

                if ($product_query->have_posts() ) {
                    while ( $product_query->have_posts() ) {
                        $product_query->the_post();
                        $product_id = get_the_ID();

                        // Process each product here
                        $batch_data[] = $this->process_product_data($product_id);

                        // batch_data is the payload we send to extend
                        $request_args = $this->buildRequest($batch_data);
                        $response     = wp_remote_request($this->settings['api_host'] . '/stores/' . $this->settings['store_id'] . '/products?batch=true', $request_args);
                        if (is_wp_error($response) ) {
                            $error_message = $response->get_error_message();
                            Extend_Protection_Logger::extend_log_error(' Catalog Sync Batch #' . $page . ' : POST request failed: ' . $error_message);
                        } else {
                            $response_code = wp_remote_retrieve_response_code($response);
                            if ($response_code === 201 ) {
                                // success
                                $data = json_decode(wp_remote_retrieve_body($response));
                            } //201
                        }//if is_wp_error
                    }
                    // write to log summary from response
                    if ($data ) {
                        $this->process_response($data, $page);
                    }
                    $this->log_syncs($response, $batch_data, $page);
                }
                wp_reset_postdata();
            } //end for

            $this->update_last_run_sync(true);

        } else {
            // only write in the log if debug is on, to avoid crowding logs
            if ($this->settings['enable_extend_debug'] == 1 ) {
                Extend_Protection_Logger::extend_log_error('No recently updated products found to sync through schedule.');
            }
        }
    }

    /*
    * shared functions
    */
    private function buildRequest( $batch_data )
    {
        return array(
        'method'  => 'POST',
        'headers' => array(
        'Content-Type'          => 'application/json',
        'Accept'                => 'application/json; version=latest',
        'X-Extend-Access-Token' => $this->settings['api_key'],
        ),
        'body'    => json_encode($batch_data),
        );
    }

    private function build_args_date_query( $args )
    {
        // if there is a last sync date, adjust the filter
        if (strtolower($this->settings['extend_last_product_sync']) != 'never' && ! is_null($this->settings['extend_last_product_sync']) ) {
            $args['date_query'] = array(
            array(
            'after'     => date('Y-m-d h:i:s', strtolower($this->settings['extend_last_product_sync'])),
            'inclusive' => 'true',
            ),
            );
        }
        return $args;
    }

    /*
    *  check_directory manages the log sync directory: create if it doesn't exist and delete older logs
    */
    private function check_directory()
    {
        // Create the log directory if it doesn't exist.
        if (! file_exists($this->directory) ) {
            mkdir($this->directory, 0755, true);
        }

        // Step 1 : delete older log files (3 weeks)
        $files           = scandir($this->directory);
        $three_weeks_ago = strtotime('-3 weeks');

        // Loop through each file and check if it's a log file and older than 3 weeks.
        foreach ( $files as $file ) {
            if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'log' ) {
                $file_path = $this->directory . '/' . $file;

                // Check the file's modification time.
                if (filemtime($file_path) < $three_weeks_ago ) {
                    // Delete the file if it's older than 3 weeks.
                    unlink($file_path);
                }
            }
        }
    }

    /*
    * Process the response from the api and break down the number of items
    * updated, created or errored
    */
    private function process_response( $data, $batch_current = 0 )
    {
        if (isset($data->added) && is_array($data->added) ) {
            $batch_added_count = count($data->added);
            $added_item        = array();
            foreach ( $data->added as $added ) {
                $added_item[] = '[id: ' . $added->referenceId . ' / sku: ' . $added->identifiers->sku . ']';
            }
            switch ( $batch_added_count ) {
            case ( $batch_added_count > 1 ):
                $items_added = ' items added : ';
                break;

            case ( $batch_added_count == 1 ):
                $items_added = ' item added : ';
                break;

            default:
                $items_added = ' item added. ';
                break;
            }
            if ($batch_added_count > 0 ) {
                $added_summary = 'Catalog Sync Batch #' . $batch_current . ', ' . $batch_added_count . $items_added . "\n" . implode(', ', $added_item);
                $this->log_sync_summary($added_summary);
            }
        }

        if (isset($data->updated) && is_array($data->updated) ) {
            $batch_updated_count = count($data->updated);
            $updated_item        = array();
            foreach ( $data->updated as $updated ) {
                $updated_item[] = '[id: ' . $updated->referenceId . ' / sku: ' . $updated->identifiers->sku . ']';
            }
            switch ( $batch_updated_count ) {
            case ( $batch_updated_count > 1 ):
                $items_updated = ' items updated : ';
                break;

            case ( $batch_updated_count == 1 ):
                $items_updated = ' item updated : ';
                break;

            default:
                $items_updated = ' item updated. ';
                break;
            }
            if ($batch_updated_count > 0 ) {
                $updated_summary = 'Catalog Sync Batch #' . $batch_current . ', ' . $batch_updated_count . $items_updated . "\n" . implode(', ', $updated_item);
                $this->log_sync_summary($updated_summary);
            }
        }

        if (isset($data->errors) && is_array($data->errors) ) {
            $batch_errors_count = count($data->errors);
            $errors_item        = array();
            foreach ( $data->errors as $errors ) {
                $errors_item[] = '[id: ' . $errors->referenceId . '/ sku: ' . $errors->identifiers->sku . ']';
            }
            switch ( $batch_errors_count ) {
            case ( $batch_errors_count > 1 ):
                $items_errored = ' items errored : ';
                break;

            case ( $batch_errors_count == 1 ):
                $items_errored = ' item errored : ';
                break;

            default:
                $items_errored = ' item errored. ';
                break;
            }
            if ($batch_errors_count > 0 ) {
                $error_summary = 'Catalog Sync Batch #' . $batch_current . ', ' . $batch_errors_count . $items_errored . "\n" . implode(', ', $errors_item);
                Extend_Protection_Logger::extend_log_error('Catalog Sync Batch #' . $batch_current . ', ' . $batch_errors_count . $items_errored . implode(',', $errors_item));
                $this->log_sync_summary($error_summary);
            }
        }
    }
}
