<?php

/**
 * Extend for WooCommerce Global Class
 *
 * @since      1.0.0
 * @package    HelloExtend_Protection
 * @author     Extend, Inc.
 * @subpackage HelloExtend_Protection/includes
 * @link       https://extend.com
 */

// Prevent direct access to the file
if (! defined('ABSPATH') ) {
    exit;
}

class HelloExtend_Protection_Global
{

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $helloextend_protection The ID of this plugin.
     */
    private $helloextend_protection;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $version The current version of this plugin.
     */
    private $version;

    /**
     * Parent plugin class
     *
     * @since 1.0.0
     * @var   HelloExtend_Protection
     */
    protected $plugin = null;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param HelloExtend_Protection $plugin Main plugin object
     */
    public function __construct($helloextend_protection, $version)
    {

        $this->helloextend_protection = $helloextend_protection;
        $this->version           = $version;
        $this->hooks();
    }

    /**
     * Initiate our hooks
     *
     * @since 1.0.0
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

        // change mini cart item price for warranty items
        add_filter('woocommerce_cart_item_price', [$this, 'cart_item_price'], 10, 3);

        // change cart item names for warranty items
        add_filter('woocommerce_cart_item_name', [$this, 'cart_item_name'], 10, 3);

        // change order item names for warranty items
        add_filter('woocommerce_order_item_name', [$this, 'order_item_name'], 10, 3);

        // set product and term data
        add_filter('woocommerce_get_item_data', [$this, 'checkout_details'], 10, 2);

        // add properties to warranty products
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'order_item_meta'], 10, 3);

        // update price for warranty items
        add_action('woocommerce_before_calculate_totals', [$this, 'update_price']);

        // Initialize global ExtendWooCommerce
        add_action('wp_head', [$this, 'init_global_extend']);
    }

    /**
     * Get Cart Extend
     *
     * @since  1.0.0
     * @return void
     */
    public static function get_cart_extend()
    {
        $cart     = WC()->cart->get_cart();
        $settings = self::get_extend_settings();

        foreach ($cart as $cart_item_key => $cart_item) {

            // Retrieve WC_Product object from the product-id:
            $_woo_product = wc_get_product($cart_item['product_id']);

            // retrieve id or sku based on settings, and default to id if sku is empty
            $sku         = $_woo_product->get_sku() <> '' ? $_woo_product->get_sku() : $cart_item['product_id'];
            $referenceId = $settings['extend_use_skus'] ? $sku : $cart_item['product_id'];

            // add sku to cart item and label it referenceId
            $cart[$cart_item_key]['referenceId']  = $referenceId;
            $cart[$cart_item_key]['product_name'] = $_woo_product->get_title();
        }

        echo wp_json_encode($cart, JSON_PRETTY_PRINT);
        wp_die();
    }

    /**
     * Retrieves the Extend for WooCommerce settings.
     *
     * @since  1.0.0
     * @return array The extended WooCommerce settings.
     */
    public static function get_extend_settings()
    {
        static $settings;

        $helloextend_protection_general_settings             = (array) get_option('helloextend_protection_for_woocommerce_general_settings');
        $helloextend_protection_product_protection_settings  = (array) get_option('helloextend_protection_for_woocommerce_product_protection_settings');
        $helloextend_protection_shipping_protection_settings = (array) get_option('helloextend_protection_for_woocommerce_shipping_protection_settings');
        $helloextend_protection_catalog_sync_settings        = (array) get_option('helloextend_protection_for_woocommerce_catalog_sync_settings');

        $settings['enable_extend'] = array_key_exists('enable_extend', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['enable_extend'] : 0;

        $settings['extend_enable_cart_offers'] = array_key_exists('extend_enable_cart_offers', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['extend_enable_cart_offers'] : 0;

        $settings['extend_enable_cart_balancing'] = array_key_exists('extend_enable_cart_balancing', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['extend_enable_cart_balancing'] : 0;

        $settings['extend_enable_pdp_offers'] = array_key_exists('extend_enable_pdp_offers', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['extend_enable_pdp_offers'] : 0;

        $settings['extend_enable_modal_offers'] = array_key_exists('extend_enable_modal_offers', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['extend_enable_modal_offers'] : 0;

        $settings['extend_pdp_offer_location'] = array_key_exists('extend_pdp_offer_location', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['extend_pdp_offer_location']
            : 'woocommerce_before_add_to_cart_button';

        $settings['extend_pdp_offer_location_other'] = array_key_exists('extend_pdp_offer_location_other', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['extend_pdp_offer_location_other'] : '';

        $settings['extend_atc_button_selector'] = array_key_exists('extend_atc_button_selector', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['extend_atc_button_selector'] : 'button.single_add_to_cart_button';

        // update pdp offer location if "other" is selected otherwise default
        if ($settings['extend_pdp_offer_location'] == 'other' && $settings['extend_pdp_offer_location_other'] !== '') {
            $settings['extend_pdp_offer_location'] = $settings['extend_pdp_offer_location_other'];
        } else {
            $settings['extend_pdp_offer_location'] = 'woocommerce_before_add_to_cart_button';
        }

        /* Contract Creation Settings */
        $settings['extend_product_protection_contract_create'] = array_key_exists('extend_product_protection_contract_create', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['extend_product_protection_contract_create'] : 0;

        $settings['extend_product_protection_contract_create_event'] = array_key_exists('extend_product_protection_contract_create_event', $helloextend_protection_product_protection_settings)
            ? $helloextend_protection_product_protection_settings['extend_product_protection_contract_create_event'] : 'Fulfillment';

        $settings['extend_environment'] = array_key_exists('extend_environment', $helloextend_protection_general_settings)
            ? $helloextend_protection_general_settings['extend_environment'] : 'sandbox';

        $settings['enable_extend_debug'] = array_key_exists('enable_extend_debug', $helloextend_protection_general_settings)
            ? $helloextend_protection_general_settings['enable_extend_debug'] : 0;

        /* shipping protection */
        if ($helloextend_protection_shipping_protection_settings) {
            $settings['enable_extend_sp'] = array_key_exists('enable_extend_sp', $helloextend_protection_shipping_protection_settings)
                ? $helloextend_protection_shipping_protection_settings['enable_extend_sp'] : 0;

            $settings['extend_sp_offer_location'] = array_key_exists('extend_sp_offer_location', $helloextend_protection_shipping_protection_settings)
                ? $helloextend_protection_shipping_protection_settings['extend_sp_offer_location']
                : 'woocommerce_review_order_after_shipping';

            $settings['extend_sp_offer_location_other'] = array_key_exists('extend_sp_offer_location_other', $helloextend_protection_shipping_protection_settings)
                ? $helloextend_protection_shipping_protection_settings['extend_sp_offer_location_other'] : '';

            // update sp offer location if "other" is selected otherwise default
            if ($settings['extend_sp_offer_location'] == 'other' && $settings['extend_sp_offer_location_other'] !== '') {
                $settings['extend_sp_offer_location'] = $settings['extend_sp_offer_location_other'];
            } else {
                $settings['extend_sp_offer_location'] = 'woocommerce_review_order_before_payment';
            }
        }

        /* Set variables depending on environment */
        if ($settings['extend_environment'] == 'live') {
            $settings['store_id'] = array_key_exists('extend_live_store_id', $helloextend_protection_general_settings)
                ? $helloextend_protection_general_settings['extend_live_store_id'] : '';

            $settings['api_host'] = 'https://api.helloextend.com';

            $settings['client_secret'] = array_key_exists('extend_live_client_secret', $helloextend_protection_general_settings)
                ? $helloextend_protection_general_settings['extend_live_client_secret'] : '';

            // Client ID
            $settings['client_id'] = array_key_exists('extend_live_client_id', $helloextend_protection_general_settings)
                ? $helloextend_protection_general_settings['extend_live_client_id'] : '';

            // Create token variable
            $settings['token'] = get_option('extend_live_token') ? get_option('extend_live_token') : '';

            // Create token date variable
            $settings['token_date'] = get_option('extend_live_token_date') ? get_option('extend_live_token_date') : '';
        } else {
            $settings['store_id'] = array_key_exists('extend_sandbox_store_id', $helloextend_protection_general_settings)
                ? $helloextend_protection_general_settings['extend_sandbox_store_id'] : '';

            $settings['api_host'] = 'https://api-demo.helloextend.com';

            $settings['client_secret'] = array_key_exists('extend_sandbox_client_secret', $helloextend_protection_general_settings)
                ? $helloextend_protection_general_settings['extend_sandbox_client_secret'] : '';

            $settings['client_id'] = array_key_exists('extend_sandbox_client_id', $helloextend_protection_general_settings)
                ? $helloextend_protection_general_settings['extend_sandbox_client_id'] : '';

            // Create token variable
            $settings['token'] = get_option('extend_sandbox_token') ? get_option('extend_sandbox_token') : '';

            // Create token date variable
            $settings['token_date'] = get_option('extend_sandbox_token_date') ? get_option('extend_sandbox_token_date') : '';
        }

        $settings['sdk_url']             = 'https://sdk.helloextend.com/extend-sdk-client/v1/extend-sdk-client.min.js';

        $settings['warranty_product_id'] = array_key_exists('warranty_product_id', $settings)
            ? $settings['warranty_product_id'] : extend_product_protection_id();

        if (empty($settings['warranty_product_id'])) {
            HelloExtend_Protection_Logger::extend_log_error('Error: Warranty product is not created.');
        }

        /* catalog sync settings */
        $settings['extend_use_skus'] = array_key_exists('extend_use_skus', $helloextend_protection_catalog_sync_settings)
            ? $helloextend_protection_catalog_sync_settings['extend_use_skus'] : 0;

        $settings['extend_automated_product_sync'] = array_key_exists('extend_automated_product_sync', $helloextend_protection_catalog_sync_settings)
            ? $helloextend_protection_catalog_sync_settings['extend_automated_product_sync'] : 0;

        $settings['extend_last_product_sync'] = array_key_exists('extend_last_product_sync', $helloextend_protection_catalog_sync_settings)
            ? $helloextend_protection_catalog_sync_settings['extend_last_product_sync'] : null;

        $settings['extend_use_special_price'] = array_key_exists('extend_use_special_price', $helloextend_protection_catalog_sync_settings)
            ? $helloextend_protection_catalog_sync_settings['extend_use_special_price'] : 0;

        $settings['extend_sync_batch'] = array_key_exists('extend_sync_batch', $helloextend_protection_catalog_sync_settings)
            ? $helloextend_protection_catalog_sync_settings['extend_sync_batch'] : 100;

        $settings['extend_sync_on_update'] = array_key_exists('extend_sync_on_update', $helloextend_protection_catalog_sync_settings)
            ? $helloextend_protection_catalog_sync_settings['extend_sync_on_update'] : 0;

        return $settings;
    }

    public static function add_to_cart_extend()
    {
        $warranty_product_id = wc_get_product_id_by_sku('extend-product-protection');
        $quantity            = sanitize_key($_REQUEST['quantity']);
        $extend_data         = $_REQUEST['extendData'];

        if (!isset($warranty_product_id) || !isset($quantity) || !isset($extend_data)) {
            return;
        }

        WC()->cart->add_to_cart($warranty_product_id, $quantity, 0, 0, ['extendData' => $extend_data]);
    }

    // update_price($cart_object)
    // @param $cart_object : WC_Cart, represents current cart object
    public function update_price($cart_object)
    {
        $cart_items = $cart_object->cart_contents;

        if (!empty($cart_items)) {

            foreach ($cart_items as $key => $value) {
                if (isset($value['extendData']) && !empty($value['extendData'])) {
                    $value['data']->set_price(round($value['extendData']['price'] / 100, 2));
                }
            }
        }
    }

    public function cart_item_price($price, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['extendData']) && !empty($cart_item['extendData'])) {
            $price = round($cart_item['extendData']['price'] / 100, 2);
            return wc_price($price);
        }
        return $price;
    }

    // cart_item_name($name, $cart_item, $cart_item_key)
    // @param $name : current items name
    // @param $cart_item : current cart item
    // @param $cart_item_key : unique key for cart item
    // @return $name or new title for warranties
    public function cart_item_name($name, $cart_item, $cart_item_key)
    {

        if (isset($cart_item['extendData']) && !empty($cart_item['extendData'])) {
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
    public function order_item_name($name, $cart_item, $cart_item_key)
    {

        $meta = $cart_item->get_meta('_extend_data');
        if ($meta) {
            return $meta['title'];
        }

        return $name;
    }

    // order_item_meta($item, $cart_item_key, $cart_item)
    // @param $item : WC_Order_Item, represents order lineItem
    // @param $cart_item_key : cart item unique key
    // @param $cart_item : current cart item
    // This function transfers data from cart items, to order items
    public function order_item_meta($item, $cart_item_key, $cart_item)
    {
        if (isset($cart_item['extendData']) &&  !empty($cart_item['extendData'])) {
            $item->add_meta_data('_extend_data', $cart_item['extendData']);

            $covered_id = $cart_item['extendData']['covered_product_id'];
            $term       = $cart_item['extendData']['term'];
            $title      = $cart_item['extendData']['title'];
            // $covered        = self::extend_get_product($covered_id);
            $covered       = wc_get_product($covered_id);
            $sku           = $cart_item['extendData']['planId'];
            $covered_title = $covered->get_title();

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
    public function checkout_details($data, $cart_item)
    {

        if (!is_cart() && !is_checkout()) {
            return $data;
        }

        if (isset($cart_item['extendData']) && !empty($cart_item['extendData'])) {
            $covered_id = $cart_item['extendData']['covered_product_id'];
            $term       = $cart_item['extendData']['term'];
            // $covered        = self::extend_get_product($covered_id);
            $covered       = wc_get_product($covered_id);
            $sku           = $cart_item['extendData']['planId'];
            $covered_title = $covered->get_title();
            $data[]        = [
                'key'   => 'Product',
                'value' => $covered_title,
            ];
            $data[]        = [
                'key'   => 'Term',
                'value' => $term . ' Months',
            ];
        }

        return $data;
    }

    public function init_global_extend()
    {
        if (is_admin()) {
            return;
        }

        $settings       = self::get_extend_settings();
        $environment    = $settings['extend_environment'];
        $store_id       = $settings['store_id'];
        $environment    = ($environment == 'live') ? $environment : 'demo';
        $extend_enabled = array_key_exists('enable_extend', $settings) ? $settings['enable_extend'] : 0;
        $ajaxurl        = admin_url('admin-ajax.php');

        if ($store_id && ($extend_enabled === '1')) {
            wp_enqueue_script('extend_script');
            wp_enqueue_script('extend_global_script');
            wp_localize_script('extend_global_script', 'ExtendWooCommerce', compact('store_id', 'ajaxurl', 'environment'));
        } else {
            HelloExtend_Protection_Logger::extend_log_error('Store Id missing or Extend Product Protection is disabled');
        }
    }

    /*
    *  retrieve product based on id, depending on if identifier is sku or id
    */
    public function extend_get_product($product_identifier)
    {
        if (is_int($product_identifier)) {
            $get_product = wc_get_product($product_identifier);
        } else {
            $get_product = wc_get_product(wc_get_product_id_by_sku($product_identifier));
        }
        return $get_product;
    }

    /**
     * Get Oauth 2 token using Client ID and Client Secret
     * If token timestamp is over 3 hours, generate a new token
     *
     * @since 1.0.0
     */
    public static function get_extend_token()
    {
        $settings = self::get_extend_settings();
        $client_id      = $settings['client_id'];
        $client_secret  = $settings['client_secret'];
        $token          = $settings['token'];
        $time           = $settings['token_date'];

        if ($token && ($time && (time() - $time  < 10800))) {
            return $token;
        } else {
            $url = $settings['api_host'] . '/auth/oauth/token';
            $args = array(
                'body'    => json_encode(
                    array(
                        'grant_type'    => 'client_credentials',
                        'client_id'     => $client_id,
                        'client_secret' => $client_secret,
                        'client_assertion' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer'
                    )
                ),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json; version=latest'
                ),
                'timeout' => 15,
            );

            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                HelloExtend_Protection_Logger::extend_log_error('Error retrieving token: ' . $response->get_error_message());
                $token = null;
            } else {
                $response = json_decode($response['body']);

                if ($response->access_token) {
                    // switch settings environments
                    if ($settings['extend_environment'] == 'live') {
                        update_option('extend_live_token', $response->access_token);
                        update_option('extend_live_token_date', time());
                        $token = $response->access_token;
                    } else {
                        update_option('extend_sandbox_token', $response->access_token);
                        update_option('extend_sandbox_token_date', time());
                        $token = $response->access_token;
                    }
                } else {
                    $token = null;
                }
            }
        }
        return $token;
    }
}
