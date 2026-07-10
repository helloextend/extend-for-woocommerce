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
 * Version:           1.2.7
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
define('HELLOEXTEND_PROTECTION_VERSION', '1.2.6');
define('HELLOEXTEND_PRODUCT_PROTECTION_SKU', 'helloextend-product-protection');
define('HELLOEXTEND_SHIPPING_PROTECTION_SKU', 'helloextend-shipping-protection');

/*
 * Option used to cache the resolved Extend Product Protection product ID.
 * Resolving the ID from the SKU is an unindexed scan of wp_postmeta.meta_value;
 * caching it in an autoloaded option turns the repeated lookups made throughout
 * the plugin into an in-memory read. See helloextend_product_protection_id().
 */
define('HELLOEXTEND_PRODUCT_PROTECTION_ID_OPTION', 'helloextend_product_protection_id');


define( 'HELLOEXTEND_PLUGIN_FILE', __FILE__ );
define( 'HELLOEXTEND_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HELLOEXTEND_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-helloextend-protection-activator.php
 */
function helloextend_activate()
{
	// The activator logs via HelloExtend_Protection_Logger, which in turn resolves
	// settings through HelloExtend_Protection_Global. These classes are normally
	// loaded as dependencies of helloextend_run(), which now runs on plugins_loaded
	// and has already fired by the time activate_plugin() calls this hook — so load
	// the activation dependencies explicitly here rather than relying on bootstrap.
	include_once HELLOEXTEND_PLUGIN_DIR . 'includes/class-helloextend-global.php';
	include_once HELLOEXTEND_PLUGIN_DIR . 'includes/class-helloextend-protection-logger.php';
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

/**
 * Run one-time data migrations after the plugin is updated.
 *
 * Plugin updates from the WordPress marketplace replace the plugin files but do
 * NOT run the activation hook and never delete existing rows in wp_options. So
 * when a setting is moved or renamed between versions, the old value still lives
 * in the database under its previous key — the new code just stops reading it,
 * which looks like the setting was wiped. This routine copies those values into
 * their new location once per version bump.
 *
 * Gated by the stored `helloextend_db_version` option so it only does work after
 * an update. Every migration below must be idempotent (safe to run twice).
 *
 * @since 1.2.4
 */
function helloextend_maybe_upgrade()
{
    $stored_version = get_option('helloextend_db_version', '0');

    // Already migrated for this code version — nothing to do.
    if (version_compare($stored_version, HELLOEXTEND_PROTECTION_VERSION, '>=')) {
        return;
    }

    /*
     * Migration: `enable_helloextend` moved from the Product Protection settings
     * group into the General settings group. Copy it forward only if the new
     * location has not been set yet, so we never clobber a newer admin choice.
     */
    $general_settings = (array) get_option('helloextend_protection_for_woocommerce_general_settings', array());
    $pp_settings      = (array) get_option('helloextend_protection_for_woocommerce_product_protection_settings', array());

    if (!array_key_exists('enable_helloextend', $general_settings)
        && array_key_exists('enable_helloextend', $pp_settings)
    ) {
        $general_settings['enable_helloextend'] = $pp_settings['enable_helloextend'];
        update_option('helloextend_protection_for_woocommerce_general_settings', $general_settings);
        // Old key is left in place intentionally (non-destructive) — the new code
        // ignores it, and keeping it allows a safe rollback to a prior version.
    }

    /*
     * Prime the product-protection ID cache after an update. Plugin updates replace
     * files without running the activation hook, so existing installs would not have
     * the cache option populated until the getter's cold path fired on a live request.
     * Resolving it here means that first post-update request already hits the fast
     * path. Safe to call this early on plugins_loaded: the getter uses $wpdb only. If
     * the product doesn't exist yet this is a harmless no-op — the activation/init
     * creation routines populate the cache when they create it.
     */
    helloextend_product_protection_id();

    // Record that migrations through the current code version have run.
    update_option('helloextend_db_version', HELLOEXTEND_PROTECTION_VERSION);
}

/* Actions */

/* run data migrations after a plugin update */
add_action('plugins_loaded', 'helloextend_maybe_upgrade');

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
    $css_path = HELLOEXTEND_PLUGIN_DIR . 'css/helloextend.css';
	$lastmodtime= file_exists($css_path) ? filemtime($css_path) : HELLOEXTEND_PROTECTION_VERSION;
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

            // Prime the cached ID so subsequent lookups skip the SKU resolution.
            helloextend_set_product_protection_id_cache($product->get_id());
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

    // Fast path: return the cached ID if it still points to a live product with
    // the expected SKU. The option is autoloaded (in-memory) and the validation
    // query is fully indexed (posts primary key + postmeta post_id index), so it
    // avoids the unindexed meta_value scan the resolution below performs. This
    // uses $wpdb rather than WooCommerce helpers because the function runs during
    // plugin bootstrap, before WooCommerce's functions are defined.
    $cached_id = (int) get_option(HELLOEXTEND_PRODUCT_PROTECTION_ID_OPTION);
    if ($cached_id) {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $is_valid = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT p.ID FROM $wpdb->posts p
                 INNER JOIN $wpdb->postmeta pm ON pm.post_id = p.ID
                 WHERE p.ID = %d AND p.post_status != 'trash'
                 AND pm.meta_key = '_sku' AND pm.meta_value = %s
                 LIMIT 1",
                $cached_id,
                HELLOEXTEND_PRODUCT_PROTECTION_SKU
            )
        );
        // phpcs:enable
        if ($is_valid) {
            return $cached_id;
        }
    }

    // Cold/stale cache: resolve the ID from the SKU. This runs only when the cache
    // is empty or invalid (e.g. right after the product is created or trashed)
    // rather than on every call, and the result is cached so the fast path serves
    // every subsequent call this request and beyond.
    $product_id = helloextend_resolve_product_protection_id_by_sku();

    if ($product_id) {
        helloextend_set_product_protection_id_cache($product_id);
        return $product_id;
    }

    // No product with this SKU exists (yet); drop any stale cache.
    delete_option(HELLOEXTEND_PRODUCT_PROTECTION_ID_OPTION);

    return null;
}

/**
 * Resolve the Extend Product Protection product ID from its SKU (the cold path).
 *
 * Prefers WooCommerce's wc_product_meta_lookup table: it holds one row per product
 * (versus ~dozens per product in wp_postmeta), so even though `sku` is not indexed
 * there, the scan is far cheaper than the equivalent postmeta scan. The join to
 * wp_posts excludes trashed products. Falls back to wp_postmeta when the lookup
 * table is absent (older WooCommerce, or lookup tables not yet regenerated).
 *
 * Uses $wpdb only — no WooCommerce functions — so it is safe during bootstrap.
 *
 * @return int Product ID, or 0 if no matching live product exists.
 */
function helloextend_resolve_product_protection_id_by_sku(): int
{
    global $wpdb;

    $lookup_table = $wpdb->prefix . 'wc_product_meta_lookup';

    // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
    $has_lookup = $wpdb->get_var(
        $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($lookup_table))
    ) === $lookup_table;

    if ($has_lookup) {
        $product_id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT l.product_id FROM {$lookup_table} l
                 INNER JOIN $wpdb->posts p ON p.ID = l.product_id
                 WHERE l.sku = %s AND p.post_status != 'trash'
                 ORDER BY l.product_id DESC LIMIT 1",
                HELLOEXTEND_PRODUCT_PROTECTION_SKU
            )
        );
        if ($product_id > 0) {
            return $product_id;
        }
    }

    return (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT pm.post_id FROM $wpdb->postmeta pm
             INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
             WHERE pm.meta_key = '_sku' AND pm.meta_value = %s AND p.post_status != 'trash'
             ORDER BY pm.meta_id DESC LIMIT 1",
            HELLOEXTEND_PRODUCT_PROTECTION_SKU
        )
    );
    // phpcs:enable
}

/**
 * Cache the resolved Extend Product Protection product ID in an autoloaded option.
 *
 * Called after the product is (re)created so the fast path in
 * helloextend_product_protection_id() is primed immediately.
 *
 * @param int $product_id
 */
function helloextend_set_product_protection_id_cache($product_id)
{
    $product_id = (int) $product_id;
    if ($product_id > 0) {
        update_option(HELLOEXTEND_PRODUCT_PROTECTION_ID_OPTION, $product_id, true);
    }
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
            if ( $product && $product instanceof WC_Product ) {
                // Check if the product SKU matches product protection
                if ($product->get_sku() === HELLOEXTEND_PRODUCT_PROTECTION_SKU) {
                  echo '<table cellspacing="0" class="display_meta"><tbody><tr><th>Extend Product Protection contracts : </th><th></th></tr>';

                    foreach ($contracts as $product_covered => $contract_id) {
                        if ( isset( $helloextend_meta_data['covered_product_id'] ) && $helloextend_meta_data['covered_product_id'] == $product_covered ) {
                            $link = add_query_arg(
                                array(
                                    'contractId'  => rawurlencode( $contract_id ),
                                    'accessToken' => rawurlencode( $token ),
                                    ),
                                $url
                                );
                            echo '<tr><td><a href="' . esc_url( $link ) . '">' . esc_html( $contract_id ) . '</a></td></tr>';
                        }
                    }
                    echo '</tbody></table>';
                }
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
    $js_path = HELLOEXTEND_PLUGIN_DIR . 'admin/js/helloextend-protection-ignore-categories.js';
    $js_file_version = file_exists($js_path) ? filemtime($js_path) : HELLOEXTEND_PROTECTION_VERSION;
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


/**
 * Bootstrap the plugin on plugins_loaded rather than at file-include time.
 *
 * Active plugin files are included alphabetically, so this plugin loads before
 * WooCommerce; kicking off at include time meant our runtime code could run before
 * WooCommerce's functions/classes existed. By plugins_loaded every plugin file has
 * been included, so WooCommerce is guaranteed available. Priority 11 keeps this
 * after helloextend_maybe_upgrade() (priority 10).
 *
 * If WooCommerce is not active we skip bootstrapping entirely and surface an admin
 * notice instead of fataling on the first WooCommerce call.
 */
function helloextend_bootstrap()
{
    if (!helloextend_is_woocommerce_activated()) {
        add_action('admin_notices', 'helloextend_woocommerce_missing_notice');
        return;
    }

    helloextend_run();
}
add_action('plugins_loaded', 'helloextend_bootstrap', 11);

function helloextend_woocommerce_missing_notice()
{
    echo '<div class="error"><p><strong>'
        . esc_html__('Extend Protection requires the WooCommerce plugin to be installed and active.', 'helloextend-protection')
        . '</strong></p></div>';
}
