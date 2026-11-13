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
     * Whether the product integration has been enqueued for this request.
     *
     * @since 1.3.1
     * @var   bool
     */
    private bool $localized = false;

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

        /*
         * The scripts load on their own hook rather than on the offer
         * location. A theme whose product template fires no add to cart hook
         * at all used to get nothing: no script, no ExtendProductIntegration,
         * and helloextend-pdp-offers.js returning on its first line. Only the
         * container has to sit at a particular point in the markup, and
         * helloextend-pdp-offers.js can place one itself when the template
         * never offered it the chance.
         */
        add_action('wp_enqueue_scripts', [ $this, 'enqueue_product_integration' ]);

        /* Initializes product_offer on the PDP Offer Location selected in wp-admin > Extend */
        add_action($this->settings['helloextend_pdp_offer_location'], [ $this, 'product_offer' ]);
    }

    /**
     * Enqueue the product integration on a single product page.
     *
     * Reads the product from the queried object rather than the $product
     * global, which the loop has not set up this early.
     *
     * @return void
     * @since  1.3.1
     */
    public function enqueue_product_integration()
    {
        if (!function_exists('is_product') || !is_product()) {
            return;
        }

        $this->localize_product_integration(wc_get_product(get_queried_object_id()));
    }

    /**
     * Grabs required variables, and enqueues product scripts
     *
     * Runs at most once a request: whichever of the two entry points gets
     * here first does the work and the other returns immediately.
     *
     * @param WC_Product|mixed $product The product being displayed.
     *
     * @return void
     * @since  1.0.0
     */
    private function localize_product_integration($product)
    {
        if ($this->localized || !$product instanceof WC_Product) {
            return;
        }

        $this->localized = true;

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
    }

    /**
     * Echo the offer container at the configured PDP offer location.
     *
     * Only the markup lives here — the scripts are enqueued separately, so a
     * theme that never fires this hook still gets a working integration and
     * helloextend-pdp-offers.js places a container of its own.
     *
     * @return void
     * @since  1.0.0
     */
    public function product_offer()
    {
        /*
         * The one read of the $product global in the plugin, and the only
         * place a product is available: [product_page] renders the single
         * product template on an ordinary page, where is_product() is false
         * and the queried object is the page, so the enqueue above never
         * fires. On a normal product page this is a no-op — the scripts were
         * localized from wp_enqueue_scripts and the guard returns at once.
         */
        global $product;

        $this->localize_product_integration($product);

        /*
         * The container is also needed with PDP offers off but the modal on.
         * The modal resolves its plan, reference id and price through the live
         * Extend.buttons component, and the component has to be rendered into
         * something — so the container ships either way and
         * helloextend-pdp-offers.js hides it when the inline offer is off.
         */
        if ($this->settings['helloextend_enable_pdp_offers'] || $this->settings['helloextend_enable_modal_offers']) {
            echo "<div class='helloextend-offer' data-extend='pdpOfferContainer' style='width: 100%;'></div>";
        }
    }
}
