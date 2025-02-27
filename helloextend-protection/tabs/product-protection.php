<?php
// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}
?>

<form id="helloextend-settings" method="post" action="options.php">
    <?php
        settings_fields('helloextend_protection_for_woocommerce_settings_product_protection_option_group');
        do_settings_sections('helloextend-protection-for-woocommerce-settings-admin-product-protection');
        submit_button();
    ?>
</form>
<?php
// Extend Product Protection Item  Management
if (helloextend_is_woocommerce_activated() ) {
    $post_id = null;
    echo "<span class='settings-product-protection-item'>Extend Product Protection Item ";
    $post_id = wc_get_product_id_by_sku(HELLOEXTEND_PRODUCT_PROTECTION_SKU);

    if (! $post_id ) {
        HelloExtend_Protection_Logger::helloextend_log_error('Extend Product Protection item is missing. Please use the create item button in the Extend Settings page');

        echo '... is missing <br/> ';
        echo '<form method="post"  action=""><input type="submit" name="helloextend-product-protection-create" class="button button-primary" value="Create Item" /></form>';

    } else {
        echo ' exists! (SKU: <em>' . esc_html(HELLOEXTEND_PRODUCT_PROTECTION_SKU) . '</em> / ID: <em>' . esc_html($post_id) . '</em>) &#9989;';
    }
    echo '</span>';
}
?>
