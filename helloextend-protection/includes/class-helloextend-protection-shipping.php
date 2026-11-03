<?php

/**
 * Extend For WooCommerce Product Integration.
 *
 * @since   1.0.0
 * @package HelloExtend_Protection
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/admin
 */

// Prevent direct access to the file
if (! defined('ABSPATH')) {
    exit;
}

/**
 * The Shipping Protection functionality of the plugin.
 *
 * Adds helloextend-shipping-offer div to the checkout area
 * Enqueues the necessary JS
 * Renders Extend checkout shipping protection offers
 *
 * @package HelloExtend_Protection
 * @author  Extend, Inc.
 */

class HelloExtend_Protection_Shipping
{

    private const HELLOEXTEND_SP_LABEL          = 'Extend Shipping Protection';
    private const HELLOEXTEND_SP_PROTECTION_SKU = 'HELLOEXTEND_SP_SKU';
    
    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $helloextend_protection The ID of this plugin.
     */
    private string $helloextend_protection;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string $version The current version of this plugin.
     */
    private string $version;
    private array $settings;

    public function __construct()
    {

        $this->settings = HelloExtend_Protection_Global::helloextend_get_settings();
        $this->hooks();
    }

    /**
     * Initiate our hooks.
     *
     * @since 0.0.0
     */
    public function hooks()
    {

        // checkout offer element - default should be woocommerce_review_order_before_payment
        add_action( $this->settings['helloextend_sp_offer_location'], [$this, 'render'], 10, 2 );

        add_action( 'woocommerce_shipstation_export_order_xml', [$this, 'add_sku_to_shipstation'], 10, 1 );

        if (did_action('woocommerce_blocks_loaded')) {
            $this->extend_store_api_schema();
        } else {
            add_action('woocommerce_blocks_loaded', [$this, 'extend_store_api_schema']);
        }

        add_action('woocommerce_cart_calculate_fees', [$this, 'set_sp_fee']);
    }

    /**
     * Extend WooCommerce Store API to support shipping protection.
     * 
     * @since 1.3
     */
    /*
     * Public because hooks() also registers this as a woocommerce_blocks_loaded
     * callback. WordPress invokes callbacks from outside the class, so a private
     * method fatals there — and only the did_action() branch, which calls it
     * internally, would keep working.
     */
    public function extend_store_api_schema()
    {
        woocommerce_store_api_register_update_callback([
            'namespace' => 'helloextend/shipping-protection',
            'callback' => function ($data) {
                if (!isset($data['action'])) return;

                if ($data['action'] == 'add') {
                    // The whole payload is client-supplied, so 'quote' may be
                    // absent even when 'action' is not.
                    $this->add_sp_to_cart(isset($data['quote']) ? $data['quote'] : []);
                } else if ($data['action'] == 'remove') {
                    $this->remove_sp_from_cart();
                }
            }
        ]);
    }

    /**
     * This method adds to xml export an SKU tag in order to identify
     * Extend Protection to ShipStation integration
     *
     * @param  $order_xml
     * @return mixed
     */
    public function add_sku_to_shipstation($order_xml)
    {

        if ($order_xml instanceof DOMElement) {

            $items = $order_xml->getElementsByTagName('Items');

            foreach ($items as $itemNode) {
                foreach ($itemNode->getElementsByTagName('Item') as $item) {
                    foreach ($item->getElementsByTagName('Name') as $name) {
                        if (trim($name->nodeValue) === self::HELLOEXTEND_SP_LABEL) {
                            $this->xml_append($item, 'SKU', self::HELLOEXTEND_SP_PROTECTION_SKU);
                        }
                    }
                }
            }
        }

        return $order_xml;
    }

    public function render()
    {

        // add offer element
        $helloextend_enabled       = $this->settings['enable_helloextend'];
        $enable_helloextend_sp     = $this->settings['enable_helloextend_sp'];
        $helloextend_sp_add_sku    = $this->settings['helloextend_sp_add_sku'];
        $env                       = $this->settings['helloextend_environment'];
        $cart_items                = WC()->cart->get_cart();
        $ajax_url                  = admin_url('admin-ajax.php');
        $update_order_review_nonce = wp_create_nonce('update_order_review');

        $items = array();
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            if (! $product->is_virtual()) {
                $referenceId = $product->get_id();
                $categories = get_the_terms($cart_item['product_id'], 'product_cat');
                $category    = HelloExtend_Protection_Global::helloextend_get_first_valid_category(is_wp_error($categories) ? [] : (array) $categories);
                $items[]     = array(
                    'referenceId'   => $referenceId,
                    'quantity'      => $cart_item['quantity'],
                    'category'      => $category,
                    'purchasePrice' => (int) floatval($product->get_price() * 100),
                    'productName'   => $product->get_name(),
                );
            }
        }
        $items = json_encode($items);

        if ($this->settings['enable_helloextend_debug'] == 1) {
            // phpcs:disable WordPress.PHP.DevelopmentFunctions
            HelloExtend_Protection_Logger::helloextend_log_debug('DEBUG : Shipping Protection Cart Item Payload :' . print_r($items, true));
            // phpcs:enable
        }

        if ($enable_helloextend_sp == 1 && $helloextend_enabled == 1) {
            wp_enqueue_script('helloextend_script');
            wp_enqueue_script('helloextend_shipping_integration_script');
            wp_localize_script(
                'helloextend_shipping_integration_script',
                'ExtendShippingIntegration',
                compact('env', 'items', 'helloextend_enabled',  'enable_helloextend_sp', 'helloextend_sp_add_sku', 'ajax_url', 'update_order_review_nonce')
            );
            echo '<tr><td colspan="2"><div id="helloextend-shipping-offer" style="height: 120px;"></div></td></tr>';
        } else {
            // make sure to remove any SP session value
            WC()->session->set('shipping_fee_remove', true);
            WC()->session->set('shipping_fee', false);
            WC()->session->set('shipping_fee_value', null);
            WC()->session->set('shipping_quote_id', null);
        }
    }

    /**
     * Store API add SP to cart
     * 
     * @param $data SP quote data.
     * @since 1.3
     */
    public function add_sp_to_cart($quote)
    {
        if (!isset($quote['premium']) || !isset($quote['id'])) {
            HelloExtend_Protection_Logger::helloextend_log_error('SP data missing quote');
            return;
        }

        /*
         * The quote reaches us straight from the browser through the Store API,
         * so the premium is shopper-controlled until it is checked. An unchecked
         * value is spendable: in fee mode a negative premium becomes a negative
         * add_fee(), i.e. an arbitrary discount, and in SKU mode it sets the
         * price of the shared protection product for the whole store.
         */
        if (!is_numeric($quote['premium']) || (float) $quote['premium'] <= 0) {
            HelloExtend_Protection_Logger::helloextend_log_error('SP quote rejected, premium is not a positive number: ' . wp_json_encode($quote['premium']));
            return;
        }

        $quote['premium'] = (float) $quote['premium'];
        $quote['id']      = sanitize_text_field((string) $quote['id']);

        if ($quote['id'] === '') {
            HelloExtend_Protection_Logger::helloextend_log_error('SP quote rejected, quote id is empty');
            return;
        }

        if (helloextend_sp_add_as_sku()) {
            $amount = floatval($quote['premium'] / 100);
            $product_id = helloextend_get_or_create_shipping_protection_product($amount);

            if (!$product_id) {
                HelloExtend_Protection_Logger::helloextend_log_error('Could not create or retrieve shipping protection product');
                return;
            }

            $already_in_cart = count(
                array_filter(WC()->cart->get_cart(), function ($item) use ($product_id) {
                    return $item['product_id'] == $product_id;
                })
            ) > 0;

            if (!$already_in_cart) {
                $cart_item_key = WC()->cart->add_to_cart($product_id, 1);

                if (!$cart_item_key) {
                    HelloExtend_Protection_Logger::helloextend_log_error('Could not add SP to cart due to Cart error');
                    wp_die();
                }
            }
            WC()->cart->calculate_totals();
        }
        WC()->session->set('shipping_fee', true);
        WC()->session->set('shipping_fee_value', $quote['premium']);
        WC()->session->set('shipping_quote_id', $quote['id']);
    }

    /**
     * Store API remove SP
     * 
     * @since 1.3
     */
    public function remove_sp_from_cart()
    {
        WC()->session->set('shipping_fee_remove', true);
        WC()->session->set('shipping_fee', false);
        WC()->session->set('shipping_fee_value', null);
        WC()->session->set('shipping_quote_id', null);

        if (helloextend_sp_add_as_sku()) {
            helloextend_remove_shipping_protection_product_from_cart();
        }
        WC()->cart->calculate_totals();
    }

    /**
     * Set Shipping Protection fee amount
     * 
     * @since 1.3
     */
    public function set_sp_fee()
    {
        $shipping_fee = WC()->session->get('shipping_fee', false);
        $premium = WC()->session->get('shipping_fee_value', null);
        $remove_fee = WC()->session->get('shipping_fee_remove');

        $add_as_sku = helloextend_sp_add_as_sku();

        if ($remove_fee) {
            if (helloextend_sp_add_as_sku()) {
                helloextend_remove_shipping_protection_product_from_cart();
            } else {
                $fees = WC()->cart->get_fees();
                foreach ($fees as $key => $fee) {
                    if ($fee->name == HelloExtend_Protection_Shipping::HELLOEXTEND_SP_LABEL) {
                        unset($fees[$key]);
                    }
                }
                WC()->cart->fees_api()->set_fees($fees);
            }

            WC()->session->set('shipping_fee_remove', false);
        } else if ($shipping_fee == 1) {
            if (!helloextend_sp_add_as_sku()) {
                $amount = floatval($premium / 100);
                WC()->cart->add_fee(__('Extend Shipping Protection', 'helloextend-protection'), $amount);
            }
        }
    }
}
