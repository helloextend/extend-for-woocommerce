<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link  http://example.com
 * @since 1.0.0
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/includes
 * @author     partner-engineering@extend.com
 */

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

class Helloextend_Protection_i18n
{

    /**
     * Load the plugin text domain for translation.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
            'helloextend-protection',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }
}

