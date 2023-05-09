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
 * The the PDP Offer functionality of the plugin.
 *
 * Adds .extend-offer div to the product display page,
 * and enqueues the necessary JS
 *
 * @package    Extend_Protection
 *  * // TODO: Q for JM - What does the subpackage need to be?
// * @subpackage Extend_Protection/admin
 * @author     Extend, Inc.
 */
class Extend_Protection_PDP_Offer
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
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $extend_protection_for_woocommerce_settings_options The current options of this plugin.
     */
    private $extend_protection_for_woocommerce_settings_options;

    private string $env;
    private string $sdk_url;
    private ?string $store_id;
    private ?string $api_host;
    private ?string $api_key;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $extend_protection The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */

    public function __construct($extend_protection, $version)
    {
        $this->extend_protection = $extend_protection;
        $this->version = $version;

        $this->basename = plugin_basename( __FILE__ );
        $this->url      = plugin_dir_url( __FILE__ );
        $this->path     = plugin_dir_path( __FILE__ );

        /* retrieve environment variables */
        // TODO: Move all these variables to a more global location
        $this->extend_protection_all_settings = get_option('extend_protection_for_woocommerce_settings');
        $this->enable_extend = array_key_exists('enable_extend', $this->extend_protection_all_settings) ? $this->extend_protection_all_settings['enable_extend'] : 0;
        $this->extend_enable_cart_offers = array_key_exists('extend_enable_cart_offers', $this->extend_protection_all_settings) ? $this->extend_protection_all_settings['extend_enable_cart_offers'] : 0;
        $this->extend_enable_cart_balancing = array_key_exists('extend_enable_cart_balancing', $this->extend_protection_all_settings) ? $this->extend_protection_all_settings['extend_enable_cart_balancing'] : 0;
        $this->extend_enable_pdp_offers = array_key_exists('extend_enable_pdp_offers', $this->extend_protection_all_settings) ? $this->extend_protection_all_settings['extend_enable_pdp_offers'] : 0;
        $this->extend_enable_modal_offers = array_key_exists('extend_enable_modal_offers', $this->extend_protection_all_settings) ? $this->extend_protection_all_settings['extend_enable_modal_offers'] : 0;
        $this->extend_environment = $this->extend_protection_all_settings['extend_environment'];

        /* Set variables depending on environment */
        if ($this->extend_environment == 'live') {
            $this->store_id = $this->extend_protection_all_settings['extend_live_store_id'];
            $this->api_host = 'https://api.helloextend.com';
            $this->api_key = $this->extend_protection_all_settings['extend_live_api_key'];
        }
        else {
            $this->store_id = $this->extend_protection_all_settings['extend_sandbox_store_id'];
            $this->api_host = 'https://api-sandbox.helloextend.com';
            $this->api_key = $this->extend_protection_all_settings['extend_sandbox_api_key'];
        }

        //TODO: Retrieve SDK URL and enqueue it as a dependency
        $this->sdk_url = 'https://sdk.helloextend.com/extend-sdk-client/v1/extend-sdk-client.min.js';

        $this->hooks_checker();

    }

    public function hooks_checker() {
        // TODO: use has_action() to iterate through all the different hooks on the pdp page

        add_action('woocommerce_before_add_to_cart_form', [$this, 'product_offer']);
    }

    /**
     * Grabs required variables, and enqueues product scripts
     *
     * @since    1.0.0
     */
    public function product_offer()
    {
        global $product;

        $id = $product->get_id();

        $sku = $product->get_sku();

        $categories = get_the_terms( $id, 'product_cat' );

        $first_category = $categories[0]->name;

        $price = $product->get_price() * 100;

        $type = $product->get_type();

        $env = $this->extend_environment;

        $sdk_url = $this->sdk_url;

        $extend_pdp_offers_enabled = $this->extend_enable_pdp_offers;

        $extend_modal_offers_enabled = $this->extend_enable_modal_offers;

        $extend_enabled = $this->enable_extend;

        if($extend_enabled === '1') {
            wp_enqueue_script('extend_script');
            wp_enqueue_script('extend_product_integration_script');
            wp_localize_script('extend_product_integration_script', 'ExtendProductIntegration', compact('id', 'sku', 'first_category', 'price', 'type', 'env', 'extend_enabled', 'extend_pdp_offers_enabled', 'extend_modal_offers_enabled'));
            echo "<div class=\"extend-offer\">
                    <h3>EXTEND OFFERS</h3> 
                        <ul>
                            <li>ID: $id</li>
                            <li>Type: $type</li>
                            <li>Price: $price</li>
                            <li>Category: $first_category</li>
                            <li>Env: $env</li>
                            <li>SDK URL: $sdk_url</li>
                            <li>Extend Enabled: $extend_enabled</li>	
                            <li>Extend PDP Offers Enabled: $extend_pdp_offers_enabled</li>
                            <li>Extend Modal Offers Enabled: $extend_modal_offers_enabled</li>
                        </ul>
                </div>";
        }
    }
}