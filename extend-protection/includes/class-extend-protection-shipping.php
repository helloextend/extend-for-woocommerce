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
 * The Shipping Protection functionality of the plugin.
 *
 * Adds extend-shipping-offer div to the checkout area
 * Enqueues the necessary JS
 * Renders Extend checkout shipping protection offers
 * @package    Extend_Protection
 * @author     Extend, Inc.
 */

class Extend_Protection_Shipping {
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

    public function __construct() {

        $this->settings = Extend_Protection_Global::get_extend_settings();
        $this->hooks();
    }

    /**
     * Initiate our hooks.
     *
     * @since  0.0.0
     */
    public function hooks() {

        //checkout offer element - default should be woocommerce_review_order_before_payment
        add_action($this->settings['extend_sp_offer_location'], [$this, 'shipping_protection_block'], 10, 2);

    }

    // echos the offer element to the checkout page
    public function shipping_protection_block()
    {

        //  add offer element
        $enable_extend_sp   = $this->settings['enable_extend_sp'] ;
        $env                = $this->settings['extend_environment'];
        $cart_items         = WC()->cart->get_cart();
        $ajax_url           = admin_url( 'admin-ajax.php' );
        $update_order_review_nonce = wp_create_nonce('update_order_review');

        $items = array();
        foreach ( $cart_items as $cart_item_key => $cart_item ) {
            $product = $cart_item['data'];
            if (!$product->is_virtual()) {
                $items[] = array(
                    'referenceId'   => $product->get_sku() ?? $product->get_id(),
                    'quantity'      => $cart_item['quantity'],
                    'purchasePrice' => ($product->get_price() * $cart_item['quantity'])*100,
                    'productName'   => $product->get_name(),
                );
            }
        }
        $items = json_encode($items);

        if ($this->settings['enable_extend_debug'] == 1){
            Extend_Protection_Logger::extend_log_debug('DEBUG : Shipping Protection Cart Item Payload :'. print_r($items, true));
        }

        if($enable_extend_sp == 1 ){
            wp_enqueue_script('extend_script');
            wp_enqueue_script('extend_shipping_integration_script');
            wp_localize_script('extend_shipping_integration_script', 'ExtendShippingIntegration',
                    compact( 'env', 'items', 'enable_extend_sp', 'ajax_url', 'update_order_review_nonce'));
            echo '<div id="extend-shipping-offer"></div>';
        }else
        {
          //make sure to remove any SP session value
            WC()->session->set('shipping_fee_remove',   true);
            WC()->session->set('shipping_fee',          false);
            WC()->session->set('shipping_fee_value',    null);
            WC()->session->set('shipping_quote_id',    null);
        }
    }
}