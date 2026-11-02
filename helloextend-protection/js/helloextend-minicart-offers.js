jQuery(document).ready(function ($) {
  /**
   * Initializes minicart offers
   * @param {Object} minicart miniCart object
   */
  function initMiniCartOffers(cart) {
    $(".woocommerce-mini-cart-item").each(function () {
      const $li = $(this); // current <li> element
      // Find the key elements
      const $removeLink = $li.find("a[data-cart_item_key]");

      // Extract values with fallbacks
      const cart_item_key = $removeLink.data("cart_item_key");

      if (!ExtendCartIntegration.cart_contents.hasOwnProperty(cart_item_key)) {
        return;
      }

      const productId =
        ExtendCartIntegration.cart_contents[cart_item_key].product_id ?? null;
      const price =
        ExtendCartIntegration.cart_contents[cart_item_key].price_raw;
      const quantity =
        ExtendCartIntegration.cart_contents[cart_item_key].quantity;
      const category =
        ExtendCartIntegration.cart_contents[cart_item_key].top_category;

      const offerId = "minicart_offer_" + productId;

      if ($("#" + offerId).length === 0) {
        if (
          ExtendWooCommerce.warrantyAlreadyInCart(
            productId,
            cart ? cart : window.ExtendCartIntegration.cart,
          ) ||
          ExtendCartIntegration.helloextend_enable_cart_offers !== "1"
        ) {
          return;
        }

        $li.append(
          $("<div>", {
            id: offerId,
            "data-covered": productId,
            "data-category": category,
            "data-price": price,
            "data-quantity": quantity,
            style: "width: max-content;",
            class: "minicart-extend-offer",
          }),
        );

        Extend.buttons.renderSimpleOffer("#" + offerId, {
          referenceId: productId,
          price: price,
          category: category,
          onAddToCart: ({ plan, product }) => {
            if (!plan || !product) {
              ExtendWooCommerce.extendAjaxLog(
                "error",
                "SimpleOffer onAddToCart failed: plan or product missing",
              );
              return;
            }
            const data = {
              quantity,
              plan: {
                ...plan,
                covered_product_id: product.id,
              },
            };
            // Add the plan to the cart.
            ExtendWooCommerce.addPlanToCart(data).then(() => {
              // After adding the plan, trigger a refresh of minicart.

              $(document.body).trigger("wc_fragment_refresh");
            });
          },
        });
      }
    });
  }

  //run on page load
  initMiniCartOffers();

  //run on mini-cart updates
  $(document.body).on(
    "wc_fragments_loaded wc_fragments_refreshed added_to_cart removed_from_cart",
    function () {
      //initMiniCartOffers();
      ExtendWooCommerce.getCart().then(initMiniCartOffers);
    },
  );
});
