<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://extend.com
 * @since             1.0.0
 * @package           Extend_Protection
 *
 * @wordpress-plugin
 * Plugin Name:       Extend Protection for Woocommerce
 * Plugin URI:        https://docs.extend.com/docs
 * Description:       Product Protection Done Right.
 * Version:           1.0.0
 * Author:            Extend
 * Author URI:        https://extend.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       extend-protection
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('EXTEND_PROTECTION_VERSION', '1.0.0');
define('EXTEND_PRODUCT_PROTECTION_SKU', 'extend-product-protection');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-extend-protection-activator.php
 */
function activate_extend_protection()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-extend-protection-activator.php';
    Extend_Protection_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-extend-protection-deactivator.php
 */
function deactivate_extend_protection()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-extend-protection-deactivator.php';
    Extend_Protection_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_extend_protection');
register_deactivation_hook(__FILE__, 'deactivate_extend_protection');

/* Actions */

/* extend logger */
add_action( 'plugins_loaded',  'extend_logger_constants' );
add_action( 'plugins_loaded', 'extend_logger_includes' );

/* item create */
add_action('init', 'extend_product_protection_create');

/* shipping protection fee management */
add_action('wp_ajax_add_shipping_protection_fee', 'add_shipping_protection_fee');
add_action('wp_ajax_nopriv_add_shipping_protection_fee', 'add_shipping_protection_fee');
add_action('wp_ajax_remove_shipping_protection_fee', 'remove_shipping_protection_fee');
add_action('wp_ajax_nopriv_remove_shipping_protection_fee', 'remove_shipping_protection_fee');
add_action( 'woocommerce_cart_calculate_fees', 'set_shipping_fee' );
add_action( 'woocommerce_checkout_update_order_meta', 'save_shipping_protection_quote_id', 10, 2 );

/* end add_action */


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-extend-protection.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_extend_protection()
{

    $plugin = new Extend_Protection();
    $plugin->run();

}

function extend_render_settings_page()
{
    if (!is_woocommerce_activated()) {
        Extend_Protection_Logger::extend_log_error('Extend Protection requires the WooCommerce plugin to be installed and active');
        echo '<div class="error"><p><strong>' . sprintf(esc_html__('Extend Protection requires the WooCommerce plugin to be installed and active. You can download %s here.', 'woocommerce-services'), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
    }

    echo '<div style="padding-top:30px">';
    echo ' <img src="' . plugins_url() . '/extend-protection/images/Extend_logo_slogan.svg" alt="Extend Logo with Slogan" style="width: 170px;">
			<p>Extend generates new revenue for your store, increases overall purchase conversions, and provides customers with streamlined product protection and peace of mind. <a href="https://extend.com/merchants">Learn more</a><br/>
            <a href="https://merchants.extend.com" class="button button-primary action action-extend-external" target="_blank">Set up my Extend account</a> or <a href="https://merchants.extend.com" class="extend-account-link" target="_blank"> I already have an Extend account, I\'m ready to edit my settings</a> </p>';
    echo '</div>';


    settings_errors(); ?>
<!-- begin tabs -->

    <div class="wrap">
    <h2>Extend Protection Settings</h2>
    <h2 class="nav-tab-wrapper">
        <a href="?page=extend&tab=general" class="nav-tab <?php echo (empty($_GET['tab']) || $_GET['tab'] === 'general') ? 'nav-tab-active' : ''; ?>">General Settings</a>
        <a href="?page=extend&tab=product_protection" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'product_protection') ? 'nav-tab-active' : ''; ?>">Product Protection</a>
        <a href="?page=extend&tab=shipping_protection" class="nav-tab <?php echo (isset($_GET['tab']) &&$_GET['tab'] === 'shipping_protection') ? 'nav-tab-active' : ''; ?>">Shipping Protection</a>
    </h2>
        <div class="tab-content">
            <?php
            $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

            switch ($current_tab) {
                case 'product_protection':
                    include_once('tabs/product-protection.php');
                    break;
                case 'shipping_protection':
                    include_once('tabs/shipping-protection.php');
                    break;
                default:
                    include_once('tabs/general-settings.php');
            }
            ?>
        </div>
    </div>

    <!-- end tabs -->
    <?php


}


function extend_render_about_page()
{

}

function extend_render_documentation_page()
{
    echo "<h2>Extend Purchase Protection Documentation</h2>";
    echo "<br/>";
    echo "<h3>Product Protection</h3>";


    wp_enqueue_script( 'jquery-ui-accordion' );



echo '
    <div class="accordion">
    <div>
        <h3><a href="#" id="offer_placement">1 - Understanding Offer Placement on PDP</a></h3>
        <div>
            <img src="'.plugins_url() . '/extend-protection/images/woocommerce_hooks.jpg'.'" >
        </div>
    </div>
    <div>
        <h3><a href="#" id="extend_2">2 - Second</a></h3>
        <div>Phasellus mattis tincidunt nibh.</div>
    </div>
    <div>
        <h3><a href="#" id="extend_3">3 - Third</a></h3>
        <div>Nam dui erat, auctor a, dignissim quis.</div>
    </div>
</div>
';

}

function extend_protection_style()
{
    // Register stylesheets
    wp_register_style('extend_protection_style', plugins_url('extend-protection/css/extend.css'));
    wp_enqueue_style('extend_protection_style');
}

/**
 * Check if WooCommerce is activated
 */
if (!function_exists('is_woocommerce_activated')) {
    function is_woocommerce_activated()
    {
        if (class_exists('woocommerce')) {
            return true;
        } else {
            return false;
        }
    }
}

/* links on the plugin definition */
if (!function_exists('extend_protection_links')) {
    function extend_protection_links($links, $file)
    {
        if (strpos($file, basename(__FILE__))) {
            $links[] = '<a href="https://www.extend.com/get-help" class="extend_support" title="Get Help"></a>';
            $links[] = '<a href="https://www.linkedin.com/company/helloextend" class="extend_linkedin" title="Follow us on LinkedIn"></a>';
        }
        return $links;
    }
}
add_filter('plugin_row_meta', 'extend_protection_links', 10, 2);


/*extend_logger */

if ( ! function_exists('write_log')) {
    function write_log ( $log )  {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}


/* local bypass of curl error ssl */
add_filter('https_ssl_verify', '__return_false');


function extend_product_protection_create()
{
    if (isset($_POST['extend-product-protection-create'])) {

        // delete if sku exists first
        $deleteProduct =  wc_get_product(extend_product_protection_id());
        if (empty($deleteProduct)){
            Extend_Protection_Logger::extend_log_notice( 'Create Extend product protection item product function was called, and product with sku '.EXTEND_PRODUCT_PROTECTION_SKU.' did not exist prior' );
        }else{
            Extend_Protection_Logger::extend_log_notice( 'Create Extend product protection item function was called, and product with sku '.EXTEND_PRODUCT_PROTECTION_SKU.' existed prior' );
            $deleteProduct->delete();
        }

        try {
            // create new
            $product = new WC_Product_Simple();
            $product->set_name('Extend Product Protection');
            $product->set_status('publish');
            $product->set_sku(EXTEND_PRODUCT_PROTECTION_SKU);
            $product->set_catalog_visibility('hidden');
            $product->set_price(1.00);
            $product->set_regular_price(1.00);
            $product->set_virtual(true);
            $product->save();
        }
        catch (\Exception $e){
            Extend_Protection_Logger::extend_log_error($e->getMessage());
        }

        //upload image and associate to product
        try {
            $product_id = $product->get_id();
            $upload = wc_rest_upload_image_from_url(plugins_url() . '/extend-protection/images/Extend_icon.png');
            $product_img_id = wc_rest_set_uploaded_image_as_attachment($upload, $product_id);
            $product->set_image_id($product_img_id);
            $product->save();
        }
        catch (\Exception $e){
            Extend_Protection_Logger::extend_log_error($e->getMessage());
        }
    }
}

/* extend logger */
function extend_logger_constants(){
    /* Set constant path to the plugin directory. */
    define( 'EXTEND_LOGGER_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

    /* Set constant path to the plugin URL. */
    define( 'EXTEND_LOGGER_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

function extend_logger_includes(){
    /* Include main admin file, this sets up the plugin's admin area */
    require_once( EXTEND_LOGGER_DIR . 'admin/extend_logger_admin.php');
}

function extend_product_protection_id(): ?int
{
    global $wpdb;

    $product_id = $wpdb->get_var(
        $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' ORDER BY meta_id DESC LIMIT 1",
            EXTEND_PRODUCT_PROTECTION_SKU)
    );

    if ($product_id)
        return $product_id;

    return null;
}

function add_shipping_protection_fee()
{
    if (  !defined( 'DOING_AJAX' ) || ! $_POST )  return;

    if (isset($_POST['fee_amount']) && isset($_POST['fee_label']))
    {
        $fee_amount = floatval(number_format($_POST['fee_amount']/100, 2));
        $fee_label  = sanitize_text_field($_POST['fee_label']);

        if ($fee_amount && $fee_label){
            WC()->session->set('shipping_fee',   true   );
            WC()->session->set('shipping_fee_value',   $fee_amount   );
            WC()->session->set('shipping_quote_id',    $_POST['shipping_quote_id']);
        }else{
            echo " No shipping protection fee added because of an error ";
        }
    }
    wp_die();
}

function remove_shipping_protection_fee()
{
    if (  !defined( 'DOING_AJAX' ) || ! $_POST )  return;

        WC()->session->set('shipping_fee_remove',   true);
        WC()->session->set('shipping_fee',          false);
        WC()->session->set('shipping_fee_value',    null);
        WC()->session->set('shipping_quote_id',     null);

    wp_die();
}

function set_shipping_fee(){
    if ( is_admin() && ! defined('DOING_AJAX') || ! is_checkout() )
        return;

    if ( 1 == WC()->session->get('shipping_fee') ) {

        $fee_label   =  "Shipping Protection" ;
        $fee_amount  = WC()->session->get('shipping_fee_value');

        WC()->cart->add_fee( $fee_label, $fee_amount );
    }
    else if (1 == WC()->session->get('shipping_fee_remove'))
    {
        $fees = WC()->cart->get_fees();
        foreach ($fees as $key => $fee) {
            if($fees[$key]->name === __( "Shipping Protection")) {
                unset($fees[$key]);
            }
        }
        WC()->cart->fees_api()->set_fees($fees);
        WC()->session->set('shipping_fee_remove',   false   );
    }
}


function save_shipping_protection_quote_id( $order_id ) {
    $settings =  get_option('extend_protection_for_woocommerce_general_settings');

    if ($settings['enable_extend_debug'] == 1) {
        Extend_Protection_Logger::extend_log_debug('Adding metadata for order id ' . $order_id . ' -> shipping_quote_id : ' . WC()->session->get('shipping_quote_id'));
    }

    if(WC()->session->get('shipping_quote_id') !== null) {
        update_post_meta( $order_id, '_shipping_protection_quote_id', sanitize_text_field( WC()->session->get('shipping_quote_id')));
    }
}


run_extend_protection();
