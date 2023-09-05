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

/**
 * The Product Sync functionality of the plugin.
 *
 * Allows syncing of your catalog to Extend's mapping database
 * to determine proper offers per item. Functionality from the admin.
 * @package    Extend_Protection
 * @author     Extend, Inc.
 */

class Extend_Protection_Sync {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $extend_protection The ID of this plugin.
     */
    private string $extend_protection;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private string $version;
    private array $settings;

    public function __construct($extend_protection, $version) {
        $this->extend_protection    = $extend_protection;
        $this->version              = $version;
        $this->settings             = Extend_Protection_Global::get_extend_settings();
        $this->hooks();
    }

    /**
     * Initiate our hooks.
     *
     * @since  0.0.0
     */
    public function hooks() {

       /* catalog sync admin events */
        add_action('wp_ajax_extend_catalog_sync_reset',         [$this, 'extend_catalog_sync_reset'], 10);
        add_action('wp_ajax_nopriv_extend_catalog_sync_reset',  [$this, 'extend_catalog_sync_reset'], 10);
        add_action('wp_ajax_extend_catalog_sync_run',           [$this, 'extend_catalog_sync_run'], 10);
        add_action('wp_ajax_nopriv_extend_catalog_sync_run',    [$this, 'extend_catalog_sync_run'], 10);
        add_action('wp_ajax_update_last_run_sync',              [$this, 'update_last_run_sync'], 10);
        add_action('wp_ajax_nopriv_update_last_run_sync',       [$this, 'update_last_run_sync'], 10);
    }


    public function extend_catalog_sync_reset(){
        if (  !defined( 'DOING_AJAX' ) )  return;

        $sync_options                               = get_option('extend_protection_for_woocommerce_catalog_sync_settings');
        $sync_options['extend_last_product_sync']   = null ;

        update_option('extend_protection_for_woocommerce_catalog_sync_settings', $sync_options);
        wp_die();
    }

    public function extend_catalog_sync_run(){
        if (  !defined( 'DOING_AJAX' ) )  return;
        check_ajax_referer('extend_sync_nonce', 'nonce');

        /*
        $this->settings['extend_last_product_sync'];
        $this->settings['extend_use_special_price'];
        $this->settings['extend_use_skus'];
        */

        $args = array(
            'post_type' => 'product',
            'meta_query' => array(
                 array(
                     'key'      => '_virtual',
                     'value'    => 'yes',
                     'compare'  => '!='
                 )
             ),
            'posts_per_page' => -1,

            //TODO: fix order
            'order_by'      => 'post_title',
            'order'         => 'ASC'
        );

        //if there is a last sync date, adjust the filter
        if (strtolower($this->settings['extend_last_product_sync']) != 'never' && !is_null($this->settings['extend_last_product_sync'])){
            //add the date updated filter
            $args['meta_query'][] = array(
                'key'           => 'post_modified',
                'value'         => date('Y-m-d h:i:s', strtolower($this->settings['extend_last_product_sync'])),
                'compare'       => '>=',
            );
        }

        //$product_ids            = get_posts($args);
        $products = new WP_Query( $args );
        //$product_data           = array();
        $batch_number = 0;
        $batch_product = 0;
        $product_data_batches   = array();
        if ( $products->have_posts() ) {
            while ($products->have_posts()) {
                $products->the_post();
                $batch_data[]           = $this->process_product_data($product_id);
                $batch_product +=1 ;
                // Process the product data
                // ...
                if ($batch_product == 200){
                    $this->send_sync_success($batch_number);
                    $batch_number += 1;
                }
                // Send JSON success response for each batch completed
                wp_send_json_success( $batch_number, $batch_data );
            }//end while
        }//end if

//        foreach ($product_ids as $product_id) {
//            //$product_data[]           = $this->process_product_data($product_id);
//            $product_data_batches[]     = $this->process_product_data($product_id);
//        }

        Extend_Protection_Logger::extend_log_debug(print_r($product_data_batches, true));
        // Send each batch of product data as it is processed
        foreach ($product_data_batches as $batch) {
            wp_send_json_success($batch);
        }

        //wp_send_json_success($product_data);
    }

    function send_sync_success($batch_number, $batch_data){

    }

    function process_product_data($product_id) {
        $product    = wc_get_product($product_id);
        return array(
            'name'  => $product->get_name(),
            'id'    => $product_id,
            'sku'   => $product->get_sku()
        );
    }

    /*
      set last_run_sync to current date and time
    */
    public function update_last_run_sync(){
        if (  !defined( 'DOING_AJAX' ) )  return;

        $sync_options                               = get_option('extend_protection_for_woocommerce_catalog_sync_settings');
        $sync_time                                  = time();
        $sync_options['extend_last_product_sync']   = $sync_time ;

        update_option('extend_protection_for_woocommerce_catalog_sync_settings', $sync_options);
        wp_send_json_success(array( 'time'=>date('Y-m-d h:i:s A',$sync_time), 'sync_unixtime' => $sync_time));
    }
}