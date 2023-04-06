<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Extend_Protection
 * @subpackage Extend_Protection/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Extend_Protection
 * @subpackage Extend_Protection/includes
 * @author     Your Name <email@example.com>
 */
class Extend_Protection
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Extend_Protection_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $extend_protection The string used to uniquely identify this plugin.
     */
    protected $extend_protection;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;


    /**
     * Renders PDP offers on the Product Page.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $pdp_offer The current version of the plugin.
     */
    protected $pdp_offer;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('EXTEND_PROTECTION_VERSION')) {
            $this->version = EXTEND_PROTECTION_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->extend_protection = 'extend-protection';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_pdp_offer_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Extend_Protection_Loader. Orchestrates the hooks of the plugin.
     * - Extend_Protection_i18n. Defines internationalization functionality.
     * - Extend_Protection_Admin. Defines all hooks for the admin area.
     * - Extend_Protection_Public. Defines all hooks for the public side of the site.
     * - Extend_Protection_PDP_Offer. Renders Extend offers on PDP page.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-extend-protection-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-extend-protection-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-extend-protection-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-extend-protection-public.php';

        /**
         * The class responsible adding .extend-offer div and the JS to render Extend
         * offers on the PDP page
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-extend-protection-pdp-offer.php';

        //TODO: If Extend is enabled, enqueue SDK JS

        $this->loader = new Extend_Protection_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Extend_Protection_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Extend_Protection_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Extend_Protection_Admin($this->get_extend_protection(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    }

    /**
     * Register all of the hooks related to the PDP offers functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_pdp_offer_hooks()
    {
        $this->pdp_offer = new Extend_Protection_PDP_Offer($this->get_extend_protection(), $this->get_version());
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Extend_Protection_Public($this->get_extend_protection(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_extend_protection()
    {
        return $this->extend_protection;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Extend_Protection_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }
}
