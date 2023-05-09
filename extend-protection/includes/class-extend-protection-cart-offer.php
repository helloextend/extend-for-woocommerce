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
 * The Cart Offer functionality of the plugin.
 *
 * Adds .extend-cart-offer div to each line item in the cart
 * Enqueues the necessary JS
 * Renders Extend cart offers
 * @package    Extend_Protection
 * @author     Extend, Inc.
 */

class Extend_Protection_Cart_Offer {
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

    protected $warranty_product_id = null;
    protected $products = [];
    protected $updates = [];

    public function __construct() {
        $this->hooks();
        // $this->warranty_product_id = wc_get_product_id_by_sku('extend-product-protection');
    }

    /**
     * Initiate our hooks.
     *
     * @since  0.0.0
     */
    public function hooks() {
        //after cart item name add offer element
        add_action('woocommerce_after_cart_item_name', [$this, 'after_cart_item_name'], 10, 2);
    }



    // after_cart_item_name($cart_item, $key)
    // @param $cart_item : cart_item contains item information
    // @param $key : key is the cart_item's key and is not used
    // echos the offer element to the cart page
    public function after_cart_item_name(){
        //if it's not a warranty, add offer element
        echo "<div class='extend-cart-offer'> CART OFFERS </div>";
//        if(!isset($cart_item['extendData'])){
//            $item_id = $cart_item['variation_id']?$cart_item['variation_id']:$cart_item['product_id'];
//            echo "<div id='offer_$item_id' class='cart-extend-offer' data-covered='$item_id'> ";
//        }

    }


}