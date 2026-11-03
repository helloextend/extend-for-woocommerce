/*
 * Imported rather than read off window.wc so the build records
 * wc-blocks-checkout as a dependency of this script. Reading the global at
 * load relied on WooCommerce happening to print its bundle first, and threw
 * on the whole file when it did not.
 */
import { extensionCartUpdate } from "@woocommerce/blocks-checkout";

jQuery(function ($) {
  "use strict";

  function initShippingOffers() {
    const items = $("#helloextend-block-shipping-offer").data("items");

    Extend.shippingProtection.render({
      selector: "#helloextend-block-shipping-offer",
      items,
      onEnable: function (quote) {
        extensionCartUpdate({
          namespace: "helloextend/shipping-protection",
          data: {
            action: "add",
            quote,
          },
        });
      },
      onDisable: function () {
        extensionCartUpdate({
          namespace: "helloextend/shipping-protection",
          data: {
            action: "remove",
          },
        });
      },
      onUpdate: function (quote) {
        extensionCartUpdate({
          namespace: "helloextend/shipping-protection",
          data: {
            action: "add",
            quote,
          },
        });
      },
    });
  }

  $(document).ready(initShippingOffers);
});
