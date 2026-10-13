<?php

/**
 * Asset manifest for index.js.
 *
 * register_block_type() looks for a `<script>.asset.php` next to each script
 * declared in block.json and uses it to enqueue that script with the right
 * dependencies and version. Tooling such as @wordpress/scripts generates this
 * file, but the plugin has no build step, so it is maintained by hand: keep the
 * dependency list in sync with the wp.* globals index.js consumes.
 *
 * @since   1.2.11
 * @package HelloExtend_Protection
 *
 * Generated with assistance from Claude Code.
 */

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

return array(
    'dependencies' => array(
        'wp-block-editor',
        'wp-blocks',
        'wp-element',
        'wp-i18n',
    ),
    'version'      => defined('HELLOEXTEND_PROTECTION_VERSION') ? HELLOEXTEND_PROTECTION_VERSION : '1.0.0',
);
