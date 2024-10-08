<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    http://extend.com
 * @since   1.0.0
 * @package HelloExtend_Protection
 *
 * @wordpress-plugin
 * Plugin Name:       Extend Protection For WooCommerce
 * Plugin URI:        https://docs.extend.com/docs/extend-protection-plugin-for-woocommerce
 * Description:       Extend Protection for Woocommerce. Allows WooCommerce merchants to offer product and shipping protection to their customers.
 * Version:           1.0.0
 * Author:            Extend, Inc.
 * Author URI:        https://extend.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       extend-protection-for-woocommerce
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
 * This action is documented in includes/class-helloextend-protection-activator.php
 */
function activate_helloextend_protection()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-helloextend-protection-activator.php';
    HelloExtend_Protection_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-helloextend-protection-deactivator.php
 */
function deactivate_helloextend_protection()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-helloextend-protection-deactivator.php';
    HelloExtend_Protection_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_helloextend_protection');
register_deactivation_hook(__FILE__, 'deactivate_helloextend_protection');

/* Actions */

/* extend logger */
add_action('plugins_loaded', 'extend_logger_constants');
add_action('plugins_loaded', 'extend_logger_includes');

/* item create */
add_action('init', 'extend_product_protection_create');

/* shipping protection fee management */
add_action('wp_ajax_add_shipping_protection_fee', 'add_shipping_protection_fee');
add_action('wp_ajax_nopriv_add_shipping_protection_fee', 'add_shipping_protection_fee');
add_action('wp_ajax_remove_shipping_protection_fee', 'remove_shipping_protection_fee');
add_action('wp_ajax_nopriv_remove_shipping_protection_fee', 'remove_shipping_protection_fee');
add_action('woocommerce_cart_calculate_fees', 'set_shipping_fee');
add_action('woocommerce_checkout_order_processed', 'save_shipping_protection_quote_id', 5, 2);

// Hook into WooCommerce order details display on admin screen
add_action('woocommerce_after_order_itemmeta', 'add_helloextend_protection_contract', 10, 2);

/* end add_action */


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-helloextend-protection.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_helloextend_protection()
{
    $plugin = new HelloExtend_Protection();
    $plugin->run();
}

function extend_render_settings_page()
{
    if (!is_woocommerce_activated()) {
        HelloExtend_Protection_Logger::extend_log_error('Extend Protection requires the WooCommerce plugin to be installed and active');
        echo '<div class="error"><p><strong>' . sprintf(__('Extend Protection requires the WooCommerce plugin to be installed and active. You can download %s here.', 'extend-protection'), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
    }

    echo '<div style="padding-top:30px">';
    echo ' <img src="' . esc_url(plugins_url() . '/extend-protection/images/Extend_logo_slogan.svg') . '" alt="Extend Logo with Slogan" style="width: 170px;">
			<p>Extend generates new revenue for your store, increases overall purchase conversions, and provides customers with streamlined product protection and peace of mind. <a href="https://extend.com/merchants">Learn more</a><br/>
            <a href="https://merchants.extend.com" class="button button-primary action action-extend-external" target="_blank">Set up my Extend account</a> or <a href="https://merchants.extend.com" class="extend-account-link" target="_blank"> I already have an Extend account, I\'m ready to edit my settings</a> </p>';
    echo '</div>';

    settings_errors(); ?>
	<!-- begin tabs -->

	<div class="wrap">
		<h2>Extend Protection Settings</h2>
		<h2 class="nav-tab-wrapper">
			<a href="?page=extend-protection-settings&tab=general" class="nav-tab <?php echo (empty($_GET['tab']) || sanitize_text_field($_GET['tab']) === 'general') ? 'nav-tab-active' : ''; ?>">General Settings</a>
			<a href="?page=extend-protection-settings&tab=product_protection" class="nav-tab <?php echo (isset($_GET['tab']) && sanitize_text_field($_GET['tab']) === 'product_protection') ? 'nav-tab-active' : ''; ?>">Product Protection</a>
			<a href="?page=extend-protection-settings&tab=shipping_protection" class="nav-tab <?php echo (isset($_GET['tab']) && sanitize_text_field($_GET['tab']) === 'shipping_protection') ? 'nav-tab-active' : ''; ?>">Shipping Protection</a>
			<a href="?page=extend-protection-settings&tab=catalog_sync" class="nav-tab <?php echo (isset($_GET['tab']) && sanitize_text_field($_GET['tab']) === 'catalog_sync') ? 'nav-tab-active' : ''; ?>">Catalog Sync</a>
		</h2>
		<div class="tab-content">
            <?php
            $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']): 'general';

            switch ($current_tab) {
                case 'product_protection':
                    include_once 'tabs/product-protection.php';
                    break;
                case 'shipping_protection':
                    include_once 'tabs/shipping-protection.php';
                    break;
                case 'catalog_sync':
                    include_once 'tabs/catalog-sync.php';
                    break;
                default:
                    include_once 'tabs/general-settings.php';
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
    echo '<h2>Extend Purchase Protection Documentation</h2>';
    echo '<br/>';
    echo '<h3>Product Protection</h3>';

    wp_enqueue_script('jquery-ui-accordion');

    echo '
    <div class="accordion">
        <div>
            <h3><a href="#" id="offer_placement">1 - Understanding Offer Placement on PDP</a></h3>
            <div>
                <img src="' . esc_url(plugins_url() . '/extend-protection/images/woocommerce_hooks.jpg') . '" >
            </div>
        </div>';
}

function helloextend_protection_style()
{
    // Register stylesheets
    wp_register_style('helloextend_protection_style', plugins_url('extend-protection/css/helloextend.css'));
    wp_enqueue_style('helloextend_protection_style');
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
if (!function_exists('helloextend_protection_links')) {

    function helloextend_protection_links($links, $file)
    {
        if (strpos($file, basename(__FILE__))) {
            $links[] = '<a href="https://www.extend.com/get-help" class="extend_support" title="Get Help"></a>';
            $links[] = '<a href="https://www.linkedin.com/company/helloextend" class="extend_linkedin" title="Follow us on LinkedIn"></a>';
        }
        return $links;
    }
}
add_filter('plugin_row_meta', 'helloextend_protection_links', 10, 2);


/*extend_logger */

if (!function_exists('write_log')) {

    function write_log($log)
    {
        if (is_array($log) || is_object($log)) {
            error_log(print_r($log, true));
        } else {
            error_log($log);
        }
    }
}


/* local bypass of curl error ssl */
add_filter('https_ssl_verify', '__return_false');

function extend_product_protection_create()
{
    if (isset($_POST['extend-product-protection-create'])) {

        // delete if sku exists first
        $deleteProduct = wc_get_product(extend_product_protection_id());
        if (empty($deleteProduct)) {
            HelloExtend_Protection_Logger::extend_log_notice('Create Extend product protection item product function was called, and product with sku ' . EXTEND_PRODUCT_PROTECTION_SKU . ' did not exist prior');
        } else {
            HelloExtend_Protection_Logger::extend_log_notice('Create Extend product protection item function was called, and product with sku ' . EXTEND_PRODUCT_PROTECTION_SKU . ' existed prior');
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
        } catch (\Exception $e) {
            HelloExtend_Protection_Logger::extend_log_error($e->getMessage());
        }

        // upload image and associate to product
        try {
            $product_id     = $product->get_id();
            //check if image exists
            if (file_exists(plugin_dir_path('images/Extend_icon.png'))) {

                $upload         = wc_rest_upload_image_from_url(plugins_url() . '/extend-protection/images/Extend_icon.png');
                if (is_wp_error($upload)) {
                    HelloExtend_Protection_Logger::extend_log_error('Could not upload extend logo from ' . plugins_url() . '/extend-protection/images/Extend_icon.png : ' . $upload->get_error_message());
                    return false;
                }

                $product_img_id = wc_rest_set_uploaded_image_as_attachment($upload, $product_id);
                if (is_wp_error($product_img_id)) {
                    HelloExtend_Protection_Logger::extend_log_error('Could not retrieve product image id : ');
                    return false;
                }

                //set the product image
                set_post_thumbnail($product_id, $product_img_id);
            } else {
                HelloExtend_Protection_Logger::extend_log_error('Extend_icon file path incorrect: ' . plugin_dir_path('images/Extend_icon.png'));
            }
        } catch (\Exception $e) {
            HelloExtend_Protection_Logger::extend_log_error($e->getMessage());
        }
    }
}

/* extend logger */
function extend_logger_constants()
{
    /* Set constant path to the plugin directory. */
    define('EXTEND_LOGGER_DIR', trailingslashit(plugin_dir_path(__FILE__)));

    /* Set constant path to the plugin URL. */
    define('EXTEND_LOGGER_URI', trailingslashit(plugin_dir_url(__FILE__)));
}

function extend_logger_includes()
{
    /* Include main admin file, this sets up the plugin's admin area */
    include_once EXTEND_LOGGER_DIR . 'admin/helloextend_logger_admin.php';
}

function extend_product_protection_id(): ?int
{
    global $wpdb;

    $product_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' ORDER BY meta_id DESC LIMIT 1",
            EXTEND_PRODUCT_PROTECTION_SKU
        )
    );

    if ($product_id) {
        return $product_id;
    }

    return null;
}

function add_shipping_protection_fee()
{
    if (!defined('DOING_AJAX') || !$_POST) {
        return;
    }

    if (isset($_POST['fee_amount']) && isset($_POST['fee_label'])) {
        $fee_amount = floatval(number_format( sanitize_text_field($_POST['fee_amount']) / 100, 2));
        $fee_label  = sanitize_text_field($_POST['fee_label']);

        if ($fee_amount && $fee_label) {
            WC()->session->set('shipping_fee', true);
            WC()->session->set('shipping_fee_value', $fee_amount);
            WC()->session->set('shipping_quote_id', sanitize_key($_POST['shipping_quote_id']));
        } else {
            echo ' No shipping protection fee added because of an error ';
        }
    }
    wp_die();
}

function remove_shipping_protection_fee()
{
    if (!defined('DOING_AJAX') || !$_POST) {
        return;
    }

    WC()->session->set('shipping_fee_remove', true);
    WC()->session->set('shipping_fee', false);
    WC()->session->set('shipping_fee_value', null);
    WC()->session->set('shipping_quote_id', null);

    wp_die();
}

function set_shipping_fee()
{
    if (is_admin() && !defined('DOING_AJAX') || !is_checkout()) {
        return;
    }

    $fee_label  = __('Extend Shipping Protection', 'extend-protection');

    if (1 == WC()->session->get('shipping_fee')) {

        $fee_amount = WC()->session->get('shipping_fee_value');

        WC()->cart->add_fee($fee_label, $fee_amount);
    } elseif (1 == WC()->session->get('shipping_fee_remove')) {
        $fees = WC()->cart->get_fees();
        foreach ($fees as $key => $fee) {
            if ($fees[$key]->name == $fee_label) {
                unset($fees[$key]);
            }
        }
        WC()->cart->fees_api()->set_fees($fees);
        WC()->session->set('shipping_fee_remove', false);
    }
}

function save_shipping_protection_quote_id($order_id)
{
    $settings = get_option('helloextend_protection_for_woocommerce_general_settings');

    if ($settings['enable_extend_debug'] == 1) {
        HelloExtend_Protection_Logger::extend_log_debug('Adding metadata for order id ' . $order_id . ' -> shipping_quote_id : ' . WC()->session->get('shipping_quote_id'));
    }

    if (WC()->session->get('shipping_quote_id') !== null) {
        update_post_meta($order_id, '_shipping_protection_quote_id', sanitize_text_field(WC()->session->get('shipping_quote_id')));
    }
}

/*
 *  display the contract information for relevant items in the admin order
*/

function add_helloextend_protection_contract($item_id, $item)
{
    // Get the order object && the contracts meta if any
    $order            = $item->get_order();
    $contracts        = get_post_meta($order->get_id(), '_product_protection_contracts', true);
    $extend_meta_data = (array) $item->get_meta('_extend_data');

    if (is_array($contracts)) {
        $settings = get_option('helloextend_protection_for_woocommerce_general_settings');
        $env      = $settings['extend_environment'] ?? 'sandbox';

        if ($env == 'sandbox') {
            $url         = 'https://customers.demo.extend.com/en-US/warranty_terms';
        } else {
            $url         = 'https://customers.extend.com/en-US/warranty_terms';
        }

        $token = HelloExtend_Protection_Global::get_extend_token();

        // Get product object
        if (method_exists($item, 'get_product')) {
            $product = $item->get_product();

            // Check if the product SKU matches product protection
            if ($product->get_sku() === 'extend-product-protection') {
                echo '<table cellspacing="0" class="display_meta"><tbody><tr><th>Extend Product Protection contracts : </th><th></th></tr>';

                foreach ($contracts as $product_covered => $contract_id) {
                    if ($extend_meta_data['covered_product_id'] == $product_covered) {
                        echo '<tr><td><a href="' . esc_url($url . '?contractId=' . $contract_id . '&accessToken=' . $token) . '">' . $contract_id . '</a></td></tr>';
                    }
                }
                echo '</tbody></table>';
            }
        }
    }
}

run_helloextend_protection();
