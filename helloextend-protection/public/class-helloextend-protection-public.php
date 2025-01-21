<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link  http://example.com
 * @since 1.0.0
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/public
 */

// Prevent direct access to the file
if (! defined('ABSPATH') ) {
    exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/public
 * @author     Your Name <email@example.com>
 */
class HelloExtend_Protection_Public
{

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $helloextend_protection    The ID of this plugin.
     */
    private $helloextend_protection;

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
     * @param string $helloextend_protection The name of the plugin.
     * @param string $version           The version of this plugin.
     */
    public function __construct( $helloextend_protection, $version )
    {

        $this->helloextend_protection = $helloextend_protection;
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
         * defined in HelloExtend_Protection_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The HelloExtend_Protection_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->helloextend_protection, plugin_dir_url(__FILE__) . 'css/helloextend-protection-public.css', array(), $this->version, 'all');

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
         * defined in HelloExtend_Protection_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The HelloExtend_Protection_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->helloextend_protection, plugin_dir_url(__FILE__) . 'js/helloextend-protection-public.js', array( 'jquery' ), $this->version, false);

    }
}
