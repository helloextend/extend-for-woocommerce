const { extensionCartUpdate } = wc.blocksCheckout;

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
            quote
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
            quote
          },
        });
      },
    });
  }

  $(document).ready(initShippingOffers);
});
