<?php
// If this file is accessed directly, exit.
if (!defined('ABSPATH')) {
    exit;
}
?>

<form id="extend-settings" method="post" action="options.php">
    <?php
        settings_fields('helloextend_protection_for_woocommerce_settings_shipping_protection_option_group');
        do_settings_sections('extend-protection-for-woocommerce-settings-admin-shipping-protection');
        submit_button();
    ?>
</form>
