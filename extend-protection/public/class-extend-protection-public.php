<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link  http://example.com
 * @since 1.0.0
 *
 * @package    Extend_Protection
 * @subpackage Extend_Protection/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Extend_Protection
 * @subpackage Extend_Protection/public
 * @author     Your Name <email@example.com>
 */
class Extend_Protection_Public
{

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $extend_protection    The ID of this plugin.
     */
    private $extend_protection;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $extend_protection The name of the plugin.
     * @param string $version           The version of this plugin.
     */
    public function __construct( $extend_protection, $version )
    {

        $this->extend_protection = $extend_protection;
        $this->version           = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Extend_Protection_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Extend_Protection_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->extend_protection, plugin_dir_url(__FILE__) . 'css/extend-protection-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Extend_Protection_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Extend_Protection_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->extend_protection, plugin_dir_url(__FILE__) . 'js/extend-protection-public.js', array( 'jquery' ), $this->version, false);

    }
}
