<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://extend.com
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
class Extend_Protection_Admin
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

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $extend_protection_for_woocommerce_settings_options The current options of this plugin.
     */
    private $extend_protection_for_woocommerce_settings_options;


    private string $env;
    private string $sdk_url;
    private ?string $store_id;
    private ?string $api_host;
    private ?string $api_key;
    private $extend_protection_for_woocommerce_settings_product_protection_options;
    private $extend_protection_for_woocommerce_settings_general_options;
    private $extend_protection_for_woocommerce_settings_shipping_protection_options;

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
        $this->version           = $version;
        $this->extend_protection_for_woocommerce_settings_general_options               = get_option('extend_protection_for_woocommerce_general_settings');
        $this->extend_protection_for_woocommerce_settings_product_protection_options    = get_option('extend_protection_for_woocommerce_product_protection_settings');
        $this->extend_protection_for_woocommerce_settings_shipping_protection_options   = get_option('extend_protection_for_woocommerce_shipping_protection_settings');

        add_action('admin_menu', array($this, 'extend_admin_menu'), 50);
        add_action('admin_init', array($this, 'extend_protection_for_woocommerce_settings_page_init'));
        add_action('admin_enqueue_scripts', 'extend_protection_style');



        /* retrieve environment variables */
        $this->env          = $this->extend_protection_for_woocommerce_settings_general_options['extend_environment'] ?? 'sandbox';
        $this->sdk_url      = 'https://sdk.helloextend.com/extend-sdk-client/v1/extend-sdk-client.min.js';

        if( $this->env == 'sandbox'){
            $this->api_host = 'https://api-demo.helloextend.com';
            $this->store_id = $this->extend_protection_for_woocommerce_settings_general_options['extend_sandbox_store_id'] ?? null ;
            $this->api_key  = $this->extend_protection_for_woocommerce_settings_general_options['extend_sandbox_api_key'] ?? null ;
        }else {
            $this->api_host = 'https://api.helloextend.com';
            $this->store_id = $this->extend_protection_for_woocommerce_settings_general_options['extend_live_store_id'] ?? null ;
            $this->api_key  = $this->extend_protection_for_woocommerce_settings_general_options['extend_live_api_key'] ?? null ;
        }

        if($this->store_id){
            $this->api_host .= '/stores/' . $this->store_id ;
        }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
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

        wp_enqueue_style($this->extend_protection, plugin_dir_url(__FILE__) . 'css/extend-protection-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
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

        wp_enqueue_script($this->extend_protection, plugin_dir_url(__FILE__) . 'js/extend-protection-admin.js', array('jquery'), $this->version, false);

    }

    /**
     * Add menu items in the admin.
     */
    public function extend_admin_menu()
    {
        global $menu, $admin_page_hooks;

        //the extend menu has an icon defined here:
        $extend_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjA2IiBoZWlnaHQ9IjE2MyIgdmlld0JveD0iMCAwIDIwNiAxNjMiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0xMTAuNzg5IDMyLjczNkwxMzYuMTczIC0zLjgxNDdlLTA2SDE5Ny44ODhMMTQxLjc2IDY5LjEwOTJMMTEwLjc4OSAzMi43MzZaIiBmaWxsPSIjMDBDOUZGIi8+CjxwYXRoIGQ9Ik0xMTAuNzg5IDMyLjczNkwxMzYuMTczIC0zLjgxNDdlLTA2SDE5Ny44ODhMMTQxLjc2IDY5LjEwOTJMMTEwLjc4OSAzMi43MzZaIiBzdHJva2U9IndoaXRlIi8+CjxwYXRoIGQ9Ik0yMDUuMzQ1IDE2Mi42MTFIMTQxLjU2OEMxNDEuNTY4IDE2Mi42MTEgMTAzLjI0NyAxMTcuMDI5IDEwMS4xODggMTE0LjI5MkM5MS42ODY4IDEyNi45NTkgNjIuODI2NCAxNjIuNjExIDYyLjgyNjQgMTYyLjYxMUgwTDY5LjgzMzkgNzguMDUzMUwzLjIwNjQ0IDAuMDYyNDczM0g2Ni42MjY4TDIwNS4zNDUgMTYyLjYxMVoiIGZpbGw9IiMwMzMyQ0MiLz4KPHBhdGggZD0iTTIwNS4zNDUgMTYyLjYxMUgxNDEuNTY4QzE0MS41NjggMTYyLjYxMSAxMDMuMjQ3IDExNy4wMjkgMTAxLjE4OCAxMTQuMjkyQzkxLjY4NjggMTI2Ljk1OSA2Mi44MjY0IDE2Mi42MTEgNjIuODI2NCAxNjIuNjExSDBMNjkuODMzOSA3OC4wNTMxTDMuMjA2NDQgMC4wNjI0NzMzSDY2LjYyNjhMMjA1LjM0NSAxNjIuNjExWiIgc3Ryb2tlPSJ3aGl0ZSIvPgo8L3N2Zz4K';

        add_menu_page('Extend Protection', 'Extend', 'manage_options', 'extend', null, $extend_icon, '55.5');
        add_submenu_page('extend', 'Settings', 'Settings', 'manage_options', 'extend', 'extend_render_settings_page');
        add_submenu_page('extend', 'Documentation', 'Documentation', 'manage_options', 'extend-docs', 'extend_render_documentation_page');
        add_submenu_page('extend', 'About', 'About', 'manage_options', 'extend-about', 'extend_render_about_page');
        add_submenu_page('extend', 'Error Log', 'Error Log', 'manage_options', 'custom-error-log', 'extend_logger_admin', '50');

    }

    /*
        register all settings
        they will end up in wp_options table, option_name = extend_protection_for_woocommerce_settings
    */

    public function extend_protection_for_woocommerce_settings_page_init()
    {
        register_setting(
            'extend_protection_for_woocommerce_settings_general_option_group', // option_group
            'extend_protection_for_woocommerce_general_settings', // option_name
            array($this, 'extend_protection_for_woocommerce_settings_sanitize') // sanitize_callback
        );
        register_setting(
            'extend_protection_for_woocommerce_settings_product_protection_option_group', // option_group
            'extend_protection_for_woocommerce_product_protection_settings', // option_name
            array($this, 'extend_protection_for_woocommerce_settings_sanitize') // sanitize_callback
        );
        register_setting(
            'extend_protection_for_woocommerce_settings_shipping_protection_option_group', // option_group
            'extend_protection_for_woocommerce_shipping_protection_settings', // option_name
            array($this, 'extend_protection_for_woocommerce_settings_sanitize') // sanitize_callback
        );

        /* build sections. note after v6.1.0, add_settings_section allows for extra parameters */

        global $wp_version;
        if (version_compare($wp_version, '6.1.0') >= 0) {
            add_settings_section(
                'extend_setting_environment_section',
                'Environment and Authentication',
                array($this, 'extend_setting_environment_section_info'),
                'extend-protection-for-woocommerce-settings-admin-general',
                array(
                    'before_section'    => '<div style="margin-top:40px;">',
                    'after_section'     => '</div>', //html for after the section
                )
            );

            add_settings_section(
                'extend_protection_for_woocommerce_settings_setting_section', // id
                'Product Protection Settings', // title
                array($this, 'extend_protection_for_woocommerce_settings_section_info'), // callback
                'extend-protection-for-woocommerce-settings-admin-product-protection', // page
                array(
                    'before_section'    => '<div style="margin-top:40px;">',
                    'after_section'     => '</div>', //html for after the section
                )
            );

            add_settings_section(
                'extend_setting_contract_section',
                'Product Protection Contracts',
                array($this, 'extend_setting_contract_section_info'),
                'extend-protection-for-woocommerce-settings-admin-product-protection',
                array(
                    'before_section'    => '<div style="margin-top:40px;">',
                    'after_section'     => '</div>', //html for after the section
                )
            );

            add_settings_section(
                'extend_setting_shipping_protection_section',
                'Shipping Protection Settings',
                array($this, 'extend_setting_shipping_protection_section_info'),
                'extend-protection-for-woocommerce-settings-admin-shipping-protection',
                array(
                    'before_section'    => '<div style="margin-top:40px;">',
                    'after_section'     => '</div>', //html for after the section
                )
            );

        }else{
            //older versions will not have margin-top
            add_settings_section(
                'extend_protection_for_woocommerce_settings_setting_section',
                'Product Protection Settings',
                array($this, 'extend_protection_for_woocommerce_settings_section_info'),
                'extend-protection-for-woocommerce-settings-admin-product-protection'
            );

            add_settings_section(
                'extend_setting_contract_section',
                'Product Protection Contracts',
                array($this, 'extend_setting_contract_section_info'),
                'extend-protection-for-woocommerce-settings-admin-product-protection'
            );

            add_settings_section(
                'extend_setting_environment_section',
                'Environment and Authentication',
                array($this, 'extend_setting_environment_section_info'),
                'extend-protection-for-woocommerce-settings-admin-general'
            );

            add_settings_section(
                'extend_setting_shipping_protection_section',
                'Shipping Protection Settings',
                array($this, 'extend_setting_shipping_protection_section_info'),
                'extend-protection-for-woocommerce-settings-admin-shipping-protection'
            );

        }

        /* build fields */

        /* product protection */

         add_settings_field(
             'enable_extend', // id
             'Enable Product Protection', // title
             array($this, 'enable_extend_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-product-protection', // page
             'extend_protection_for_woocommerce_settings_setting_section' // section
         );

         add_settings_field(
             'extend_enable_cart_offers', // id
             'Enable Cart Offers', // title
             array($this, 'extend_enable_cart_offers_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-product-protection', // page
             'extend_protection_for_woocommerce_settings_setting_section' // section
         );

         add_settings_field(
             'extend_enable_cart_balancing', // id
             'Enable Cart Balancing	', // title
             array($this, 'extend_enable_cart_balancing_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-product-protection', // page
             'extend_protection_for_woocommerce_settings_setting_section' // section
         );

         add_settings_field(
             'extend_enable_pdp_offers', // id
             'Enable PDP Offers	', // title
             array($this, 'extend_enable_pdp_offers_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-product-protection', // page
             'extend_protection_for_woocommerce_settings_setting_section' // section
         );

         add_settings_field(
             'extend_pdp_offer_location', // id
             'PDP Offer Location', // title
             array($this, 'extend_pdp_offer_location_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-product-protection', // page
             'extend_protection_for_woocommerce_settings_setting_section' // section
         );

         add_settings_field(
             'extend_enable_modal_offers', // id
             'Enable Modal Offers', // title
             array($this, 'extend_enable_modal_offers_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-product-protection', // page
             'extend_protection_for_woocommerce_settings_setting_section' // section
         );

         add_settings_field(
             'extend_automated_product_sync', // id
             'Automated Product Sync', // title
             array($this, 'extend_automated_product_sync_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-product-protection', // page
             'extend_protection_for_woocommerce_settings_setting_section' // section
         );

         add_settings_field(
             'extend_product_protection_contract_create', // id
             'Create Contracts', // title
             array($this, 'extend_product_protection_contract_create_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-product-protection', // page
             'extend_setting_contract_section' // section
         );

         add_settings_field(
             'extend_product_protection_contract_create_event', // id
             'Contracts Event', // title
             array($this, 'extend_product_protection_contract_create_event_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-product-protection', // page
             'extend_setting_contract_section' // section
         );

         /* general settings */

         add_settings_field(
             'extend_environment', // id
             'Environment', // title
             array($this, 'extend_environment_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-general', // page
             'extend_setting_environment_section' // section
         );

         add_settings_field(
             'extend_sandbox_store_id', // id
             'Extend Sandbox Store Id', // title
             array($this, 'extend_sandbox_store_id_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-general', // page
             'extend_setting_environment_section' // section
         );

         add_settings_field(
             'extend_sandbox_api_key', // id
             'Extend Sandbox API Key', // title
             array($this, 'extend_sandbox_api_key_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-general', // page
             'extend_setting_environment_section' // section
         );

         add_settings_field(
             'extend_live_store_id', // id
             'Extend Live Store Id', // title
             array($this, 'extend_live_store_id_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-general', // page
             'extend_setting_environment_section' // section
         );

         add_settings_field(
             'extend_live_api_key', // id
             'Extend Live API Key', // title
             array($this, 'extend_live_api_key_callback'), // callback
             'extend-protection-for-woocommerce-settings-admin-general', // page
             'extend_setting_environment_section' // section
         );

        add_settings_field(
            'enable_extend_debug', // id
            'Enable Debugging Log', // title
            array($this, 'enable_extend_debug_callback'), // callback
            'extend-protection-for-woocommerce-settings-admin-general', // page
            'extend_setting_environment_section' // section
        );

        /*  shipping protection  */

        add_settings_field(
            'enable_extend_sp', // id
            'Enable Shipping Protection', // title
            array($this, 'enable_extend_sp_callback'), // callback
            'extend-protection-for-woocommerce-settings-admin-shipping-protection', // page
            'extend_setting_shipping_protection_section' // section
        );

        add_settings_field(
            'enable_extend_sp', // id
            'Enable Shipping Protection', // title
            array($this, 'enable_extend_sp_callback'), // callback
            'extend-protection-for-woocommerce-settings-admin-shipping-protection', // page
            'extend_setting_shipping_protection_section' // section
        );

        add_settings_field(
            'extend_sp_offer_location', // id
            'Offer Location', // title
            array($this, 'extend_sp_offer_location_callback'), // callback
            'extend-protection-for-woocommerce-settings-admin-shipping-protection', // page
            'extend_setting_shipping_protection_section' // section
        );

         //once options have been registered, initialize values in the db:

         if (get_option('extend_protection_for_woocommerce_general_settings') == null ){
             $settings = [
                 'enable_extend_debug'      => '0',
                 'extend_environment'       => 'sandbox',
                 'extend_sandbox_store_id'  => '',
                 'extend_live_store_id'     => '',
                 'extend_sandbox_api_key'   => '',
                 'extend_live_api_key'      => ''

             ];
             update_option('extend_protection_for_woocommerce_general_settings', $settings);
         }

        if (get_option('extend_protection_for_woocommerce_product_protection_settings') == null ){
            $settingsPP = [
                'enable_extend'                 => '1',
                'extend_enable_cart_offers'     => '1',
                'extend_enable_modal_offers'    => '1',
                'extend_enable_cart_balancing'  => '1',
                'extend_enable_pdp_offers'      => '1',
                'extend_pdp_offer_location'     => 'woocommerce_before_add_to_cart_button'
            ];
            update_option('extend_protection_for_woocommerce_product_protection_settings', $settingsPP);
        }

        if (get_option('extend_protection_for_woocommerce_shipping_protection_settings') == null ){
            $settingsSP = [
                'enable_extend_sp'          => '1',
                'enable_sp_offer_location'  => 'woocommerce_review_order_before_payment',
                'enable_sp_offer_location_other'  => ''
            ];
            update_option('extend_protection_for_woocommerce_shipping_protection_settings', $settingsSP);
        }
     }

         /* sanitize all the fields before saving */

    public function extend_protection_for_woocommerce_settings_sanitize($input)
    {
        $sanitary_values = array();
        if (isset($input['enable_extend'])) {
            $sanitary_values['enable_extend'] = $input['enable_extend'];
        }

        if (isset($input['enable_extend_sp'])) {
            $sanitary_values['enable_extend_sp'] = $input['enable_extend_sp'];
        }

        if (isset($input['enable_extend_debug'])) {
            $sanitary_values['enable_extend_debug'] = $input['enable_extend_debug'];
        }

        if (isset($input['extend_enable_cart_offers'])) {
            $sanitary_values['extend_enable_cart_offers'] = $input['extend_enable_cart_offers'];
        }

        if (isset($input['extend_enable_cart_balancing'])) {
            $sanitary_values['extend_enable_cart_balancing'] = $input['extend_enable_cart_balancing'];
        }

        if (isset($input['extend_enable_pdp_offers'])) {
            $sanitary_values['extend_enable_pdp_offers'] = $input['extend_enable_pdp_offers'];
        }

        if (isset($input['extend_enable_modal_offers'])) {
            $sanitary_values['extend_enable_modal_offers'] = $input['extend_enable_modal_offers'];
        }

        if (isset($input['extend_automated_product_sync'])) {
            $sanitary_values['extend_automated_product_sync'] = $input['extend_automated_product_sync'];
        }

        if (isset($input['extend_pdp_offer_location'])) {
            $sanitary_values['extend_pdp_offer_location'] = $input['extend_pdp_offer_location'];
        }

        if (isset($input['extend_pdp_offer_location_other'])) {
            $sanitary_values['extend_pdp_offer_location_other'] = $input['extend_pdp_offer_location_other'];
        }

        if (isset($input['extend_sp_offer_location'])) {
            $sanitary_values['extend_sp_offer_location'] = $input['extend_sp_offer_location'];
        }

        if (isset($input['extend_sp_offer_location_other'])) {
            $sanitary_values['extend_sp_offer_location_other'] = $input['extend_sp_offer_location_other'];
        }

        if (isset($input['extend_product_protection_contract_create'])) {
            $sanitary_values['extend_product_protection_contract_create'] = $input['extend_product_protection_contract_create'];
        }

        if (isset($input['extend_product_protection_contract_create_event'])) {
            $sanitary_values['extend_product_protection_contract_create_event'] = $input['extend_product_protection_contract_create_event'];
        }

        if (isset($input['extend_environment'])) {
            $sanitary_values['extend_environment'] = $input['extend_environment'];
        }

        if (isset($input['extend_sandbox_store_id'])) {
            $sanitary_values['extend_sandbox_store_id'] = sanitize_text_field($input['extend_sandbox_store_id']);
        }

        if (isset($input['extend_sandbox_api_key']))
        {
            $sanitary_values['extend_sandbox_api_key'] = sanitize_textarea_field($input['extend_sandbox_api_key']);
        }

        if (isset($input['extend_live_store_id'])) {
            $sanitary_values['extend_live_store_id'] = sanitize_text_field($input['extend_live_store_id']);
        }

        if (isset($input['extend_live_api_key'])) {
            $sanitary_values['extend_live_api_key'] = sanitize_textarea_field($input['extend_live_api_key']);
        }

        return $sanitary_values;
    }

    public function extend_protection_for_woocommerce_settings_section_info()
    {
        echo "<hr>";
    }

    /* all callback functions for registering fields and displaying them with their saved values */

    public function enable_extend_callback()
    {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_product_protection_settings[enable_extend]" id="enable_extend" value="1" %s>',
            (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['enable_extend'])
                    && $this->extend_protection_for_woocommerce_settings_product_protection_options['enable_extend'] === '1') ? 'checked' : ''
        );
    }

    public function enable_extend_sp_callback()
    {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_shipping_protection_settings[enable_extend_sp]" id="enable_extend_sp" value="1" %s>',
            (isset($this->extend_protection_for_woocommerce_settings_shipping_protection_options['enable_extend_sp'])
                    && $this->extend_protection_for_woocommerce_settings_shipping_protection_options['enable_extend_sp'] === '1') ? 'checked' : ''
        );
    }

    public function extend_enable_cart_offers_callback()
    {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_product_protection_settings[extend_enable_cart_offers]" 
                           id="extend_enable_cart_offers" value="1" %s> <label for="extend_enable_cart_offers">Display protection offers in the cart</label>',
            (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_enable_cart_offers'])
                     && $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_enable_cart_offers'] === '1') ? 'checked' : ''
        );
    }

    public function extend_enable_cart_balancing_callback()
    {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_product_protection_settings[extend_enable_cart_balancing]" 
                           id="extend_enable_cart_balancing" value="1" %s> <label for="extend_enable_cart_balancing">Automatically adjust quantities</label>',
            (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_enable_cart_balancing'])
                    && $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_enable_cart_balancing'] === '1') ? 'checked' : ''
        );
    }

    public function extend_enable_pdp_offers_callback()
    {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_product_protection_settings[extend_enable_pdp_offers]" 
                           id="extend_enable_pdp_offers" value="1" %s> <label for="extend_enable_pdp_offers">Display offers on product page</label>',
            (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_enable_pdp_offers'])
                    && $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_enable_pdp_offers'] === '1') ? 'checked' : ''
        );
    }

    public function extend_enable_modal_offers_callback()
    {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_product_protection_settings[extend_enable_modal_offers]" 
                           id="extend_enable_modal_offers" value="1" %s> <label for="extend_enable_modal_offers">Display offers in a modal (PDP and cart)</label>' ,
            (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_enable_modal_offers'])
                    && $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_enable_modal_offers'] === '1') ? 'checked' : ''
        );
    }

    public function extend_automated_product_sync_callback()
    {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_product_protection_settings[extend_automated_product_sync]" 
                           id="extend_automated_product_sync" value="1" %s> <label for="extend_automated_product_sync">Automatically sync your catalog with Extend (for warranty mapping)</label>',
            (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_automated_product_sync'])
                    && $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_automated_product_sync'] === '1') ? 'checked' : ''
        );
    }

    public function extend_pdp_offer_location_callback()
    {
        $extend_pdp_offer_dropdown_values = array('woocommerce_before_add_to_cart_form', 'woocommerce_before_variations_form',
            'woocommerce_before_add_to_cart_button', 'woocommerce_before_single_variation', 'woocommerce_single_variation',
            'woocommerce_before_add_to_cart_quantity', 'woocommerce_after_add_to_cart_quantity', 'woocommerce_after_single_variation',
            'woocommerce_after_add_to_cart_button', 'woocommerce_after_variations_form', 'woocommerce_after_add_to_cart_form',
            'woocommerce_product_meta_start', 'woocommerce_product_meta_end', 'woocommerce_share', 'other');

        ?>
        <select name="extend_protection_for_woocommerce_product_protection_settings[extend_pdp_offer_location]" id="extend_pdp_offer_location">
            <?php
            //set default value if option is not set yet
            if (!isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_pdp_offer_location'])){
                $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_pdp_offer_location']='woocommerce_before_add_to_cart_button';
            }

            //build dropdown from array of possible hooks
            foreach($extend_pdp_offer_dropdown_values as $extend_pdp_hooks){
                $selected = (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_pdp_offer_location'])
                            && $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_pdp_offer_location'] === $extend_pdp_hooks ) ? 'selected' : '';

                if ($extend_pdp_hooks == 'woocommerce_before_add_to_cart_button'){
                    echo '<option value="' . $extend_pdp_hooks . '" ' . $selected . '>' . $extend_pdp_hooks . ' (default)</option>';
                }
                else {
                    echo '<option value="' . $extend_pdp_hooks . '" ' . $selected . '>' . $extend_pdp_hooks . '</option>';
                }
            }
            ?>
        </select>
        <?php
        //show information in a popup
        echo  '<label for="extend_pdp_offer_location"><a href="?page=extend-docs#offer_placement">What\'s this ?</a></label>';

        // logic for "other" option selected
        if ($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_pdp_offer_location'] === 'other'){
            $current_value                      = get_option('extend_protection_for_woocommerce_product_protection_settings');
            $extend_pdp_offer_location_other    = $current_value['extend_pdp_offer_location_other'];

            add_settings_field(
                'extend_pdp_offer_location_other', // id
                'Offer Location', // title
                array($this, 'extend_pdp_offer_location_callback'), // callback
                'extend-protection-for-woocommerce-settings-admin-product-protection', // page
                'extend_setting_product_protection_section' // section
            );
            echo '<br /><input type = "text" class = "extend_pdp_offer_location_other" id = "extend_pdp_offer_location_other" 
                        name  = "extend_protection_for_woocommerce_product_protection_settings[extend_pdp_offer_location_other]" 
                        value = "' . esc_attr($extend_pdp_offer_location_other) . '" placeholder = "Enter your custom value" />';
            echo  '<label for = "extend_pdp_offer_location_other"> Please enter a valid PDP layout hook</label>';
        }
    }

    public function extend_sp_offer_location_callback()
    {
        $extend_sp_offer_dropdown_values = array('woocommerce_before_checkout_billing_form', 'woocommerce_after_checkout_billing_form',
            'woocommerce_review_order_before_shipping', 'woocommerce_review_order_after_shipping',
            'woocommerce_review_order_before_order_total', 'woocommerce_review_order_after_order_total', 'woocommerce_review_order_before_payment',
            'woocommerce_review_order_before_submit', 'other');
        ?>
        <select name="extend_protection_for_woocommerce_shipping_protection_settings[extend_sp_offer_location]" id="extend_sp_offer_location">
            <?php
            //set default value if option is not set yet
            if (!isset($this->extend_protection_for_woocommerce_settings_shipping_protection_options['extend_sp_offer_location'])) {
                $this->extend_protection_for_woocommerce_settings_shipping_protection_options['extend_sp_offer_location'] = 'woocommerce_review_order_before_payment';
            }

            //build dropdown from array of possible hooks
            foreach($extend_sp_offer_dropdown_values as $extend_sp_hooks){
                $selected = (isset($this->extend_protection_for_woocommerce_settings_shipping_protection_options['extend_sp_offer_location'])
                    && $this->extend_protection_for_woocommerce_settings_shipping_protection_options['extend_sp_offer_location'] === $extend_sp_hooks ) ? 'selected' : '';

                if ($extend_sp_hooks == 'woocommerce_review_order_before_payment'){
                    echo '<option value="' . $extend_sp_hooks . '" ' . $selected . '>' . $extend_sp_hooks . ' (default)</option>';
                }
                else {
                    echo '<option value="' . $extend_sp_hooks . '" ' . $selected . '>' . $extend_sp_hooks . '</option>';
                }

            }
            ?>
        </select>

        <?php
        //show information in a popup
        echo  '<label for="extend_sp_offer_location"><a href="?page=extend-docs#sp_offer_placement">What\'s this ?</a></label>';

        // logic for "other" option selected
        if ($this->extend_protection_for_woocommerce_settings_shipping_protection_options['extend_sp_offer_location'] === 'other') {
            $current_value                  = get_option('extend_protection_for_woocommerce_shipping_protection_settings');
            $extend_sp_offer_location_other = array_key_exists('extend_sp_offer_location_other', $current_value) ? $current_value['extend_sp_offer_location_other'] : '';

            add_settings_field(
                'extend_sp_offer_location_other', // id
                'Offer Location', // title
                array($this, 'extend_sp_offer_location_callback'), // callback
                'extend-protection-for-woocommerce-settings-admin-shipping-protection', // page
                'extend_setting_shipping_protection_section' // section
            );
            echo '<br /><input type = "text" class = "extend_sp_offer_location_other" id = "extend_sp_offer_location_other" 
                        name  = "extend_protection_for_woocommerce_shipping_protection_settings[extend_sp_offer_location_other]" 
                        value = "' . esc_attr($extend_sp_offer_location_other) . '" placeholder = "Enter your custom value" />';
            echo '<label for  = "extend_sp_offer_location_other"> Please enter a valid checkout layout hook</label>';
        }
    }


    public function extend_product_protection_contract_create_callback(){
        // show checkbox to create contracts
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_product_protection_settings[extend_product_protection_contract_create]" 
                           id="extend_product_protection_contract_create" value="1" %s> 
                    <label for="extend_product_protection_contract_create">Create Product Protection Contracts</label>' ,
            (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_product_protection_contract_create'])
                    && $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_product_protection_contract_create'] === '1') ? 'checked' : ''
        );
    }

    public function extend_product_protection_contract_create_event_callback(){
        ?>
        <select name="extend_protection_for_woocommerce_product_protection_settings[extend_product_protection_contract_create_event]"
                id="extend_product_protection_contract_create_event">
            <?php   $selected = (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_product_protection_contract_create_event'])
                    && $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_product_protection_contract_create_event'] === 'Order Create') ? 'selected' : ''; ?>
            <option value="Order Create" <?php echo $selected; ?>>Order Create</option>
            <?php   $selected = (isset($this->extend_protection_for_woocommerce_settings_product_protection_options['extend_product_protection_contract_create_event'])
                    && $this->extend_protection_for_woocommerce_settings_product_protection_options['extend_product_protection_contract_create_event'] === 'Fulfillment') ? 'selected' : ''; ?>
            <option value="Fulfillment" <?php echo $selected; ?>>Fulfillment</option>
        </select>
        <?php
    }

    public function extend_environment_callback()
    {
        ?>
        <select name="extend_protection_for_woocommerce_general_settings[extend_environment]" id="extend_environment">
        <?php   $selected = (isset($this->extend_protection_for_woocommerce_settings_general_options['extend_environment'])
                && $this->extend_protection_for_woocommerce_settings_general_options['extend_environment'] === 'sandbox') ? 'selected' : ''; ?>
        <option value="sandbox" <?php echo $selected; ?>>Sandbox</option>
        <?php   $selected = (isset($this->extend_protection_for_woocommerce_settings_general_options['extend_environment'])
                && $this->extend_protection_for_woocommerce_settings_general_options['extend_environment'] === 'live') ? 'selected' : ''; ?>
        <option value="live" <?php echo $selected; ?>>Live</option>
    </select>
        <?php
    }

    public function extend_sandbox_store_id_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="extend_protection_for_woocommerce_general_settings[extend_sandbox_store_id]" 
                           id="extend_sandbox_store_id" value="%s">',
            isset($this->extend_protection_for_woocommerce_settings_general_options['extend_sandbox_store_id'])
                    ? esc_attr($this->extend_protection_for_woocommerce_settings_general_options['extend_sandbox_store_id']) : ''
        );
    }

    public function extend_sandbox_api_key_callback()
    {
        printf(
            '<textarea class="regular-text" rows="5" name="extend_protection_for_woocommerce_general_settings[extend_sandbox_api_key]" 
                              id="extend_sandbox_api_key" >%s</textarea>',
            isset($this->extend_protection_for_woocommerce_settings_general_options['extend_sandbox_api_key'])
                    ? esc_attr($this->extend_protection_for_woocommerce_settings_general_options['extend_sandbox_api_key']) : ''
        );
    }

    public function extend_live_store_id_callback()
    {
        printf(
            '<input class="regular-text" type="text" name="extend_protection_for_woocommerce_general_settings[extend_live_store_id]" 
                           id="extend_live_store_id" value="%s">',
            isset($this->extend_protection_for_woocommerce_settings_general_options['extend_live_store_id'])
                    ? esc_attr($this->extend_protection_for_woocommerce_settings_general_options['extend_live_store_id']) : ''
        );
    }

    public function extend_live_api_key_callback()
    {
        printf(
            '<textarea class="regular-text" rows="5" name="extend_protection_for_woocommerce_general_settings[extend_live_api_key]" 
                              id="extend_live_api_key" >%s</textarea>',
            isset($this->extend_protection_for_woocommerce_settings_general_options['extend_live_api_key'])
                    ? esc_attr($this->extend_protection_for_woocommerce_settings_general_options['extend_live_api_key']) : ''
        );
    }

    public function enable_extend_debug_callback()
    {
        printf(
            '<input type="checkbox" name="extend_protection_for_woocommerce_general_settings[enable_extend_debug]" id="enable_extend_debug" value="1" %s>',
            (isset($this->extend_protection_for_woocommerce_settings_general_options['enable_extend_debug'])
                    && $this->extend_protection_for_woocommerce_settings_general_options['enable_extend_debug'] === '1') ? 'checked' : ''
        );
    }

    function extend_setting_contract_section_info() {
        echo "<hr>";
    }

    function extend_setting_environment_section_info() {
        echo "<hr>";
    }

    function extend_setting_shipping_protection_section_info() {
        echo "<hr>";
    }
}