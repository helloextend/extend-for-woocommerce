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
 * Text Domain:       helloextend-protection
 * Domain Path:       /languages
 */


// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}



/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('HELLOEXTEND_PROTECTION_VERSION', '1.1.0');
define('HELLOEXTEND_PRODUCT_PROTECTION_SKU', 'helloextend-product-protection');
define('HELLOEXTEND_SHIPPING_PROTECTION_SKU', 'helloextend-shipping-protection');


define( 'HELLOEXTEND_PLUGIN_FILE', __FILE__ );
define( 'HELLOEXTEND_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HELLOEXTEND_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-helloextend-protection-activator.php
 */
function helloextend_activate()
{
	include_once HELLOEXTEND_PLUGIN_DIR . 'includes/class-helloextend-protection-activator.php';
    HelloExtend_Protection_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-helloextend-protection-deactivator.php
 */
function helloextend_deactivate()
{
    include_once HELLOEXTEND_PLUGIN_DIR . 'includes/class-helloextend-protection-deactivator.php';
    HelloExtend_Protection_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'helloextend_activate');
register_deactivation_hook(__FILE__, 'helloextend_deactivate');

/* Actions */

/* extend logger */
add_action('plugins_loaded', 'helloextend_logger_constants');
add_action('plugins_loaded', 'helloextend_logger_includes');

/* item create */
add_action('init', 'helloextend_product_protection_create');

/* shipping protection fee management */
add_action('wp_ajax_add_shipping_protection_fee', 'helloextend_add_shipping_protection_fee');
add_action('wp_ajax_nopriv_add_shipping_protection_fee', 'helloextend_add_shipping_protection_fee');
add_action('wp_ajax_remove_shipping_protection_fee', 'helloextend_remove_shipping_protection_fee');
add_action('wp_ajax_nopriv_remove_shipping_protection_fee', 'helloextend_remove_shipping_protection_fee');
add_action('woocommerce_cart_calculate_fees', 'helloextend_set_shipping_fee');
add_action('woocommerce_checkout_order_processed', 'helloextend_save_shipping_protection_quote_id', 5, 2);

// Hook into WooCommerce order details display on admin screen
add_action('woocommerce_after_order_itemmeta', 'helloextend_add_protection_contract', 10, 2);

// Hook into new category page
add_action('product_cat_add_form_fields', 'helloextend_add_ignore_product_category_field', 10);
// Hook into edit category page
add_action('product_cat_edit_form_fields', 'helloextend_edit_ignore_product_category_field', 10);

// Save when category is saved
add_action('created_term', 'helloextend_save_category', 10, 1);
add_action('edited_term', 'helloextend_save_category', 10, 1);

//add email paragraph for SP
add_action('woocommerce_email_before_order_table', 'helloextend_add_protection_message_to_email', 10, 4);

// Add text to the order received (thank you) page
add_action('woocommerce_thankyou', 'helloextend_add_protection_message_to_thankyou_page', 20);

/* end add_action */


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require HELLOEXTEND_PLUGIN_DIR . 'includes/class-helloextend-protection.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function helloextend_run()
{
    $plugin = new HelloExtend_Protection();
    $plugin->run();
}

function helloextend_render_settings_page()
{
    if (!helloextend_is_woocommerce_activated()) {
        $allowedtags = array(
	        'a' => array(
		        'href' => true,
		        'target' => true
            )
        );

        HelloExtend_Protection_Logger::helloextend_log_error('Extend Protection requires the WooCommerce plugin to be installed and active');
	    /* translators: woocommerce download link. */
        echo '<div class="error"><p><strong>' . wp_kses(sprintf('Extend Protection requires the WooCommerce plugin to be installed and active. You can download %s here.' , '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'), $allowedtags) . '</strong></p></div>';
    }

    echo '<div style="padding-top:30px">';
    echo ' <img src="' . esc_url(HELLOEXTEND_PLUGIN_URL . '/images/Extend_logo_slogan.svg') . '" alt="Extend Logo with Slogan" style="width: 170px;">
			<p>Extend generates new revenue for your store, increases overall purchase conversions, and provides customers with streamlined product protection and peace of mind. <a href="https://extend.com/merchants">Learn more</a><br/>
            <a href="https://merchants.extend.com" class="button button-primary action action-extend-external" target="_blank">Set up my Extend account</a> or <a href="https://merchants.extend.com" class="helloextend-account-link" target="_blank"> I already have an Extend account, I\'m ready to edit my settings</a> </p>';
    echo '</div>';

    settings_errors(); ?>
	<!-- begin tabs -->

	<div class="wrap">
		<h2>Extend Protection Settings</h2>
		<h2 class="nav-tab-wrapper">
			<a href="?page=helloextend-protection-settings&tab=general" class="nav-tab <?php echo (empty($_GET['tab']) || sanitize_text_field(wp_unslash($_GET['tab'])) === 'general') ? 'nav-tab-active' : ''; ?>">General Settings</a>
			<a href="?page=helloextend-protection-settings&tab=product_protection" class="nav-tab <?php echo (isset($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])) === 'product_protection') ? 'nav-tab-active' : ''; ?>">Product Protection</a>
			<a href="?page=helloextend-protection-settings&tab=shipping_protection" class="nav-tab <?php echo (isset($_GET['tab']) && sanitize_text_field(wp_unslash($_GET['tab'])) === 'shipping_protection') ? 'nav-tab-active' : ''; ?>">Shipping Protection</a>
		</h2>
		<div class="tab-content">
            <?php
            $current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])): 'general';

            switch ($current_tab) {
                case 'product_protection':
                    include_once 'tabs/product-protection.php';
                    break;
                case 'shipping_protection':
                    include_once 'tabs/shipping-protection.php';
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

function helloextend_render_about_page()
{
}

function helloextend_render_documentation_page()
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
               <img src="' . esc_url(HELLOEXTEND_PLUGIN_URL . '/images/woocommerce_hooks.jpg') . '" >
            </div>
    </div>';
}

function helloextend_protection_style()
{
    // Register stylesheets
	$lastmodtime= filemtime(HELLOEXTEND_PLUGIN_URL. 'css/helloextend.css');
    wp_register_style('helloextend_protection_style',  HELLOEXTEND_PLUGIN_URL.'css/helloextend.css', array(), $lastmodtime);
    wp_enqueue_style('helloextend_protection_style');
}

/**
 * Check if WooCommerce is activated
 */
if (!function_exists('helloextend_is_woocommerce_activated')) {

    function helloextend_is_woocommerce_activated()
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
            $links[] = '<a href="https://www.extend.com/get-help" class="helloextend_support" title="Get Help"></a>';
            $links[] = '<a href="https://www.linkedin.com/company/helloextend" class="helloextend_linkedin" title="Follow us on LinkedIn"></a>';
        }
        return $links;
    }
}
add_filter('plugin_row_meta', 'helloextend_protection_links', 10, 2);


/*extend_logger */

if (!function_exists('helloextend_write_log')) {

    function helloextend_write_log($log)
    {
	    // phpcs:disable WordPress.PHP.DevelopmentFunctions
	    if (is_array($log) || is_object($log)) {
	        error_log(print_r($log, true));
        } else {
            error_log($log);
        }
	    // phpcs:enable
    }
}


/* local bypass of curl error ssl */
add_filter('https_ssl_verify', '__return_false');

function helloextend_product_protection_create()
{
    if (isset($_POST['helloextend-product-protection-create'])) {

        // delete if sku exists first
        $deleteProduct = wc_get_product(helloextend_product_protection_id());
        if (empty($deleteProduct)) {
            HelloExtend_Protection_Logger::helloextend_log_notice('Create Extend product protection item product function was called, and product with sku ' . HELLOEXTEND_PRODUCT_PROTECTION_SKU . ' did not exist prior');
        } else {
            HelloExtend_Protection_Logger::helloextend_log_notice('Create Extend product protection item function was called, and product with sku ' . HELLOEXTEND_PRODUCT_PROTECTION_SKU . ' existed prior');
            $deleteProduct->delete();
        }

        try {
            // create new
            $product = new WC_Product_Simple();
            $product->set_name('Extend Product Protection');
            $product->set_status('publish');
            $product->set_sku(HELLOEXTEND_PRODUCT_PROTECTION_SKU);
            $product->set_catalog_visibility('hidden');
            $product->set_price(1.00);
            $product->set_regular_price(1.00);
            $product->set_virtual(true);
            $product->save();
        } catch (\Exception $e) {
            HelloExtend_Protection_Logger::helloextend_log_error($e->getMessage());
        }

        try {
            $product_id = $product->get_id();
            $image_path = HELLOEXTEND_PLUGIN_DIR . 'images/Extend_icon.png';
            $image_url  = HELLOEXTEND_PLUGIN_URL . 'images/Extend_icon.png';

            if (file_exists($image_path)) {
                $upload = wc_rest_upload_image_from_url($image_url);

                if (is_wp_error($upload)) {
                    HelloExtend_Protection_Logger::helloextend_log_error('Could not upload Extend logo from ' . $image_url . ' : ' . $upload->get_error_message());
                    return false;
                }

                $product_img_id = wc_rest_set_uploaded_image_as_attachment($upload, $product_id);
                if (is_wp_error($product_img_id)) {
                    HelloExtend_Protection_Logger::helloextend_log_error('Could not retrieve product image ID.');
                    return false;
                }

                set_post_thumbnail($product_id, $product_img_id);
            } else {
                HelloExtend_Protection_Logger::helloextend_log_error('Extend_icon file path incorrect: ' . $image_path);
            }
        } catch (\Exception $e) {
            HelloExtend_Protection_Logger::helloextend_log_error($e->getMessage());
        }
    }
}

function helloextend_get_or_create_shipping_protection_product($fee_amount) {
    $query = new WP_Query([
        'post_type'  => 'product',
        'meta_key'   => '_helloextend_shipping_protection_product',
        'meta_value' => '1',
        'post_status'=> 'any',
        'numberposts'=> 1
    ]);

    if (!empty($query->posts)) {
        $product_id = $query->posts[0]->ID;
        update_post_meta($product_id, '_price', $fee_amount);
        update_post_meta($product_id, '_regular_price', $fee_amount);
        return $product_id;
    }

    $product_id = wp_insert_post([
        'post_title'   => 'Extend Shipping Protection',
        'post_content' => 'Optional shipping protection offered at checkout.',
        'post_status'  => 'publish',
        'post_type'    => 'product',
    ]);

    if (!is_wp_error($product_id)) {
        update_post_meta($product_id, '_virtual', 'yes');
        update_post_meta($product_id, '_price', $fee_amount);
        update_post_meta($product_id, '_regular_price', $fee_amount);
        update_post_meta($product_id, '_visibility', 'hidden');
        update_post_meta($product_id, '_catalog_visibility', 'hidden');
        update_post_meta($product_id, '_helloextend_shipping_protection_product', 1);
        update_post_meta($product_id, '_sku', HELLOEXTEND_SHIPPING_PROTECTION_SKU);
        wp_set_object_terms($product_id, 'simple', 'product_type');

        // Upload image and associate to product
        $image_path = HELLOEXTEND_PLUGIN_DIR . 'images/Extend_shipping_icon.png';
        $image_url  = HELLOEXTEND_PLUGIN_URL . 'images/Extend_shipping_icon.png';

        if (file_exists($image_path)) {
            $upload         = wc_rest_upload_image_from_url($image_url);
            if (is_wp_error($upload)) {
                HelloExtend_Protection_Logger::helloextend_log_error('Could not upload extend logo from ' . $image_url . $upload->get_error_message());
                return false;
            }

            $product_img_id = wc_rest_set_uploaded_image_as_attachment($upload, $product_id);
            if (is_wp_error($product_img_id)) {
                HelloExtend_Protection_Logger::helloextend_log_error('Could not retrieve product image id : ');
                return false;
            }

            //set the product image
            set_post_thumbnail($product_id, $product_img_id);
        } else {
            HelloExtend_Protection_Logger::helloextend_log_error('Extend_icon file path incorrect: ' . $image_path);
        }

        return $product_id;
    }

    return false;
}

/* extend logger */
function helloextend_logger_constants()
{
    /* Set constant path to the plugin directory. */
    define('HELLOEXTEND_LOGGER_DIR', trailingslashit(HELLOEXTEND_PLUGIN_DIR));

    /* Set constant path to the plugin URL. */
    define('HELLOEXTEND_LOGGER_URI', trailingslashit(HELLOEXTEND_PLUGIN_URL));
}

function helloextend_logger_includes()
{
    /* Include main admin file, this sets up the plugin's admin area */
    include_once HELLOEXTEND_LOGGER_DIR . 'admin/helloextend_logger_admin.php';
}

function helloextend_product_protection_id(): ?int
{
    global $wpdb;
	// phpcs:disable WordPress.DB.DirectDatabaseQuery
	/* translators: Meta Value. */
    $product_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value=%s ORDER BY meta_id DESC LIMIT 1",
            HELLOEXTEND_PRODUCT_PROTECTION_SKU
        )
    );
    // phpcs:enable

    if ($product_id) {
        return $product_id;
    }

    return null;
}

function helloextend_add_shipping_protection_fee()
{
    if (!defined('DOING_AJAX') || !$_POST) {
        return;
    }

    if (isset($_POST['fee_amount']) && isset($_POST['fee_label'])) {
        $fee_amount = floatval(number_format( sanitize_text_field(wp_unslash($_POST['fee_amount'])) / 100, 2));
        $fee_label  = sanitize_text_field(wp_unslash($_POST['fee_label']));
        $shipping_quote_id = ( !empty($_POST['shipping_quote_id'] ))  ? (sanitize_key( wp_unslash( $_POST['shipping_quote_id']))) : null;

        if ($fee_amount && $fee_label && $shipping_quote_id) {
            WC()->session->set('shipping_fee', true);
            WC()->session->set('shipping_fee_value', $fee_amount);
            WC()->session->set('shipping_quote_id', $shipping_quote_id);
        } else {
            echo ' No shipping protection fee added because of an error ';
        }
    }
    wp_die();
}

function helloextend_remove_shipping_protection_fee()
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

function helloextend_set_shipping_fee()
{
    if ((is_admin() && !defined('DOING_AJAX')) || !is_checkout()) {
        return;
    }

    $fee_label = __('Extend Shipping Protection', 'helloextend-protection');
    $shipping_fee = WC()->session->get('shipping_fee');
    $remove_fee = WC()->session->get('shipping_fee_remove');
    $fee_amount = WC()->session->get('shipping_fee_value');

    $options = get_option('helloextend_protection_for_woocommerce_shipping_protection_settings');
    $add_as_sku = isset($options['helloextend_sp_add_sku']) && $options['helloextend_sp_add_sku'];

    if ($shipping_fee == 1) {
        if ($add_as_sku) {

            $product_id = helloextend_get_or_create_shipping_protection_product($fee_amount);
            if (!$product_id) {
                HelloExtend_Protection_Logger::helloextend_log_error('Could not create or retrieve shipping protection product');
                return;
            }
            // Avoid duplicate product in cart
            $already_in_cart = false;
            foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
                if ($values['product_id'] == $product_id) {
                    $already_in_cart = true;
                    break;
                }
            }

            if (!$already_in_cart) {
                WC()->cart->add_to_cart($product_id, 1);
            }
        } else {
            WC()->cart->add_fee($fee_label, $fee_amount);
        }
    } elseif ($remove_fee == 1) {
        if ($add_as_sku) {
            // Remove the product from cart
            foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
                $product = $values['data'];
                if ($product && get_post_meta($product->get_id(), '_helloextend_shipping_protection_product', true)) {
                    WC()->cart->remove_cart_item($cart_item_key);
                }
            }
        } else {
            $fees = WC()->cart->get_fees();
            foreach ($fees as $key => $fee) {
                if ($fee->name == $fee_label) {
                    unset($fees[$key]);
                }
            }
            WC()->cart->fees_api()->set_fees($fees);
        }

        WC()->session->set('shipping_fee_remove', false);
    }
}


function helloextend_save_shipping_protection_quote_id($order_id)
{
    $settings = get_option('helloextend_protection_for_woocommerce_general_settings');

    if ($settings['enable_helloextend_debug'] == 1) {
        HelloExtend_Protection_Logger::helloextend_log_debug('Adding metadata for order id ' . $order_id . ' -> shipping_quote_id : ' . WC()->session->get('shipping_quote_id'));
    }

    if (WC()->session->get('shipping_quote_id') !== null) {
        update_post_meta($order_id, '_shipping_protection_quote_id', sanitize_text_field(WC()->session->get('shipping_quote_id')));
    }
}

/*
 *  display the contract information for relevant items in the admin order
*/

function helloextend_add_protection_contract($item_id, $item)
{
    // Get the order object && the contracts meta if any
    $order            = $item->get_order();
    $contracts        = get_post_meta($order->get_id(), '_product_protection_contracts', true);
    $helloextend_meta_data = (array) $item->get_meta('_helloextend_data');

    if (is_array($contracts)) {
        $settings = get_option('helloextend_protection_for_woocommerce_general_settings');
        $env      = $settings['helloextend_environment'] ?? 'sandbox';

        if ($env == 'sandbox') {
            $url         = 'https://customers.demo.extend.com/en-US/warranty_terms';
        } else {
            $url         = 'https://customers.extend.com/en-US/warranty_terms';
        }

        $token = HelloExtend_Protection_Global::helloextend_get_token();

        // Get product object
        if (method_exists($item, 'get_product')) {
            $product = $item->get_product();

            // Check if the product SKU matches product protection
            if ($product->get_sku() === 'helloextend-product-protection') {
                echo '<table cellspacing="0" class="display_meta"><tbody><tr><th>Extend Product Protection contracts : </th><th></th></tr>';

                foreach ($contracts as $product_covered => $contract_id) {
                    if ($helloextend_meta_data['covered_product_id'] == $product_covered) {
                        echo '<tr><td><a href="' . esc_url($url . '?contractId=' . $contract_id . '&accessToken=' . $token) . '">' . esc_html($contract_id) . '</a></td></tr>';
                    }
                }
                echo '</tbody></table>';
            }
        }
    }
}

/**
 * Adds "Ignore Category in Extend" field to the category create form
 * @return void
 */
function helloextend_add_ignore_product_category_field( ) {

    echo '
    <div class="form-field term-helloextend-category-ignore-wrap">
        <label for="helloextend-ignore-display">Ignore Category in Extend</label>
        <input id="helloextend-ignore-display" type="checkbox"/>
        <input hidden="true" name="helloextend-ignore-value" value="0"/>
        <p id="helloextend-ignore-description">When enabled, this category will not be used to retrieve offers from Extend</p>
    </div>
    ';

}

/**
 * Adds "Ignore Category in Extend" field to category edit form
 * @return void
 */
function helloextend_edit_ignore_product_category_field( ) {
    $term_id        = isset($_GET['tag_ID']) ? wp_unslash(sanitize_key($_GET['tag_ID'])) : null;
    $ignored_categories = get_option('helloextend_protection_for_woocommerce_ignored_categories');
    $is_ignored = 0;

    if ($ignored_categories && array_search($term_id, $ignored_categories) > -1) {
        $is_ignored = 1;
    }
    
    echo '
    <tr class="form-field form-required term-helloextend-ignore-wrap">
        <th scope="row">
            <label for="helloextend-ignore-display">Ignore Category in Extend</label>
        </th>
        <td>
            <input id="helloextend-ignore-display" type="checkbox"/>
            <input hidden="true" name="helloextend-ignore-value" value="' . (int) $is_ignored . '"/>
            <p class="description" id="helloextend-ignore-description">When enabled, this category will not be used to retrieve offers from Extend</p>
        </td>
    </tr>
    ';
    $js_file_version = filemtime(plugin_dir_url(__FILE__) . 'admin/js/helloextend-protection-ignore-categories.js');
    wp_enqueue_script('helloextend_set_ignore_value_script', plugin_dir_url(__FILE__) . 'admin/js/helloextend-protection-ignore-categories.js' , array('jquery'), $js_file_version, true);

}

/**
 * Save the ignored category to DB
 * @param int $term_id ID of the ignored category
 * @return void
 */
function helloextend_save_category($term_id) {

    $helloextend_ignore = isset($_POST['helloextend-ignore-value']) ? (bool) $_POST['helloextend-ignore-value'] : null;
    
    $ignored_categories = get_option('helloextend_protection_for_woocommerce_ignored_categories');
    if (!$ignored_categories) {
        $ignored_categories = array();
    }

    $category_in_array = in_array($term_id, $ignored_categories);

    if (($helloextend_ignore && $category_in_array) || (!$helloextend_ignore && !$category_in_array)) {
        return;
    } else if (!$helloextend_ignore && $category_in_array) {
        $new_ignored_categories = array();
        foreach ($ignored_categories as $category_id) { 
            if ($category_id !== $term_id) {
                array_push($new_ignored_categories, $category_id);
            }
        }
        HelloExtend_Protection_Logger::helloextend_log_notice(sprintf("Category ID %d was removed from ignore list", $term_id));
        
        $ignored_categories = $new_ignored_categories;
    } else if ($helloextend_ignore && !$category_in_array) {
        
        HelloExtend_Protection_Logger::helloextend_log_notice(sprintf("Category ID %d was added to ignore list", $term_id));

        array_push($ignored_categories, $term_id);
    }

    update_option('helloextend_protection_for_woocommerce_ignored_categories', $ignored_categories);
}

/**
 * Add a paragraph to the email receipt when a SP protection is added to the order
 * @return void
 */
function helloextend_add_protection_message_to_email($order, $sent_to_admin, $plain_text, $email) {
	foreach ($order->get_fees() as $fee_id => $fee){
		if ($fee->get_name() == "Extend Shipping Protection"){
			echo '<div style="display: inline-flex; margin: 20px 0px;">';
			echo '<img src="'.esc_url(HELLOEXTEND_PLUGIN_URL . '/images/Extend_icon_shipping_protection_160x160.png').'" alt="Extend logo" width="60" height="60" style="margin-right: 10px;" />';
            echo '<p>Your order includes Extend shipping protection. If your package is lost, damaged, or stolen, we’ll replace it for free.</p></div>';
			break;
		}
	}
}

/**
 * Add text to the order received (thank you) page when SP is purchased
 * @return void
 */
function helloextend_add_protection_message_to_thankyou_page($order_id) {
    if (!$order_id) {
        return;
    }
    $order = wc_get_order($order_id);

        foreach ($order->get_fees() as $fee_id => $fee){
            if ($fee->get_name() == "Extend Shipping Protection"){
	            echo '<div style="display: inline-flex; margin: 20px 0px;">';
                echo '<img src="'.esc_url(HELLOEXTEND_PLUGIN_URL . '/images/Extend_icon_shipping_protection_160x160.png').'" alt="Extend logo" width="60" height="60" style="margin-right: 10px;" />';
	            echo '<p>Your order includes Extend shipping protection. If your package is lost, damaged, or stolen, we’ll replace it for free.</p></div>';
	            break;
            }
        }
}


helloextend_run();
