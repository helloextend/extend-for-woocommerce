<?php
/**
 * Extend WooCommerce Product Integration.
 *
 * @since   1.0.0
 * @package Extend_Protection
 */

/**
 * Extend WooCommerce Product Integration.
 *
 * @since 1.0.0
 */
class Extend_Protection_PDP_Offer {
    /**
     * Parent plugin class.
     *
     * @since 1.0.0
     *
     * @var   Extend_Protection
     */
    protected $plugin = null;

    /**
     * Constructor.
     *
     * @since  1.0.0
     *
     * @param  Extend_Protection $plugin Main plugin object.
     */
    public function __construct( $plugin ) {
        $this->plugin = $plugin;
        $this->hooks();
    }

    /**
     * Initiate our hooks.
     *
     * @since  0.0.0
     */
    public function hooks() {

        // TODO: use has_action() to iterate through all the different hooks on the pdp page

        add_action('woocommerce_before_add_to_cart_form', [$this, 'product_offer']);
    }

    // product_offer()
    // grabs required variables, and enqueue's product scripts
    public function product_offer(){
        global $product;

        $id = $product->get_id();

        $type = $product->get_type();
        // TODO: Get all the options using the new plugin names
        $extend_protection_for_woocommerce_settings_options = get_option('extend_protection_for_woocommerce_settings');

        // TODO: Get option for enabled
        // TODO: Get offers for PDP enabled
        // TODO: Get option for env

        echo "<div class=\"extend-offer\">HELLO</div>";


        $extend_enabled = get_option('wc_extend_enabled');
        $extend_pdp_offers_enabled = get_option('wc_extend_pdp_offers_enabled');
        $extend_modal_offers_enabled = get_option('wc_extend_modal_offers_enabled');

        if($extend_enabled === 'yes') {
            wp_enqueue_script('extend_script');
            wp_enqueue_script('extend_product_integration_script');
            wp_localize_script('extend_product_integration_script', 'ExtendProductIntegration', compact('id', 'type', 'extend_modal_offers_enabled', 'extend_pdp_offers_enabled'));
            echo "<div class=\"extend-offer\"></div>";
        }
    }
}