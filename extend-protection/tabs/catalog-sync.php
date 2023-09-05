<form id="extend-settings" method="post" action="options.php">
    <?php
        settings_fields('extend_protection_for_woocommerce_settings_catalog_sync_option_group');
        do_settings_sections('extend-protection-for-woocommerce-settings-admin-catalog-sync');
        submit_button();
    ?>
</form>

<?php
    echo '<br/>  <input type="button" id="extend-catalog-sync-run" name="extend-catalog-sync-run" class="button button-danger" value="Run Manual Sync" />';
    echo '<div id="progress-bar-container"><div id="progress-bar"></div></div>';
    echo '<div id="sync_feedback"></div>';
    echo "<br/>";
    echo '<br /><input type="button" id="extend-catalog-sync-reset" name="extend-catalog-sync-reset" class="button button-danger" value="Reset Last Sync Date" />';
?>