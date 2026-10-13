<?php

/**
 * Extend For WooCommerce Block Registration.
 *
 * @since   1.2.11
 * @package HelloExtend_Protection
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/includes
 */

/**
 * Registers the plugin's editor blocks.
 *
 * Blocks live under client/blocks/<name>/ and are registered from their
 * block.json metadata. Each block is dynamic — its markup is produced by the
 * render.php the metadata points at, so no build step or editor bundle is
 * required.
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
    /**
     * Directories under client/blocks/ to register, relative to the plugin root.
     *
     * @since  1.2.11
     * @access private
     * @var    string[] $blocks
     */
    private array $blocks = [ 'buttons' ];

    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Initiate our hooks.
     *
     * @since 1.2.11
     */
    public function hooks()
    {
        add_action('init', [ $this, 'register_blocks' ]);
    }

    /**
     * Register each block from its block.json metadata.
     *
     * @since 1.2.11
     */
    public function register_blocks()
    {
        foreach ($this->blocks as $block) {
            $block_path = HELLOEXTEND_PLUGIN_DIR . 'client/blocks/' . $block;

            if (!file_exists($block_path . '/block.json')) {
                HelloExtend_Protection_Logger::helloextend_log_error('Could not register block, block.json missing at: ' . $block_path);
                continue;
            }

            register_block_type($block_path);
        }
    }
}
