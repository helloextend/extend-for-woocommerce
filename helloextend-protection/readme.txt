=== Extend Protection For WooCommerce ===
Plugin Name: Extend Protection For WooCommerce
Plugin URI: https://www.extend.com/
Contributors: santiagoenciso33, jmbextend, alexsmithext, helloextend
Tags: extend, protection, tracking
Requires at least: 4.0
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extend helps merchants generate revenue and protect customers from damage and loss through modern product and shipping protection solutions.

== Description ==

**About Extend**

Extend helps merchants generate revenue and protect customers from damage and loss through modern product and shipping protection solutions. No cost. Total profit. Win-win.

**Extend Products**

- **Extend Product Protection**: When a product fails or is damaged accidentally, Extend repairs or replaces it. Customers buy again, merchants boost revenue on plans and new purchases.
- **Extend Shipping Protection**: When a package is lost, stolen, or damaged, Extend refunds the customer and the merchant. Customers buy again, merchants boost revenue on plans and new purchases.

== Installation ==

= Minimum Requirements =

* PHP 7.4 or greater is required (PHP 8.0 or greater is recommended)
* MySQL 5.6 or greater is recommended
* WooCommerce 7.0 or greater must be installed and activated

Visit the [Extend Documentation](https://docs.extend.com/docs/welcome-to-extend) page for more information about the Extend API.

= Automatic installation =

Automatic installation is the easiest option -- WordPress will handle the file transfer, and you won’t need to leave your web browser. To do an automatic install of Extend Protection For WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”

In the search field type “Extend Protection For WooCommerce,” then click “Search Plugins.” Click “Install Now,” and then click "Activate".

= Manual installation =

Manual installation method requires downloading the Extend Protection For WooCommerce plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).

= Updating =

Automatic updates should work like a charm; as always, though, ensure you back up your site just in case.

== Frequently Asked Questions ==

= What is Extend? =

Extend helps merchants generate revenue and protect customers from damage and loss through modern product and shipping protection solutions. No cost. Total profit. Win-win.

= What do I need for the plugin to work?  =

You need to have WooCommerce 7.0 or greater installed and activated. You also need to have an Extend account and API key. For more information, visit the [Extend Documentation](https://docs.extend.com/docs/welcome-to-extend) page.

== External services ==

Extend Protection For WooCommerce relies on the Extend API to send and receive contract information. It also uses the Extend SDK to render offers. The plugin communicates with the following services:
- [Extend API](https://docs.extend.com/reference/ordersupsert-1): used to create and manage protection plans, contracts, claims, and upsell leads. In the plugin you will see the URL `https://api.helloextend.com` and `https://api-demo.helloextend.com`, which is the API endpoint for Extend's API.
- [Extend SDK](https://helloextend.github.io/extend-sdk-client): The plugin also uses Extend's SDK via `sdk.helloextend.com` to render protection offers in the frontend.
- [Extend Merchant Portal](https://merchants.extend.com/): is available for users to manage products, plans, contracts, claims, and upsell leads. It also provides enhanced reporting & dashboards: customer segmentation, catalog analysis, trends, and performance.

The plugin sends order information to the Extend API when the actions `woocommerce_checkout_order_processed` and `woocommerce_order_status_completed` are triggered to create a contract and send the customer an email with the contract details.

For more information on our terms of service and privacy policy, visit the links below:
- https://www.extend.com/terms
- https://www.extend.com/privacy

== Screenshots ==

1. Kaley is our automated claims agent, who adjudicates 98% of claims adjudicated in second.
2. Sell protection plans online, in-store, and in-app.
3. Extend’s Merchant Portal enables self-service management for products, plans, contracts, claims, and upsell leads. Along with Enhanced reporting & dashboards: customer segmentation, catalog analysis, trends and performance.
4. Extend's settings page in wp-admin.

== Changelog ==

= 1.1.0 2025-04-15 =
* Fix — PDP offers now pass correct quantity from input element. If the input element does not exists, it defaults to 1.
* Feature — Adds the ability to add Shipping Protection as a line item instead of a checkout fee.

= 1.0.0 2025-03-27 =
* Extend Protection For WooCommerce plugin launch.
* Initial release. Supports product and shipping protection.


