=== Extend Protection For WooCommerce ===
Plugin Name: Extend Protection For WooCommerce
Plugin URI: https://www.extend.com/
Contributors: santiagoenciso33, jmbextend, alexsmithext, helloextend
Tags: extend, protection, tracking
Requires at least: 4.0
Tested up to: 7.0
Stable tag: 1.2.9
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
= 1.2.9 2026-07-15 =
* Fix - keep the warranty "Product"/Term metadata visible in the block Cart/Checkout order summary (it was dropped on the Store API cart refresh)

= 1.2.8 2026-07-14 =
* Fix - show the Extend plan price (not the $1 base) in the block-based Cart/Checkout (Store API) order summary
* Fix - stamp the plan price on the WooCommerce order line item so placing an order no longer reverts the warranty to $1 and skews the order total

= 1.2.7 2026-07-10 =
* Fix - keep Extend plan price on cart line when Advanced Coupons is active

= 1.2.6 2026-07-09 =
### Performance
- **Cache the Extend Product Protection product ID.** `helloextend_product_protection_id()` previously ran an unindexed `wp_postmeta.meta_value` scan on every call, across many code paths per request. The resolved ID is now stored in an autoloaded option (`helloextend_product_protection_id`) and served from memory, with a single fully-indexed query to validate it still points to a live product.
- **Self-validating cache.** On each read the cached ID is checked for existence, non-trashed status, and matching SKU; if the product is trashed, deleted, or its SKU changed, the cache re-resolves and refreshes automatically (returns null when no valid product exists).
- **Cheaper cold-path resolution.** When the cache is empty/stale, the ID is resolved via WooCommerce's `wc_product_meta_lookup` table (one row per product) instead of scanning `wp_postmeta`, with a fallback to `wp_postmeta` when the lookup table is unavailable.
- **Cache priming.** The cached ID is populated proactively on plugin activation, after plugin updates (upgrade routine), and whenever the protection product is created, so the slow resolution effectively never runs on a live request.
- Add-to-cart (AJAX) now uses the cached lookup instead of `wc_get_product_id_by_sku()` on every request.

### Fixed
- **Fatal error on load/activation** (`Call to undefined function wc_get_product_id_by_sku()`). The plugin now bootstraps on `plugins_loaded` (after WooCommerce is guaranteed loaded) instead of at file-include time, and the ID lookup no longer depends on WooCommerce functions being available.
- Activation no longer fatals when its logging/settings classes aren't yet loaded; the activation path now loads its own dependencies.
- Hardened the activator's product check against a null/false product result and replaced the deprecated `->status` access with `->get_status()`.

### Added
- Graceful admin notice when WooCommerce is not active, instead of a fatal error.
- Removal of the cached product-ID option on plugin uninstall.

= 1.2.5 2026-06-18 =
* Fix - Add to cart on an empty cart did not add the warranty item because of the cart normalization running before the item was in cart
* Fix - upgrading the module would default to disable the module and lose the enable product protection setting. 

= 1.2.4 2026-05-08 =
* Fix - Increased priority for warranty price hook so that plan prices are properly set in more scenarios.
* Fix - Fixed a bug where product prices were incorrectly set when orders were sent to Extend.


= 1.2.3 2026-03-25 =
* Feature - Added support for cart offers in WooCommerce side cart

= 1.2.0 2025-09-09 =
 * Feature - Customers now have the option to purchase a protection plan after their initial purchase. Contact Extend to learn more.
 * Fix - Any protection plans on an order will be cancelled when the line items or entire order is refunded or cancelled
 * Fix - Small bug fixes and improvements

= 1.1.3 2025-08-28 =
 * Enhancement - Product images are now synced to Extend store

= 1.1.2 2025-08-25 =
 * Fix - Versioning patch

= 1.1.1 2025-08-01 =
 * Fix - When Shipping Protection as a line item is enabled, the cart total would not correctly update when selecting or deselecting - this has been fixed
 * Fix - Resolved an issue where the default add to cart behavior was not always prevented in the modal offer
 * Feature - Logs are now passed to the backend from the site. You can now view cart offer debug logs.

= 1.1.0 2025-04-15 =
* Fix — PDP offers now pass correct quantity from input element. If the input element does not exists, it defaults to 1.
* Feature — Adds the ability to add Shipping Protection as a line item instead of a checkout fee.

= 1.0.0 2025-03-27 =
* Extend Protection For WooCommerce plugin launch.
* Initial release. Supports product and shipping protection.


