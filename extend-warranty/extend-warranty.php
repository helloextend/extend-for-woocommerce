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
 * @package           Extend_Warranty
 *
 * @wordpress-plugin
 * Plugin Name:       Extend Warranty for Woocommerce
 * Plugin URI:        https://docs.extend.com/docs
 * Description:       WooCommerce plugin to display Extend Warranty Offers.
 * Version:           1.0.0
 * Author:            Extend
 * Author URI:        https://extend.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       extend-warranty
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EXTEND_WARRANTY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-extend-warranty-activator.php
 */
function activate_extend_warranty() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-extend-warranty-activator.php';
	Extend_Warranty_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-extend-warranty-deactivator.php
 */
function deactivate_extend_warranty() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-extend-warranty-deactivator.php';
	Extend_Warranty_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_extend_warranty' );
register_deactivation_hook( __FILE__, 'deactivate_extend_warranty' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-extend-warranty.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_extend_warranty() {

	$plugin = new Extend_Warranty();
	$plugin->run();

}

function extend_render_settings_page(){


    echo '<div style="padding-top:30px">';
    echo ' <img src="'.plugins_url().'/extend-warranty/images/Extend_logo_slogan.svg" alt="Extend Logo with Slogan" style="width: 170px;">
			<p>Extend generates new revenue for your store, increases overall purchase conversions, and provides customers with streamlined product protection and peace of mind. <a href="https://extend.com/merchants">Learn more</a><br/>
            <a href="https://merchants.extend.com" class="action action-extend-external" target="_blank">Set up my Extend account</a> or <a href="https://merchants.extend.com" class="extend-account-link" target="_blank"> I already have an Extend account, I\'m ready to edit my settings</a> </p>';
    echo '</div>';


             settings_errors(); ?>

			<form id="extend-settings" method="post" action="options.php">
				<?php
					settings_fields( 'extend_warranty_for_woocommerce_settings_option_group' );
                    do_settings_sections( 'extend-warranty-for-woocommerce-settings-admin' );
					submit_button();
				?>
			</form>
			<?php
}




function extend_render_about_page(){

}

function extend_render_documentation_page(){

}

function extend_warranty_style(){
    // Register stylesheets
    wp_register_style('extend_warranty_style', plugins_url('extend-warranty/css/extend.css'));
    wp_enqueue_style('extend_warranty_style');
}

run_extend_warranty();
