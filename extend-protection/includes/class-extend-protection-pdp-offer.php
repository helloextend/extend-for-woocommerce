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



    private string $basename;
    private string $url;
    private string $path;
    private array $settings;

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
        $this->settings = Extend_Protection_Global::get_extend_settings();

        /* Initializes product_offer on the PDP Offer Location selected in wp-admin > Extend */
        add_action($this->settings['extend_pdp_offer_location'], [$this, 'product_offer']);
    }

    /**
     * Grabs required variables, and enqueues product scripts
     *
     * @since    1.0.0
     */
    public function product_offer()
    {
        global $product;

        // Variables that are passed to the PDP JS Script
        $extend_use_skus            = $this->settings['extend_use_skus'];
        $id                         = $product->get_id();
        $sku                        = $product->get_sku();
        $categories                 = get_the_terms($id, 'product_cat');
        $first_category             = $categories[0]->name;
        $price                      = $product->get_price() * 100;
        $type                       = $product->get_type();
        $env                        = $this->settings['extend_environment'];
        $extend_pdp_offers_enabled  = $this->settings['extend_enable_pdp_offers'];
        $extend_modal_offers_enabled = $this->settings['extend_enable_modal_offers'];
        $extend_enabled             = $this->settings['enable_extend'];


        if($extend_enabled === '1') {
            wp_enqueue_script('extend_script');
            wp_enqueue_script('extend_product_integration_script');
            wp_localize_script('extend_product_integration_script', 'ExtendProductIntegration',
                compact('id', 'sku', 'first_category', 'price', 'type', 'env', 'extend_enabled', 'extend_pdp_offers_enabled', 'extend_modal_offers_enabled', 'extend_use_skus'));
            echo "<div class='extend-offer' data-extend='pdpOfferContainer'></div>";
        }
    }
}