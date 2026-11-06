(function ($) {
  "use strict";

  // The cart is already updated server-side (fee or SP product), so one refresh gets the right total
  function refreshCheckout() {
    $("body").trigger("update_checkout");
  }

  function initShippingOffers() {
    // Deconstructs ExtendProductIntegration variables
    const { items, helloextend_enabled, enable_helloextend_sp, ajax_url } =
      ExtendShippingIntegration;
    let items_array = eval(items);

    if (helloextend_enabled == 0 || enable_helloextend_sp == 0) return;

    //If Extend shipping  protection is enabled, render offers
    if (enable_helloextend_sp == "1") {
      Extend.shippingProtection.render({
        selector: "#helloextend-shipping-offer",
        items: items_array,
        onEnable: function (quote) {
          // Update totals and trigger WooCommerce cart calculations
          $.ajax({
            type: "POST",
            url: ajax_url,
            data: {
              action: "add_shipping_protection_fee",
              fee_amount: quote.premium,
              fee_label: "Shipping Protection",
              shipping_quote_id: quote.id,
            },
            success: refreshCheckout,
          });
        },
        onDisable: function (quote) {
          // Update totals and trigger WooCommerce cart calculations
          $.ajax({
            type: "POST",
            url: ajax_url,
            data: {
              action: "remove_shipping_protection_fee",
            },
            success: refreshCheckout,
          });
        },
        onUpdate: function (quote) {
          // Update totals and trigger WooCommerce cart calculations
          $.ajax({
            type: "POST",
            url: ajax_url,
            data: {
              action: "add_shipping_protection_fee",
              fee_amount: quote.premium,
              fee_label: "Shipping Protection",
              shipping_quote_id: quote.id,
            },
            success: refreshCheckout,
          });
        },
      });
    }
  }

  $(document)
    .off("integration.extend.shipping")
    .on("integration.extend.shipping", function () {
      if (!ExtendWooCommerce || !ExtendShippingIntegration) {
        return;
      }

      initShippingOffers();
    });
})(jQuery);
