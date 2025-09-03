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

/**
 * The Cart Offer functionality of the plugin.
 *
 * Adds .helloextend-cart-offer div to each line item in the cart
 * Enqueues the necessary JS
 * Renders Extend cart offers
 *
 * @package HelloExtend_Protection
 * @author  Extend, Inc.
 */

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

class HelloExtend_Protection_Cart_Offer
{
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

    protected string $warranty_product_id;
    private array $settings;

    public function __construct()
    {
        $this->hooks();
        $this->settings = HelloExtend_Protection_Global::helloextend_get_settings();
    }

    /**
     * Initiate our hooks.
     *
     * @since 0.0.0
     */
    public function hooks()
    {

        // after cart add cart offers
        add_action('woocommerce_after_cart', [ $this, 'cart_offers' ]);

        // after cart item name add offer element
        add_action('woocommerce_after_cart_item_name', [ $this, 'after_cart_item_name' ], 10, 2);

        // run normalization on check
        add_action('woocommerce_check_cart_items', [ $this, 'normalize_cart' ]);

    }

    private function is_item_helloextend($item)
    {
        $warranty_product_id = $this->settings['warranty_product_id'];
        return $item['product_id'] == $warranty_product_id && isset($item['extendData']) && !empty($item['extendData']);
    }

    private function is_lead($item)
    {
        return $this->is_item_helloextend($item) && isset($item['extendData']['leadToken']) && isset($item['extendData']['leadQuantity']);
    }

    private function is_warranty($item)
    {
        return $this->is_item_helloextend($item) && !isset($item['extendData']['leadToken']) && !isset($item['extendData']['leadQuantity']);
    }

    private function get_product_id($line)
    {
        if ($this->is_item_helloextend($line)) {
            return $line['extendData']['covered_product_id'];
        } else {
            return $line['variation_id'] > 0 ? $line['variation_id'] : $line['product_id'];
        }
    }

    private function map_cart_items_with_warranties()
    {
        $cart_contents = WC()->cart->get_cart_contents();

        $products = array();

        foreach ( $cart_contents as $line ) {

            $product_id = $this->get_product_id($line);
            $id = $line['extendData']['leadToken'] ?? $product_id;

            $product = $products[ $id ] ?? array(
                'quantity'          => 0,
                'warranty_quantity' => 0,
                'warranties'        => array(),
            );

            if ($this->is_warranty($line)) {
                $product['warranty_quantity'] += $line['quantity'];
                $product['warranties'][] = $line;
            } else {
                $product['quantity'] += $line['quantity'];

                if (isset($line['extendData']) && isset($line['extendData']['leadQuantity'])) {
                    $product['leadQuantity'] = $line['extendData']['leadQuantity'];
                    $product['leadProductKey'] = $line['key'];
                }
            }

            $products[ $id ] = $product;
        }

        return $products;
    }

    // get_cart_updates()
    // goes through the cart and gets updates to products/plans for normalization
    public function get_cart_updates($products)
    {
        $cart_balancing = $this->settings['helloextend_enable_cart_balancing'] == 1 ? true : false;

        $updates = array();

        foreach ( $products as $product ) {

            // If warranty item is coming from lead and the quantity in the cart does not match the lead quantity
            if (isset($product['leadQuantity']) && isset($product['leadProductKey'])) {
                if ($product['leadQuantity'] != $product['quantity']) {
                    $updates[$product['leadProductKey']] = $product['leadQuantity'];
                }

                continue;
            }

            // Remove warranties without products
            if ($product['warranty_quantity'] > 0 && $product['quantity'] == 0 ) {
                foreach ( $product['warranties'] as $warranty ) {
                    $updates[ $warranty['key'] ] = 0;
                }
                continue;
            }
            
            // grab difference of warranty quantity and product quantity
            $quantity_diff = $product['warranty_quantity'] - $product['quantity'];

            // No difference or warranties, no updates
            if ($quantity_diff == 0 || $product['warranty_quantity'] == 0) {
                continue;
            }

            // Too many warranties
            if ($quantity_diff > 0 ) {
                foreach ( $product['warranties'] as $warranty ) {
                    if ($quantity_diff == 0) {
                        break;
                    }

                    $new_quantity_diff           = max([ 0, $quantity_diff - $warranty['quantity'] ]);
                    $removed_quantity            = $quantity_diff - $new_quantity_diff;
                    $updates[ $warranty['key'] ] = $warranty['quantity'] - $removed_quantity;
                    $quantity_diff               = $new_quantity_diff;
                }
                continue;
            }
            
            // Else, not enough warranties
            if ($cart_balancing && $quantity_diff < 0 ) {
                $warranty = $product['warranties'][0];
                $updates[$warranty['key']] = $warranty['quantity'] - $quantity_diff;
            }
        }

        return $updates;
    }

    // normalize_cart()
    // grabs & applies cart updates
    public function normalize_cart()
    {

        $products = $this->map_cart_items_with_warranties();

        $updates = $this->get_cart_updates($products);

        foreach ( $updates as $key => $quantity_update ) {
            WC()->cart->set_quantity($key, $quantity_update, true);
        }

        return WC()->cart;

    }

    // after_cart_item_name($cart_item, $key)
    // @param $cart_item : cart_item contains item information
    // @param $key : key is the cart_item's key and is not used
    // echos the offer element to the cart page
    public function after_cart_item_name( $cart_item, $key )
    {
        // if it's not a warranty, add offer element
        if (! isset($cart_item['extendData']) ) {
            $item_id     = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
            $parent_id   = $cart_item['product_id'];
            $categories  = get_the_terms($parent_id, 'product_cat');
            $category    = HelloExtend_Protection_Global::helloextend_get_first_valid_category($categories);

            echo "<div id='offer_".esc_attr($item_id)."' class='cart-extend-offer' data-covered='".esc_attr($item_id)."' data-category='".esc_attr($category)."'></div>";
        }
    }

    // cart_offers()
    // renders cart offers
    public function cart_offers()
    {
        // get Extend options
        $enable_helloextend             = trim($this->settings['enable_helloextend']);
        $helloextend_enable_cart_offers = $this->settings['helloextend_enable_cart_offers'];
        $cart                           = WC()->cart;

        if ($helloextend_enable_cart_offers === '1' && $enable_helloextend === '1' ) {
            wp_enqueue_script('helloextend_script');
            wp_enqueue_script('helloextend_cart_integration_script');
            $ajaxurl = admin_url('admin-ajax.php');
            wp_localize_script(
                'helloextend_cart_integration_script',
                'ExtendCartIntegration',
                compact('cart', 'helloextend_enable_cart_offers')
            );
        } else {
            HelloExtend_Protection_Logger::helloextend_log_error('Cart Offers Class: Extend is not enabled');
        }
    }
}
