<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link  http://example.com
 * @since 1.0.0
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/includes
 * @author     support@extend.com
 */

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

class HelloExtend_Protection
{


    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    HelloExtend_Protection_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string $helloextend_protection The string used to uniquely identify this plugin.
     */
    protected $helloextend_protection;

    /**
     * The current version of the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string $version The current version of the plugin.
     */
    protected $version;


    /**
     * Renders PDP offers on the Product Page.
     *
     * @since  1.0.0
     * @access protected
     * @var    string $pdp_offer The current version of the plugin.
     */
    protected $pdp_offer;

    /**
     * Renders global hooks
     *
     * @since  1.0.0
     * @access protected
     * @var    string $global_hooks The current version of the plugin.
     */
    protected $global_hooks;
    private $sdk_url = 'https://sdk.helloextend.com/extend-sdk-client/v1/extend-sdk-client.min.js';

    /**
     * URL of plugin directory.
     *
     * @var   string
     * @since 1.0.0
     */
    protected $url = '';

    /**
     * Path of plugin directory.
     *
     * @var   string
     * @since 1.0.0
     */
    protected $path = '';
    private HelloExtend_Protection_Cart_Offer $cart_offer;
    private HelloExtend_Protection_Orders $orders;
    private HelloExtend_Protection_Shipping $shipping_protection;
    private HelloExtend_Protection_Sync $sync;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        if (defined('HELLOEXTEND_PROTECTION_VERSION') ) {
            $this->version = HELLOEXTEND_PROTECTION_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->helloextend_protection = 'helloextend-protection';

        $this->url  = plugin_dir_url(__FILE__);
        $this->path = plugin_dir_path(__FILE__);

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_global_hooks();
        $this->define_pdp_offer_hooks();
        $this->define_cart_offer_hooks();
        $this->define_orders_hooks();
        $this->define_shipping_protection_offer_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - HelloExtend_Protection_Loader. Orchestrates the hooks of the plugin.
     * - HelloExtend_Protection_i18n. Defines internationalization functionality.
     * - HelloExtend_Protection_Admin. Defines all hooks for the admin area.
     * - HelloExtend_Protection_Public. Defines all hooks for the public side of the site.
     * - HelloExtend_Protection_PDP_Offer. Renders Extend offers on PDP page.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-helloextend-protection-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-helloextend-protection-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-helloextend-protection-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'public/class-helloextend-protection-public.php';

        /**
         * The class responsible for adding .helloextend-offer div and the JS to render Extend
         * offers on the PDP page
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-helloextend-protection-pdp-offer.php';

        /**
         * The class responsible for rendering extend offers on the cart page
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-helloextend-protection-cart-offer.php';

        /**
         * The class responsible for handling the Extend Orders API
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-helloextend-protection-orders.php';

        /**
         * The class responsible for loading the global class and enqueing the JS
         * to add extend offers to the cart
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-helloextend-global.php';

        /**
         * The class responsible for handling the Logs
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-helloextend-protection-logger.php';

        /**
         * The class responsible for handling the Shipping Protection
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-helloextend-protection-shipping.php';

        /**
         * The class responsible for handling the Product Sync
         */
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-helloextend-protection-sync.php';

        $this->loader = new HelloExtend_Protection_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the HelloExtend_Protection_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     */
    private function set_locale()
    {

        $plugin_i18n = new HelloExtend_Protection_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new HelloExtend_Protection_Admin($this->get_helloextend_protection(), $this->get_version());
        $this->sync   = new HelloExtend_Protection_Sync($this->get_helloextend_protection(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    }

    /**
     * Register globals class and add hooks to render Extend offers on PDP page
     *
     * @since  1.0.0
     * @access private
     */
    private function define_global_hooks()
    {
        wp_register_script('helloextend_script', $this->sdk_url, array(), '1.0.0', true);
        wp_register_script('helloextend_global_script', $this->url . '../js/helloextend-global.js', [ 'jquery', 'helloextend_script' ], '1.0.0', true);
        wp_register_script('helloextend_product_integration_script', $this->url . '../js/helloextend-pdp-offers.js', [ 'jquery', 'helloextend_global_script' ], '1.0.0', true);
        wp_register_script('helloextend_cart_integration_script', $this->url . '../js/helloextend-cart-offers.js', [ 'jquery', 'helloextend_script' ], '1.0.0', true);
        wp_register_script('helloextend_shipping_integration_script', $this->url . '../js/helloextend-shipping-offers.js', [ 'jquery', 'helloextend_script' ], '1.0.0', true);
        wp_register_script('helloextend_sync_script', $this->url . '../js/helloextend-sync.js', [ 'jquery', 'helloextend_script' ], '1.0.0', true);

        $this->global_hooks = new HelloExtend_Protection_Global($this->get_helloextend_protection(), $this->get_version());
    }

    /**
     * Register all of the hooks related to the PDP offers functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_pdp_offer_hooks()
    {
        $this->pdp_offer = new HelloExtend_Protection_PDP_Offer($this->get_helloextend_protection(), $this->get_version());
    }

    /**
     * Register all the hooks related to the cart offers functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_cart_offer_hooks()
    {
        $this->cart_offer = new HelloExtend_Protection_Cart_Offer($this->get_helloextend_protection(), $this->get_version());
    }

    /**
     * Register all the hooks related to the shipping protection offers functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_shipping_protection_offer_hooks()
    {
        $this->shipping_protection_offer = new HelloExtend_Protection_Shipping($this->get_helloextend_protection(), $this->get_version());
    }

    /**
     * Register all the hooks related to the Extend Orders API
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_orders_hooks()
    {
        $this->orders = new HelloExtend_Protection_Orders($this->get_helloextend_protection(), $this->get_version());
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_public_hooks()
    {

        $plugin_public = new HelloExtend_Protection_Public($this->get_helloextend_protection(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since 1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return string    The name of the plugin.
     * @since  1.0.0
     */
    public function get_helloextend_protection()
    {
        return $this->helloextend_protection;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return HelloExtend_Protection_Loader    Orchestrates the hooks of the plugin.
     * @since  1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return string    The version number of the plugin.
     * @since  1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }
}
