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
        echo '<div class="error"><p><strong>' . sprintf(esc_html__('Extend Protection requires the WooCommerce plugin to be installed and active. You can download %s here.', 'woocommerce-services'), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
    }

    echo '<div style="padding-top:30px">';
    echo ' <img src="' . plugins_url() . '/extend-protection/images/Extend_logo_slogan.svg" alt="Extend Logo with Slogan" style="width: 170px;">
			<p>Extend generates new revenue for your store, increases overall purchase conversions, and provides customers with streamlined product protection and peace of mind. <a href="https://extend.com/merchants">Learn more</a><br/>
            <a href="https://merchants.extend.com" class="button button-primary action action-extend-external" target="_blank">Set up my Extend account</a> or <a href="https://merchants.extend.com" class="extend-account-link" target="_blank"> I already have an Extend account, I\'m ready to edit my settings</a> </p>';
    echo '</div>';


    settings_errors(); ?>

    <form id="extend-settings" method="post" action="options.php">
        <?php
        settings_fields('extend_protection_for_woocommerce_settings_option_group');
        do_settings_sections('extend-protection-for-woocommerce-settings-admin');
        submit_button();
        ?>
    </form>
    <?php

    //Extend Product Protection Item  Management
    if (is_woocommerce_activated()) {
        $post_id = null;

        echo "<span class='settings-product-protection-item'>Extend Product Protection Item <em>(sku : " . EXTEND_PRODUCT_PROTECTION_SKU . ")</em> ";
        $post_id = wc_get_product_id_by_sku(EXTEND_PRODUCT_PROTECTION_SKU);

        if (!$post_id) {
            echo "... is missing <br/> "; //<button class='button button-primary' id='extend-product-protection-create'>Create Item</button>";
            echo '<form method="post"  action=""><input type="submit" name="extend-product-protection-create" class="button button-primary" value="Create Item" /></form>';

        } else {
            echo " exists! (ID: " . $post_id . ") &#9989;";
        }
        echo "</span>";
    }
}


function extend_render_about_page()
{

}

function extend_render_documentation_page()
{

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

/* local bypass of curl error ssl */
add_filter('https_ssl_verify', '__return_false');


/* item create */
add_action('init', 'extend_product_protection_create');

function extend_product_protection_create()
{
    if (isset($_POST['extend-product-protection-create'])) {
        $product = new WC_Product_Simple();
        $product->set_name('Extend Product Protection');
        $product->set_status('publish');
        $product->set_sku(EXTEND_PRODUCT_PROTECTION_SKU);
        $product->set_catalog_visibility('hidden');
        $product->set_price(1.00);
        $product->set_regular_price(1.00);
        $product->set_virtual(true);
        $product->save();

        //upload image and associate to product
        $product_id = $product->get_id();
        $upload = wc_rest_upload_image_from_url(plugins_url() . '/extend-protection/images/Extend_icon.png');
        $product_img_id = wc_rest_set_uploaded_image_as_attachment($upload, $product_id);
        $product->set_image_id($product_img_id);
        $product->save();
    }
}

run_extend_protection();
