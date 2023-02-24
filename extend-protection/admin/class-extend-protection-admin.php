<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Extend_Protection
 * @subpackage Extend_Protection/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Extend_Protection
 * @subpackage Extend_Protection/admin
 * @author     Your Name <email@example.com>
 */
class Extend_Protection_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $extend_protection    The ID of this plugin.
	 */
	private $extend_protection;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $extend_protection_for_woocommerce_settings_options    The current options of this plugin.
     */
    private $extend_protection_for_woocommerce_settings_options;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $extend_protection       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $extend_protection, $version ) {

		$this->extend_protection = $extend_protection;
		$this->version = $version;
        $this->extend_protection_for_woocommerce_settings_options = get_option( 'extend_protection_for_woocommerce_settings' );

        add_action( 'admin_menu', array( $this, 'extend_admin_menu' ), 50 );
        add_action( 'admin_init', array( $this, 'extend_protection_for_woocommerce_settings_page_init' ) );
        add_action('admin_enqueue_scripts', 'extend_protection_style');
        //add_action( 'admin_menu', array( $this, 'settings_menu' ), 50 );
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->extend_protection, plugin_dir_url( __FILE__ ) . 'css/extend-protection-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->extend_protection, plugin_dir_url( __FILE__ ) . 'js/extend-protection-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Add menu items.
     */
    public function extend_admin_menu() {
        global $menu, $admin_page_hooks;

        $extend_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjA2IiBoZWlnaHQ9IjE2MyIgdmlld0JveD0iMCAwIDIwNiAxNjMiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0xMTAuNzg5IDMyLjczNkwxMzYuMTczIC0zLjgxNDdlLTA2SDE5Ny44ODhMMTQxLjc2IDY5LjEwOTJMMTEwLjc4OSAzMi43MzZaIiBmaWxsPSIjMDBDOUZGIi8+CjxwYXRoIGQ9Ik0xMTAuNzg5IDMyLjczNkwxMzYuMTczIC0zLjgxNDdlLTA2SDE5Ny44ODhMMTQxLjc2IDY5LjEwOTJMMTEwLjc4OSAzMi43MzZaIiBzdHJva2U9IndoaXRlIi8+CjxwYXRoIGQ9Ik0yMDUuMzQ1IDE2Mi42MTFIMTQxLjU2OEMxNDEuNTY4IDE2Mi42MTEgMTAzLjI0NyAxMTcuMDI5IDEwMS4xODggMTE0LjI5MkM5MS42ODY4IDEyNi45NTkgNjIuODI2NCAxNjIuNjExIDYyLjgyNjQgMTYyLjYxMUgwTDY5LjgzMzkgNzguMDUzMUwzLjIwNjQ0IDAuMDYyNDczM0g2Ni42MjY4TDIwNS4zNDUgMTYyLjYxMVoiIGZpbGw9IiMwMzMyQ0MiLz4KPHBhdGggZD0iTTIwNS4zNDUgMTYyLjYxMUgxNDEuNTY4QzE0MS41NjggMTYyLjYxMSAxMDMuMjQ3IDExNy4wMjkgMTAxLjE4OCAxMTQuMjkyQzkxLjY4NjggMTI2Ljk1OSA2Mi44MjY0IDE2Mi42MTEgNjIuODI2NCAxNjIuNjExSDBMNjkuODMzOSA3OC4wNTMxTDMuMjA2NDQgMC4wNjI0NzMzSDY2LjYyNjhMMjA1LjM0NSAxNjIuNjExWiIgc3Ryb2tlPSJ3aGl0ZSIvPgo8L3N2Zz4K';

        add_menu_page(  'Extend Protection',  'Extend', 'manage_options', 'extend', null, $extend_icon, '55.5' );
        add_submenu_page('extend', 'Settings', 'Settings', 'manage_options', 'extend', 'extend_render_settings_page');
        add_submenu_page('extend', 'Documentation', 'Documentation', 'manage_options', 'extend-docs', 'extend_render_documentation_page');
        add_submenu_page('extend', 'About', 'About', 'manage_options', 'extend-about', 'extend_render_about_page');
    }

    /**
     * Add menu items.
     */
    public function settings_menu() {
        $settings_page = add_submenu_page( 'woocommerce', 'WooCommerce settings',  'Settings', 'manage_options', 'extend-settings', array( $this, 'settings_page' ) );

        add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
    }



    public function extend_protection_for_woocommerce_settings_page_init() {
        register_setting(
            'extend_protection_for_woocommerce_settings_option_group', // option_group
            'extend_protection_for_woocommerce_settings', // option_name
            array( $this, 'extend_protection_for_woocommerce_settings_sanitize' ) // sanitize_callback
        );

        add_settings_section(
            'extend_protection_for_woocommerce_settings_setting_section', // id
            'Product Protection Settings', // title
            array( $this, 'extend_protection_for_woocommerce_settings_section_info' ), // callback
            'extend-protection-for-woocommerce-settings-admin' // page
        );

        add_settings_field(
            'enable_extend', // id
            ' Enable Extend', // title
            array( $this, 'enable_extend_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_enable_cart_offers', // id
            'Enable Cart Offers', // title
            array( $this, 'extend_enable_cart_offers_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_enable_cart_balancing', // id
            'Enable Cart Balancing	', // title
            array( $this, 'extend_enable_cart_balancing_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_enable_pdp_offers', // id
            'Enable PDP Offers	', // title
            array( $this, 'extend_enable_pdp_offers_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_enable_modal_offers', // id
            'Enable Modal Offers', // title
            array( $this, 'extend_enable_modal_offers_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_automated_product_sync', // id
            'Automated Product Sync', // title
            array( $this, 'extend_automated_product_sync_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_environment', // id
            'Environment', // title
            array( $this, 'extend_environment_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_sandbox_store_id', // id
            'Extend Sandbox Store Id', // title
            array( $this, 'extend_sandbox_store_id_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_sandbox_api_key', // id
            'Extend Sandbox API Key', // title
            array( $this, 'extend_sandbox_api_key_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_live_store_id', // id
            'Extend Live Store Id', // title
            array( $this, 'extend_live_store_id_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

        add_settings_field(
            'extend_live_api_key', // id
            'Extend Live API Key', // title
            array( $this, 'extend_live_api_key_callback' ), // callback
            'extend-protection-for-woocommerce-settings-admin', // page
            'extend_protection_for_woocommerce_settings_setting_section' // section
        );

    }

    public function extend_protection_for_woocommerce_settings_sanitize($input) {
        $sanitary_values = array();
        if ( isset( $input['enable_extend'] ) ) {
            $sanitary_values['enable_extend'] = $input['enable_extend'];
        }

        if ( isset( $input['extend_enable_cart_offers'] ) ) {
            $sanitary_values['extend_enable_cart_offers'] = $input['extend_enable_cart_offers'];
        }

        if ( isset( $input['extend_enable_cart_balancing'] ) ) {
            $sanitary_values['extend_enable_cart_balancing'] = $input['extend_enable_cart_balancing'];
        }

        if ( isset( $input['extend_enable_pdp_offers'] ) ) {
            $sanitary_values['extend_enable_pdp_offers'] = $input['extend_enable_pdp_offers'];
        }

        if ( isset( $input['extend_enable_modal_offers'] ) ) {
            $sanitary_values['extend_enable_modal_offers'] = $input['extend_enable_modal_offers'];
        }

        if ( isset( $input['extend_automated_product_sync'] ) ) {
            $sanitary_values['extend_automated_product_sync'] = $input['extend_automated_product_sync'];
        }

        if ( isset( $input['extend_environment'] ) ) {
            $sanitary_values['extend_environment'] = $input['extend_environment'];
        }

        if ( isset( $input['extend_sandbox_store_id'] ) ) {
            $sanitary_values['extend_sandbox_store_id'] = sanitize_text_field( $input['extend_sandbox_store_id'] );
        }

        if ( isset( $input['extend_sandbox_api_key'] ) ) {
            $sanitary_values['extend_sandbox_api_key'] = sanitize_textarea_field( $input['extend_sandbox_api_key'] );
        }

        if ( isset( $input['extend_live_store_id'] ) ) {
            $sanitary_values['extend_live_store_id'] = sanitize_text_field( $input['extend_live_store_id'] );
        }

        if ( isset( $input['extend_live_api_key'] ) ) {
            $sanitary_values['extend_live_api_key'] = sanitize_textarea_field( $input['extend_live_api_key'] );
        }

        return $sanitary_values;
    }

    public function extend_protection_for_woocommerce_settings_section_info() {

    }

    public function enable_extend_callback() {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_settings[enable_extend]" id="enable_extend" value="enable_extend" %s>',
            ( isset( $this->extend_protection_for_woocommerce_settings_options['enable_extend'] ) && $this->extend_protection_for_woocommerce_settings_options['enable_extend'] === 'enable_extend' ) ? 'checked' : ''
        );
    }

    public function extend_enable_cart_offers_callback() {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_settings[extend_enable_cart_offers]" id="extend_enable_cart_offers" value="extend_enable_cart_offers" %s> <label for="extend_enable_cart_offers">Display protection offers in the cart</label>',
            ( isset( $this->extend_protection_for_woocommerce_settings_options['extend_enable_cart_offers'] ) && $this->extend_protection_for_woocommerce_settings_options['extend_enable_cart_offers'] === 'extend_enable_cart_offers' ) ? 'checked' : ''
        );
    }

    public function extend_enable_cart_balancing_callback() {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_settings[extend_enable_cart_balancing]" id="extend_enable_cart_balancing" value="extend_enable_cart_balancing" %s> <label for="extend_enable_cart_balancing">Automatically adjust quantities</label>',
            ( isset( $this->extend_protection_for_woocommerce_settings_options['extend_enable_cart_balancing'] ) && $this->extend_protection_for_woocommerce_settings_options['extend_enable_cart_balancing'] === 'extend_enable_cart_balancing' ) ? 'checked' : ''
        );
    }

    public function extend_enable_pdp_offers_callback() {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_settings[extend_enable_pdp_offers]" id="extend_enable_pdp_offers" value="extend_enable_pdp_offers" %s> <label for="extend_enable_pdp_offers">Display offers on product page</label>',
            ( isset( $this->extend_protection_for_woocommerce_settings_options['extend_enable_pdp_offers'] ) && $this->extend_protection_for_woocommerce_settings_options['extend_enable_pdp_offers'] === 'extend_enable_pdp_offers' ) ? 'checked' : ''
        );
    }

    public function extend_enable_modal_offers_callback() {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_settings[extend_enable_modal_offers]" id="extend_enable_modal_offers" value="extend_enable_modal_offers" %s>',
            ( isset( $this->extend_protection_for_woocommerce_settings_options['extend_enable_modal_offers'] ) && $this->extend_protection_for_woocommerce_settings_options['extend_enable_modal_offers'] === 'extend_enable_modal_offers' ) ? 'checked' : ''
        );
    }

    public function extend_automated_product_sync_callback() {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_settings[extend_automated_product_sync]" id="extend_automated_product_sync" value="extend_automated_product_sync" %s>',
            ( isset( $this->extend_protection_for_woocommerce_settings_options['extend_automated_product_sync'] ) && $this->extend_protection_for_woocommerce_settings_options['extend_automated_product_sync'] === 'extend_automated_product_sync' ) ? 'checked' : ''
        );
    }

    public function extend_environment_callback() {
        ?> <select name="extend_protection_for_woocommerce_settings[environment]" id="environment">
            <?php $selected = (isset( $this->extend_protection_for_woocommerce_settings_options['extend_environment'] ) && $this->extend_protection_for_woocommerce_settings_options['extend_environment'] === 'sandbox') ? 'selected' : '' ; ?>
            <option value="sandbox" <?php echo $selected; ?>>Sandbox</option>
            <?php $selected = (isset( $this->extend_protection_for_woocommerce_settings_options['extend_environment'] ) && $this->extend_protection_for_woocommerce_settings_options['extend_environment'] === 'live') ? 'selected' : '' ; ?>
            <option value="live" <?php echo $selected; ?>>Live</option>
        </select> <?php
    }

    public function extend_sandbox_store_id_callback() {
        printf(
            '<input class="regular-text" type="text" name="extend_protection_for_woocommerce_settings[extend_sandbox_store_id]" id="extend_sandbox_store_id" value="%s">',
            isset( $this->extend_protection_for_woocommerce_settings_options['extend_sandbox_store_id'] ) ? esc_attr( $this->extend_protection_for_woocommerce_settings_options['extend_sandbox_store_id']) : ''
        );
    }

    public function extend_sandbox_api_key_callback() {
        printf(
            '<textarea class="regular-text" rows="5" name="extend_protection_for_woocommerce_settings[extend_sandbox_api_key]" id="extend_sandbox_api_key" >%s</textarea>',
            isset( $this->extend_protection_for_woocommerce_settings_options['extend_sandbox_api_key'] ) ? esc_attr( $this->extend_protection_for_woocommerce_settings_options['extend_sandbox_api_key']) : ''
        );
    }

    public function extend_live_store_id_callback() {
        printf(
            '<input class="regular-text" type="text" name="extend_protection_for_woocommerce_settings[extend_live_store_id]" id="extend_live_store_id" value="%s">',
            isset( $this->extend_protection_for_woocommerce_settings_options['extend_live_store_id'] ) ? esc_attr( $this->extend_protection_for_woocommerce_settings_options['extend_live_store_id']) : ''
        );
    }

    public function extend_live_api_key_callback() {
        printf(
            '<textarea class="regular-text" rows="5" name="extend_protection_for_woocommerce_settings[extend_live_api_key]" id="extend_live_api_key" >%s</textarea>',
            isset( $this->extend_protection_for_woocommerce_settings_options['extend_live_api_key'] ) ? esc_attr( $this->extend_protection_for_woocommerce_settings_options['extend_live_api_key']) : ''
        );
    }

}