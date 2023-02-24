<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Extend_Warranty
 * @subpackage Extend_Warranty/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Extend_Warranty
 * @subpackage Extend_Warranty/public
 * @author     Your Name <email@example.com>
 */
class Extend_Warranty_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $extend_warranty    The ID of this plugin.
	 */
	private $extend_warranty;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $extend_warranty       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $extend_warranty, $version ) {

		$this->extend_warranty = $extend_warranty;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Extend_Warranty_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Extend_Warranty_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->extend_warranty, plugin_dir_url( __FILE__ ) . 'css/extend-warranty-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Extend_Warranty_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Extend_Warranty_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->extend_warranty, plugin_dir_url( __FILE__ ) . 'js/extend-warranty-public.js', array( 'jquery' ), $this->version, false );

	}

}
