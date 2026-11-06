<?php

/**
 * Extend For WooCommerce Block Registration.
 *
 * @since   1.3
 * @package HelloExtend_Protection
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/includes
 */

/**
 * Registers the plugin's editor blocks.
 *
 * Blocks are authored under client/blocks/<name>/ and compiled into build/,
 * where the build also emits blocks-manifest.php — a single file holding every
 * block.json in the collection, so WordPress parses one PHP file instead of
 * reading and decoding each block.json separately.
 *
 * @package HelloExtend_Protection
 * @author  Extend, Inc.
 *
 * Generated with assistance from Claude Code.
 */

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

class HelloExtend_Protection_Blocks
{
    private const WP_VERSION_NOTICE_TRANSIENT = 'helloextend_blocks_wp_version_notice';

    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Initiate our hooks.
     *
     * @since 1.3
     */
    public function hooks()
    {
        /*
         * Blocks must be registered on init. This class is constructed from
         * HelloExtend_Protection during plugins_loaded, which is too early —
         * registering there also registers the blocks' scripts before
         * wp_register_script() is allowed to run, which WordPress flags as
         * _doing_it_wrong.
         */
        add_action('init', [ $this, 'register_blocks' ]);
        add_filter( 'block_categories_all', [$this, 'register_block_category'], 10, 2 );
    }

    /**
     * @param array[] $block_categories
     * @param WP_Block_Editor_Context $editor_context
     * 
     * @since 1.3
     */
    public function register_block_category($block_categories, $editor_context)
    {
        array_push(
            $block_categories,
            array(
                'slug'  => 'helloextend-protection',
                'title' => __( 'Extend Protection', 'helloextend-protection' ),
                'icon'  => null,
            )
        );

        return $block_categories;
    }

    /**
     * Register every block in the build collection from its manifest.
     *
     * @since 1.3
     */
    public function register_blocks()
    {
        /*
         * The collection root must be the directory that DIRECTLY contains the
         * block folders. WordPress locates each block's metadata by joining the
         * root with the manifest key and '/block.json', and the keys the build
         * emits are bare folder names ('buttons'), never paths. If the blocks
         * sit one level deeper the lookup misses, block.json is reported as
         * non-existent, and the metadata's 'file' entry becomes null — which
         * silently strips the render callback and the editor script, leaving a
         * block that registers without error and never appears in the inserter.
         */
        $collection_path = HELLOEXTEND_PLUGIN_DIR . 'build';
        $manifest_path   = $collection_path . '/blocks-manifest.php';

        /*
         * wp_register_block_types_from_metadata_collection() landed in
         * WordPress 6.8, and the blocks themselves are apiVersion 3 (6.3+).
         * readme.txt still advertises support back to 4.1, so without this
         * guard an older install fatals on an undefined function on every
         * request — the plugin would not load at all, blocks or not.
         */
        if (!function_exists('wp_register_block_types_from_metadata_collection')) {
            /*
             * Rate limited because this runs on every init. The notice log is
             * an option that helloextend_log_notice() reads, appends to and
             * rewrites whole, with no cap — so on a site that has opted into
             * logging it would grow by one record and one write per request.
             *
             * The transient is only worth setting when the notice is actually
             * written. helloextend_log_notice() returns early while logging is
             * off, so setting it unconditionally would mark a notice as
             * delivered that never reached the log, and a merchant who turns
             * logging on to find out why the blocks are missing would wait up
             * to a day for it to appear.
             */
            $settings    = HelloExtend_Protection_Global::helloextend_get_settings();
            $log_enabled = $settings['enable_helloextend_log'] ?? 0;

            /*
             * Loose != 0 on purpose: it is the comparison the logger itself
             * makes, so the gate and the logger can never disagree about
             * whether a given setting value counts as on.
             */
            if ($log_enabled != 0 && !get_transient(self::WP_VERSION_NOTICE_TRANSIENT)) {
                set_transient(self::WP_VERSION_NOTICE_TRANSIENT, 1, DAY_IN_SECONDS);
                HelloExtend_Protection_Logger::helloextend_log_notice('Skipping block registration: WordPress 6.8 or later is required. The rest of the plugin is unaffected.');
            }

            return;
        }

        /*
         * A missing manifest is silent unless WP_DEBUG is on: the collection
         * fails to register and every block disappears from the inserter with
         * no other symptom. Log it so the cause is visible in the plugin log.
         */
        if (!file_exists($manifest_path)) {
            HelloExtend_Protection_Logger::helloextend_log_error('Could not register blocks, manifest missing at: ' . $manifest_path . ' — run the block build.');
            return;
        }

        wp_register_block_types_from_metadata_collection($collection_path, $manifest_path);
    }
}
