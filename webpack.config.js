/**
 * Build configuration for the plugin's editor blocks.
 *
 * wp-scripts' default config extracts `@wordpress/*` imports into script
 * handles and writes them to each entry's .asset.php, so WordPress can load
 * them in dependency order. It does not know about `@woocommerce/*`, which
 * leaves the shipping protection view script reading `wc.blocksCheckout` off
 * the global at load with nothing guaranteeing wc-blocks-checkout ran first.
 *
 * WooCommerce's extractor is a superset — it resolves `@woocommerce/*` and
 * everything the WordPress one handles — so it replaces that plugin rather
 * than being added alongside it.
 *
 * Generated with assistance from Claude Code.
 */
const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const WooCommerceDependencyExtractionWebpackPlugin = require("@woocommerce/dependency-extraction-webpack-plugin");

module.exports = {
  ...defaultConfig,
  plugins: [
    ...defaultConfig.plugins.filter(
      (plugin) =>
        plugin.constructor.name !== "DependencyExtractionWebpackPlugin",
    ),
    new WooCommerceDependencyExtractionWebpackPlugin(),
  ],
};
