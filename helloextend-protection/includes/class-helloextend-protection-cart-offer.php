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
    protected array $products = [];
    protected array $updates  = [];
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

    // get_cart_updates()
    // goes through the cart and gets updates to products/plans for normalization
    public function get_cart_updates()
    {

        $cart_contents = WC()->cart->get_cart_contents();

        foreach ( $cart_contents as $line ) {

            // if we're on a warranty item
            if (intval($line['product_id']) === intval($this->settings['warranty_product_id']) && isset($line['extendData']) && !empty($line['extendData']) ) {
                // Grab reference id
                $product_reference_id = $line['extendData']['covered_product_id'];

                // If this product doesn't exist, create it with the warranty quantity and warranty added, else add to warranty quantity, and add warranty to warranty list
                if (! isset($products[ $product_reference_id ]) ) {
                    $products[ $product_reference_id ] = [
                        'quantity'          => 0,
                        'warranty_quantity' => $line['quantity'],
                        'warranties'        => [ $line ],
                    ];
                } else {
                    $products[ $product_reference_id ]['warranty_quantity'] += $line['quantity'];
                    array_push($products[ $product_reference_id ]['warranties'], $line);
                }
                // if we're on a non-warranty check if the product exists in list, if so add quantity, if not add to product list
            } else {
                $id = $line['variation_id'] > 0 ? $line['variation_id'] : $line['product_id'];
                if (! isset($products[ $id ]) ) {
                    $products[ $id ] = [
                        'quantity'          => $line['quantity'],
                        'warranty_quantity' => 0,
                        'warranties'        => [],
                    ];
                } else {
                    $products[ $id ]['quantity'] += $line['quantity'];
                }
            }
        }

        $cart_balancing = $this->settings['helloextend_enable_cart_balancing'] == 1 ? true : false;

        // if we have products, go through each and check for updates
        if (isset($products) ) {
            foreach ( $products as $product ) {

                // if warranty quantity is greater than 0 and product quantity is 0 set warranty quantity to 0
                if (intval($product['warranty_quantity']) > 0 && intval($product['quantity']) == 0 ) {
                    foreach ( $product['warranties'] as $warranty ) {
                        $updates[ $warranty['key'] ] = [ 'quantity' => 0 ];
                    }
                } else {
                    // grab difference of warranty_quantity and product quantity
                    $diff = $product['warranty_quantity'] - $product['quantity'];

                    // if there's a difference & that difference is greater than 0, we remove warranties till we reach the product quantity
                    if ($diff !== 0 ) {
                        if ($diff > 0 ) {
                            foreach ( $product['warranties'] as $warranty ) {
                                $new_quantity_diff           = max([ 0, $diff - $warranty['quantity'] ]);
                                $removed_quantity            = $diff - $new_quantity_diff;
                                $updates[ $warranty['key'] ] = [ 'quantity' => $warranty['quantity'] - $removed_quantity ];
                                $diff                        = $new_quantity_diff;
                            }
                        } elseif ($cart_balancing && $diff < 0 ) {
                            foreach ( $product['warranties'] as $warranty ) {
                                $new_quantity_diff           = max([ 0, $diff - $warranty['quantity'] ]);
                                $new_quantity                = $warranty['quantity'] - $diff;
                                $updates[ $warranty['key'] ] = [ 'quantity' => $new_quantity ];
                                $diff                        = $new_quantity_diff;
                            }
                        }
                    }
                }
            }
        }

        // if there's updates return updates
        if (isset($updates) ) {
            return $updates;
        }
    }

    // normalize_cart()
    // grabs & applies cart updates
    public function normalize_cart()
    {

        $newUpdates = $this->get_cart_updates();

        if (isset($newUpdates) ) {
            $cart = WC()->cart->get_cart_contents();
            foreach ( $cart as $line ) {

                foreach ( $newUpdates as $key => $value ) {
                    if ($key == $line['key'] ) {
                        WC()->cart->set_quantity($key, $value['quantity'], true);
                    }
                }
            }
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
            $item_sku    = $cart_item['data']->get_sku() ? $cart_item['data']->get_sku() : $item_id;
            $referenceId = $this->settings['helloextend_use_skus'] ? $item_sku : $item_id;
            $categories  = get_the_terms($item_id, 'product_cat');
            $category    = HelloExtend_Protection_Global::helloextend_get_first_valid_category($categories);

            echo "<div id='offer_".esc_attr($item_id)."' class='cart-extend-offer' data-covered='".esc_attr($referenceId)."' data-category='".esc_attr($category)."'></div>";
        }
    }

    // cart_offers()
    // renders cart offers
    public function cart_offers()
    {
        // get Extend options
        $enable_helloextend             = trim($this->settings['enable_helloextend']);
        $helloextend_enable_cart_offers = $this->settings['helloextend_enable_cart_offers'];
        $cart                      = WC()->cart;

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
