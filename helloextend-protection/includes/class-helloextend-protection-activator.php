<?php

/**
 * Fired during plugin activation
 *
 * @link  http://example.com
 * @since 1.0.0
 *
 * @package    HelloExtend_Protection
 * @subpackage HelloExtend_Protection/includes
 * @author     support@extend.com
 */

// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}

class HelloExtend_Protection_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since 1.0.0
     */
    public static function activate()
    {
        /* Extend  Logging : On activation create two fields in the wp_options table to store our errors, debugs and notices. */
        add_option('helloextend_error_log');
        add_option('helloextend_notice_log');
        add_option('helloextend_debug_log');
        add_option('helloextend_logger_new_logs');
        add_option('helloextend_logger_ab_show', true);

        // Extend Oauth token fields
        add_option('helloextend_live_token_date');
        add_option('helloextend_sandbox_token_date');
        add_option('helloextend_live_token');
        add_option('helloextend_sandbox_token');

        //create the extend protection product if it doesn't exist (or if it's in the trash)
        $helloextend_product_protection_id = helloextend_product_protection_id();
        $deletedProduct = wc_get_product(helloextend_product_protection_id());

        if (!$helloextend_product_protection_id || $deletedProduct->status == 'trash' ){
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
            } catch (\Exception $e) {
                HelloExtend_Protection_Logger::helloextend_log_error($e->getMessage());
            }

            // upload image and associate to product
            try {
                $product_id     = $product->get_id();
                //check if image exists
                if (file_exists(HELLOEXTEND_PLUGIN_DIR.'/images/Extend_icon.png')) {

                    $upload         = wc_rest_upload_image_from_url(HELLOEXTEND_PLUGIN_URL . '/images/Extend_icon.png');
                    if (is_wp_error($upload)) {
                        HelloExtend_Protection_Logger::helloextend_log_error('Could not upload extend logo from ' . HELLOEXTEND_PLUGIN_URL . '/images/Extend_icon.png : ' . $upload->get_error_message());
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
                    HelloExtend_Protection_Logger::helloextend_log_error('Extend_icon file path incorrect: ' . plugin_dir_path('images/Extend_icon.png'));
                }
            } catch (\Exception $e) {
                HelloExtend_Protection_Logger::helloextend_log_error($e->getMessage());
            }
        }else{
            HelloExtend_Protection_Logger::helloextend_log_error('*** No need to create the product, it exists already');
        }

        return null;
    }
}
