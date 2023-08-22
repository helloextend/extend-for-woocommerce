<?php
/**
 * Extend for WooCommerce Global Class
 * @since 1.0.0
 * @package Extend_Protection
 * @author Extend, Inc.
 * @subpackage Extend_Protection/includes
 * @link https://extend.com
 */
class Extend_Protection_Global
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $extend_protection The ID of this plugin.
     */
    private $extend_protection;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Parent plugin class
     * @since 1.0.0
     * @var Extend_Protection
     *
     */
    protected $plugin = null;

    /**
     * Constructor
     * @since 1.0.0
     * @param Extend_Protection $plugin Main plugin object
     *
     */
    public function __construct($extend_protection, $version) {

        $this->extend_protection = $extend_protection;
        $this->version = $version;
        $this->hooks();
    }

    /**
     * Initiate our hooks
     * @since 1.0.0
     *
     */
    public function hooks()
    {
        // add to cart for users without permissions
        add_action('wp_ajax_nopriv_add_to_cart_extend', [$this, 'add_to_cart_extend'], 10);

        // add to cart for users with permissions
        add_action('wp_ajax_add_to_cart_extend', [$this, 'add_to_cart_extend'], 10);

        // get cart for users without permissions
        add_action('wp_ajax_nopriv_get_cart_extend', [$this, 'get_cart_extend'], 10);

        // get cart for users with permissions
        add_action('wp_ajax_get_cart_extend', [$this, 'get_cart_extend'], 10);

        //change mini cart item price for warranty items
        add_filter('woocommerce_cart_item_price', [$this, 'cart_item_price'], 10, 3);

        //change cart item names for warranty items
        add_filter('woocommerce_cart_item_name', [$this, 'cart_item_name'], 10, 3);

        //change order item names for warranty items
        add_filter('woocommerce_order_item_name', [$this, 'order_item_name'], 10, 3);

        //set product and term data
        add_filter('woocommerce_get_item_data', [$this, 'checkout_details'], 10, 2);

        //add properties to warranty products
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'order_item_meta'], 10, 3);

        //update price for warranty items
        add_action('woocommerce_before_calculate_totals', [$this, 'update_price']);

        // Initialize global ExtendWooCommerce
        add_action('wp_head', [$this, 'init_global_extend']);
    }

    /** Get Cart Extend
     * @since 1.0.0
     * @return void
     */
    public static function get_cart_extend() {
        $cart = WC()->cart->get_cart();
        echo json_encode($cart, JSON_PRETTY_PRINT);
        wp_die();
    }

    /**
     * Retrieves the Extend for WooCommerce settings.
     * @since 1.0.0
     * @return array The extended WooCommerce settings.
     */
    public static function get_extend_settings() {
        static $settings;

        $extend_protection_general_settings             = (array) get_option('extend_protection_for_woocommerce_general_settings');
        $extend_protection_product_protection_settings  = (array) get_option('extend_protection_for_woocommerce_product_protection_settings');
        $extend_protection_shipping_protection_settings = (array) get_option('extend_protection_for_woocommerce_shipping_protection_settings');

        $settings['enable_extend']              = array_key_exists('enable_extend', $extend_protection_product_protection_settings)
                                                ? $extend_protection_product_protection_settings['enable_extend'] : 0;

        $settings['extend_enable_cart_offers']  = array_key_exists('extend_enable_cart_offers', $extend_protection_product_protection_settings)
                                                ? $extend_protection_product_protection_settings['extend_enable_cart_offers'] : 0;

        $settings['extend_enable_cart_balancing'] = array_key_exists('extend_enable_cart_balancing', $extend_protection_product_protection_settings)
                                                ? $extend_protection_product_protection_settings['extend_enable_cart_balancing'] : 0;

        $settings['extend_enable_pdp_offers']   = array_key_exists('extend_enable_pdp_offers', $extend_protection_product_protection_settings)
                                                ? $extend_protection_product_protection_settings['extend_enable_pdp_offers'] : 0;

        $settings['extend_enable_modal_offers'] = array_key_exists('extend_enable_modal_offers', $extend_protection_product_protection_settings)
                                                ? $extend_protection_product_protection_settings['extend_enable_modal_offers'] : 0;

        $settings['extend_pdp_offer_location']  = array_key_exists('extend_pdp_offer_location', $extend_protection_product_protection_settings)
                                                ? $extend_protection_product_protection_settings['extend_pdp_offer_location'] : 'woocommerce_before_add_to_cart_button';

        $settings['extend_environment']         = array_key_exists('extend_environment', $extend_protection_general_settings)
                                                ? $extend_protection_general_settings['extend_environment'] : 'sandbox';


        $settings['enable_extend_debug']         = array_key_exists('enable_extend_debug', $extend_protection_general_settings)
                                                 ? $extend_protection_general_settings['enable_extend_debug'] : 0;

        if ($extend_protection_shipping_protection_settings){
            $settings['enable_extend_sp']       = array_key_exists('enable_extend_sp', $extend_protection_shipping_protection_settings)
                                                ? $extend_protection_shipping_protection_settings['enable_extend_sp'] : 0;

            $settings['extend_sp_offer_location'] = array_key_exists('extend_sp_offer_location', $extend_protection_shipping_protection_settings)
                                                ? $extend_protection_shipping_protection_settings['extend_sp_offer_location'] : 'woocommerce_review_order_after_shipping';
        }

        /* Set variables depending on environment */
        if ($settings['extend_environment'] == 'live') {
            $settings['store_id']   = array_key_exists('extend_live_store_id', $extend_protection_general_settings ) ?
                                      $extend_protection_general_settings['extend_live_store_id'] : '';
            $settings['api_host']   = 'https://api.helloextend.com';
            $settings['api_key']    = array_key_exists('extend_live_api_key', $extend_protection_general_settings ) ?
                                      $extend_protection_general_settings['extend_live_api_key'] : '';
        }
        else {
            $settings['store_id']   = array_key_exists('extend_sandbox_store_id', $extend_protection_general_settings) ?
                                      $extend_protection_general_settings['extend_sandbox_store_id'] : '';
            $settings['api_host']   = 'https://api-demo.helloextend.com';
            $settings['api_key']    = array_key_exists('extend_sandbox_api_key', $extend_protection_general_settings) ?
                                      $extend_protection_general_settings['extend_sandbox_api_key'] : '';
        }

        $settings['sdk_url'] = 'https://sdk.helloextend.com/extend-sdk-client/v1/extend-sdk-client.min.js';
        $settings['warranty_product_id'] = extend_product_protection_id();

        if (empty($settings['warranty_product_id'])) {
            Extend_Protection_Logger::extend_log_error("Error: Warranty product is not created.");
        }

        return $settings;
    }

    public static function add_to_cart_extend(){
        $warranty_product_id = wc_get_product_id_by_sku( 'extend-product-protection' );
        $quantity = $_REQUEST['quantity'];
        $extend_data = $_REQUEST['extendData'];

        if(!isset($warranty_product_id) || !isset($quantity) || !isset($extend_data)) {
            return;
        }

        WC()->cart->add_to_cart( $warranty_product_id, $quantity, 0, 0, ['extendData' => $extend_data]);

    }

    // update_price($cart_object)
    // @param $cart_object : WC_Cart, represents current cart object
    public function update_price($cart_object){
        $cart_items = $cart_object->cart_contents;

        if ( ! empty( $cart_items ) ) {

            foreach ( $cart_items as $key => $value ) {
                if(isset($value['extendData'])){
                    $value['data']->set_price( round($value['extendData']['price']/100, 2) );
                }

            }
        }
    }

    public function cart_item_price($price, $cart_item, $cart_item_key) {
        if(isset($cart_item['extendData'])) {
            $price = round($cart_item['extendData']['price']/100, 2);
            return wc_price($price);
        }
        return $price;
    }

    // cart_item_name($name, $cart_item, $cart_item_key)
    // @param $name : current items name
    // @param $cart_item : current cart item
    // @param $cart_item_key : unique key for cart item
    // @return $name or new title for warranties
    public function cart_item_name($name, $cart_item, $cart_item_key){

        if(isset($cart_item['extendData'])){
            $term = $cart_item['extendData']['term'];
            return "Extend Protection Plan - {$term} Months";
        }

        return $name;

    }

    // order_item_name($name, $cart_item, $cart_item_key)
    // @param $name : current items name
    // @param $cart_item : current cart item
    // @param $cart_item_key : unique key
    // @return $name or Extend Protection Plan for warranties
    public function order_item_name($name, $cart_item, $cart_item_key){

        $meta = $cart_item->get_meta('_extend_data');
        if($meta){
            return $meta['title'];
        }

        return $name;

    }

    // order_item_meta($item, $cart_item_key, $cart_item)
    // @param $item : WC_Order_Item, represents order lineItem
    // @param $cart_item_key : cart item unique key
    // @param $cart_item : current cart item
    // This function transfers data from cart items, to order items
    public function order_item_meta($item, $cart_item_key, $cart_item ){
        if(isset($cart_item['extendData'])){
            $item->add_meta_data('_extend_data', $cart_item['extendData']);

            $covered_id     = $cart_item['extendData']['covered_product_id'];
            $term           = $cart_item['extendData']['term'];
            $title          = $cart_item['extendData']['title'];
            $covered        = wc_get_product($covered_id);
            $sku            = $cart_item['extendData']['planId'];
            $covered_title  = $covered->get_title();

            $item->add_meta_data('Warranty', $title);
            $item->add_meta_data('Warranty Term', $term . ' Months');
            $item->add_meta_data('Plan Id', $sku);
            $item->add_meta_data('Covered Product', $covered_title);

        }
    }

    // checkout_details($data, $cart_item)
    // @param $data : order item data
    // @param $cart_item : current cart item
    // @return $data : returns modified item data
    public function checkout_details($data, $cart_item){

        if(!is_cart() && !is_checkout()){
            return $data;
        }

        if(isset($cart_item['extendData'])){
            $covered_id     = $cart_item['extendData']['covered_product_id'];
            $term           = $cart_item['extendData']['term'];
            $covered        = wc_get_product($covered_id);
            $sku            = $cart_item['extendData']['planId'];
            $covered_title  = $covered->get_title();
            $data[] = [
                'key'=>'Product',
                'value'=>$covered_title
            ];
            $data[] = [
                'key'=>'Term',
                'value'=>$term . ' Months'
            ];

        }

        return $data;

    }

    public function init_global_extend() {
        if ( is_admin() ) { return; }

        $settings       = self::get_extend_settings();
        $environment    = $settings['extend_environment'];
        $store_id       = $settings['store_id'];
        $environment    = ($environment == 'live') ? $environment : 'demo';
        $extend_enabled = array_key_exists('enable_extend', $settings) ? $settings['enable_extend'] : 0;
        $ajaxurl        = admin_url('admin-ajax.php');


        if ($store_id && ($extend_enabled === '1')){
            wp_enqueue_script('extend_script');
            wp_enqueue_script('extend_global_script');
            wp_localize_script('extend_global_script', 'ExtendWooCommerce', compact('store_id' , 'ajaxurl', 'environment'));
        }
        else{
            Extend_Protection_Logger::extend_log_error('Store Id missing or Extend Product Protection is disabled');
        }
    }
}