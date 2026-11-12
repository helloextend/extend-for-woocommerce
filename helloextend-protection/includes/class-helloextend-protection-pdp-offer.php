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
 * The the PDP Offer functionality of the plugin.
 *
 * Adds .helloextend-offer div to the product display page,
 * and enqueues the necessary JS
 *
 * @package HelloExtend_Protection
 *  * // TODO: Q for JM - What does the subpackage need to be?
// * @subpackage HelloExtend_Protection/admin
 * @author  Extend, Inc.
 */
class HelloExtend_Protection_PDP_Offer
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



    private string $basename;
    private string $url;
    private string $path;
    private array $settings;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $helloextend_protection The name of this plugin.
     * @param string $version           The version of this plugin.
     * @since 1.0.0
     */
    public function __construct( $helloextend_protection, $version )
    {
        $this->helloextend_protection = $helloextend_protection;
        $this->version           = $version;

        $this->basename = plugin_basename(__FILE__);
        $this->url      = plugin_dir_url(__FILE__);
        $this->path     = plugin_dir_path(__FILE__);

        /* retrieve environment variables */
        $this->settings = HelloExtend_Protection_Global::helloextend_get_settings();

        /* Initializes product_offer on the PDP Offer Location selected in wp-admin > Extend */
        add_action($this->settings['helloextend_pdp_offer_location'], [ $this, 'product_offer' ]);
    }

    /**
     * Grabs required variables, and enqueues product scripts
     *
     * @since 1.0.0
     */
    public function product_offer()
    {
        global $product;

        // Variables that are passed to the PDP JS Script
        $id                          = $product->get_id();
        $sku                         = $product->get_sku();
        
        $categories                  = get_the_terms($id, 'product_cat');
        $first_category              = HelloExtend_Protection_Global::helloextend_get_first_valid_category($categories);

        $price                       = (int) round(floatval($product->get_price()) * 100);
        $type                        = $product->get_type();
        $env                         = $this->settings['helloextend_environment'];
        $helloextend_pp_enabled      = $this->settings['enable_helloextend_pp'];
        $helloextend_pdp_offers_enabled   = $this->settings['helloextend_enable_pdp_offers'];
        $helloextend_modal_offers_enabled = $this->settings['helloextend_enable_modal_offers'];
        $helloextend_enabled              = $this->settings['enable_helloextend'];
        $atc_button_selector         = $this->settings['helloextend_atc_button_selector'];

        /*
         * Variation prices for the block add to cart (woocommerce/add-to-cart-with-options).
         *
         * The block replaces the classic variation form, and fires no JS event
         * on selection — its state lives in Interactivity API stores that
         * WooCommerce registers as private. The one piece of public markup it
         * does render is a hidden input[name="variation_id"] holding the
         * resolved variation, which the JS watches. That gives an ID but no
         * price, so the lookup table is built here instead of reading it out of
         * a store WooCommerce says will break between releases.
         *
         * Only built for variable products: get_available_variations() loads
         * every variation, which is wasted work for every other product type.
         */
        $variations = array();

        if ($type === 'variable') {
            foreach ($product->get_available_variations() as $variation) {
                $variations[ (string) $variation['variation_id'] ] = (int) round(floatval($variation['display_price']) * 100);
            }
        }

        if ($helloextend_enabled == '1' && $helloextend_pp_enabled == '1' ) {
            wp_enqueue_script('helloextend_script');
            wp_enqueue_script('helloextend_product_integration_script');
            wp_localize_script(
                'helloextend_product_integration_script',
                'ExtendProductIntegration',
                compact('id', 'sku', 'first_category', 'price', 'type', 'env', 'helloextend_enabled', 'helloextend_pp_enabled', 'helloextend_pdp_offers_enabled', 'helloextend_modal_offers_enabled', 'atc_button_selector', 'variations')
            );
        }

        /*
         * The container is also needed with PDP offers off but the modal on.
         * The modal resolves its plan, reference id and price through the live
         * Extend.buttons component, and the component has to be rendered into
         * something — so the container ships either way and
         * helloextend-pdp-offers.js hides it when the inline offer is off.
         */
        if ($helloextend_pdp_offers_enabled || $helloextend_modal_offers_enabled) {
            echo "<div class='helloextend-offer' data-extend='pdpOfferContainer' style='width: 100%;'></div>";
        }
    }
}
