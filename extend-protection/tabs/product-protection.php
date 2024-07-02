<form id="extend-settings" method="post" action="options.php">
    <?php
        settings_fields('extend_protection_for_woocommerce_settings_product_protection_option_group');
        do_settings_sections('extend-protection-for-woocommerce-settings-admin-product-protection');
        submit_button();
    ?>
</form>
<?php
// Extend Product Protection Item  Management
if (is_woocommerce_activated() ) {
    $post_id = null;
    echo esc_html("<span class='settings-product-protection-item'>Extend Product Protection Item ");
    $post_id = wc_get_product_id_by_sku(EXTEND_PRODUCT_PROTECTION_SKU);

    if (! $post_id ) {
        Extend_Protection_Logger::extend_log_error('Extend Product Protection item is missing. Please use the create item button in the Extend Settings page');

        echo esc_html('... is missing <br/> ');
        echo esc_html('<form method="post"  action=""><input type="submit" name="extend-product-protection-create" class="button button-primary" value="Create Item" /></form>');

    } else {
        echo esc_html(' exists! (SKU: <em>' . EXTEND_PRODUCT_PROTECTION_SKU . '</em> / ID: <em>' . $post_id . '</em>) &#9989;');
    }
    echo '</span>';
}
?>
