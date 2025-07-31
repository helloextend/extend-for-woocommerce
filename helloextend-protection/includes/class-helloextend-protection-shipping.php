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
if (! defined('ABSPATH') ) {
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

    const HELLOEXTEND_SP_LABEL          = 'Extend Shipping Protection';
    const HELLOEXTEND_SP_PROTECTION_SKU = 'HELLOEXTEND_SP_SKU';

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
        add_action($this->settings['helloextend_sp_offer_location'], [ $this, 'shipping_protection_block' ], 10, 2);

        add_action('woocommerce_shipstation_export_order_xml', [ $this, 'helloextend_sp_add_sku_to_shipstation' ], 10, 1);

    }

    /**
     * This method adds to xml export an SKU tag in order to identify
     * Extend Protection to ShipStation integration
     *
     * @param  $order_xml
     * @return mixed
     */
    public function helloextend_sp_add_sku_to_shipstation( $order_xml )
    {

        if ($order_xml instanceof DOMElement ) {

            $items = $order_xml->getElementsByTagName('Items');

            foreach ( $items as $itemNode ) {
                foreach ( $itemNode->getElementsByTagName('Item') as $item ) {
                    foreach ( $item->getElementsByTagName('Name') as $name ) {
                        if (trim($name->nodeValue) === self::HELLOEXTEND_SP_LABEL ) {
                            $this->xml_append($item, 'SKU', self::HELLOEXTEND_SP_PROTECTION_SKU);
                        }
                    }
                }
            }
        }

        return $order_xml;
    }

    // echos the offer element to the checkout page
    public function shipping_protection_block()
    {

        // add offer element
        $enable_helloextend_sp          = $this->settings['enable_helloextend_sp'];
        $helloextend_sp_add_sku        = $this->settings['helloextend_sp_add_sku'];
        $env                       = $this->settings['helloextend_environment'];
        $cart_items                = WC()->cart->get_cart();
        $ajax_url                  = admin_url('admin-ajax.php');
        $update_order_review_nonce = wp_create_nonce('update_order_review');

        $items = array();
        foreach ( $cart_items as $cart_item_key => $cart_item ) {
            $product = $cart_item['data'];
            if (! $product->is_virtual() ) {
                $referenceId = $product->get_id();
                $items[]     = array(
                 'referenceId'   => $referenceId,
                 'quantity'      => $cart_item['quantity'],
                 'category'      => get_the_terms($product->get_id(), 'product_cat')[0]->name,
                 'purchasePrice' => (int) floatval($product->get_price() * 100),
                 'productName'   => $product->get_name(),
                );
            }
        }
        $items = json_encode($items);

        if ($this->settings['enable_helloextend_debug'] == 1 ) {
	        // phpcs:disable WordPress.PHP.DevelopmentFunctions
	        HelloExtend_Protection_Logger::helloextend_log_debug('DEBUG : Shipping Protection Cart Item Payload :' . print_r($items, true));
	        // phpcs:enable
        }

        if ($enable_helloextend_sp == 1 ) {
            wp_enqueue_script('helloextend_script');
            wp_enqueue_script('helloextend_shipping_integration_script');
            wp_localize_script(
                'helloextend_shipping_integration_script',
                'ExtendShippingIntegration',
                compact('env', 'items', 'enable_helloextend_sp', 'helloextend_sp_add_sku', 'ajax_url', 'update_order_review_nonce')
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
}
