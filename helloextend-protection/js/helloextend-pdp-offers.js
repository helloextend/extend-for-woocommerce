(function ($) {
  "use strict";

  /*
   * Teardown for the capture-phase add to cart interceptor, held at module
   * scope so that a repeated `integration.extend` trigger replaces the
   * previous listener instead of stacking another one. addEventListener only
   * de-duplicates identical function references, and every run of the handler
   * below builds a fresh closure.
   *
   * Generated with assistance from Claude Code.
   */
  let removeAtcInterceptor = null;

  $(document)
    .off("integration.extend.pdp")
    .on("integration.extend.pdp", function () {
      if (!ExtendWooCommerce || !ExtendProductIntegration) return;

      // Deconstructs ExtendProductIntegration variables
      const {
        type: product_type,
        id: product_id,
        first_category,
        price,
        helloextend_enabled,
        helloextend_pp_enabled,
        helloextend_pdp_offers_enabled,
        helloextend_modal_offers_enabled,
        atc_button_selector,
        variations = {},
      } = ExtendProductIntegration;

      if (helloextend_enabled == 0 || helloextend_pp_enabled == 0) return;

      const getQuantity = () =>
        parseInt($('input[name="quantity"]').val() || 1, 10);

      let reference_id = product_id;

      /*
       * PDP offers turned off in the settings. class-helloextend-protection-pdp-offer.php
       * gates the container on the same setting, so there is nothing to render
       * into — and without this, add to cart was still intercepted and replayed
       * on every click to resolve a plan that could never exist. PHP treats
       * both "" and "0" as off; "0" is truthy in JavaScript, so match it by name.
       */
      if (
        !helloextend_pdp_offers_enabled ||
        helloextend_pdp_offers_enabled === "0"
      ) {
        $(".helloextend-offer").hide();
        return;
      }

      /*
       * A product template can end up with two offer containers: the one
       * class-helloextend-protection-pdp-offer.php echoes on the configured
       * PDP hook, and the one the Extend Buttons block renders. Block themes
       * still fire those classic hooks, so placing the block does not stop the
       * backend from echoing its own.
       *
       * Everything below addresses the offer by the .helloextend-offer
       * selector, so a second match is at best a stray empty box and at worst
       * the element the SDK picks instead of the one the merchant placed.
       * Inserting the block is a deliberate placement choice, so the block's
       * container wins and the rest are dropped before anything renders.
       */
      const blockOffer = document.querySelector(
        '.helloextend-offer[data-helloextend-offer="block"]',
      );

      if (blockOffer) {
        const strays = Array.prototype.filter.call(
          document.querySelectorAll(".helloextend-offer"),
          function (el) {
            return el !== blockOffer;
          },
        );

        strays.forEach(function (el) {
          el.remove();
        });

        /* Worth a log line: from the merchant's side this looks like the
                 PDP "Offer Location" setting silently doing nothing. */
        if (strays.length && ExtendWooCommerce.debugLogEnabled) {
          ExtendWooCommerce.extendAjaxLog(
            "debug",
            "helloextend-pdp-offers.js: Extend Buttons block found, dropped " +
              strays.length +
              " offer container(s) rendered by the classic PDP hook.",
          );
        }
      }

      /**
       * Find out whether the shopper wants protection with this add to cart.
       *
       * Resolves with { plan, referenceId } when there is a plan to add, or
       * null when there is nothing to add: no offer rendered, no selection
       * and no modal configured, or the modal dismissed without a choice.
       *
       * @return {Promise} Resolves with the protection decision, or null.
       */
      function getPlanDecision() {
        return new Promise(function (resolve) {
          const component = Extend.buttons.instance(".helloextend-offer");

          /* No offer on the page — e.g. a variable product before a
                   variation has been picked. Nothing to attach. */
          if (!component) {
            resolve(null);
            return;
          }

          const activeProduct = component.getActiveProduct();
          const referenceId = activeProduct ? activeProduct.id : reference_id;
          const productPrice = activeProduct ? activeProduct.price : price;
          const plan = component.getPlanSelection();

          /* get the users plan selection */
          if (plan) {
            resolve({ plan: plan, referenceId: referenceId });
            return;
          }

          if (helloextend_modal_offers_enabled != "1") {
            resolve(null);
            return;
          }

          /* Nothing selected inline: upsell through the modal and hold
                   the add to cart until the shopper chooses or dismisses. */
          Extend.modal.open({
            referenceId,
            price: productPrice,
            category: first_category,
            onClose: function (modalPlan, product) {
              resolve(
                modalPlan && product
                  ? { plan: modalPlan, referenceId: referenceId }
                  : null,
              );
            },
          });
        });
      }

      /**
       * Add the decided plan to the cart.
       *
       * @param  {Object|null} decision Result of getPlanDecision().
       * @return {Promise}              Resolves once the plan is in the cart.
       */
      function submitPlan(decision) {
        if (!decision) return Promise.resolve();

        return ExtendWooCommerce.addPlanToCart({
          quantity: getQuantity(),
          plan: { ...decision.plan, covered_product_id: decision.referenceId },
          price: (decision.plan.price / 100).toFixed(2),
        });
      }

      /**
       * Reject add to cart buttons belonging to a different product.
       *
       * Guards against a merchant configuring a loose atc_button_selector
       * that also matches related products or upsells further down the page.
       * Classic markup is identified from the form's add-to-cart field; block
       * markup has no form.cart wrapper, so fall back to the product id the
       * block renders onto the button itself. Only a positive mismatch
       * rejects — a button we cannot place is let through, so themes with
       * unusual markup keep working.
       *
       * @param  {Element} btn The clicked add to cart button.
       * @return {boolean}     Whether the button adds this page's product.
       */
      function belongsToThisProduct(btn) {
        const form = btn.closest("form.cart");
        const field = form ? form.querySelector('[name="add-to-cart"]') : null;

        if (field) return String(field.value) === String(product_id);

        const blockProductId =
          btn.getAttribute("data-product_id") ||
          btn.getAttribute("data-product-id");

        if (blockProductId)
          return String(blockProductId) === String(product_id);

        return true;
      }

      /**
       * Intercept add to cart so protection can be resolved first.
       *
       * Registered in the capture phase on window. The capture path runs
       * window -> document -> ... -> target, so this fires ahead of every
       * bubble-phase handler AND ahead of any capture-phase listener on
       * document, whichever registered first. That ordering is load-bearing:
       * block themes drive add to cart from a capture-phase listener on
       * document and call stopImmediatePropagation, which takes the click
       * before anything on document or the button itself can see it.
       * Registering on document would make this a race we lose, because the
       * theme's footer script binds at parse time while this runs on ready.
       *
       * Delegation also keeps it working when the button is re-rendered by a
       * variation change or a fragment refresh.
       *
       * The click is cancelled, the protection decision resolved, then the
       * original click replayed. Replaying rather than re-firing an AJAX
       * event means one path covers both AJAX and plain form themes —
       * whatever would have handled the first click handles the replay.
       */
      function handleAddToCartLogic() {
        /* atc_button_selector is free text from the settings screen, and
               an invalid selector would now throw on every click rather than
               once at startup. Fail loudly here instead. */
        try {
          document.querySelector(atc_button_selector);
        } catch (e) {
          console.warn(
            "helloextend-pdp-offers.js error: invalid add to cart button selector: ",
            atc_button_selector,
          );
          return;
        }

        /* Drop any interceptor left behind by a previous run. */
        if (removeAtcInterceptor) removeAtcInterceptor();

        let replaying = false;
        let pending = false;

        function onAddToCartClick(e) {
          const target = e.target;

          if (!target || typeof target.closest !== "function") return;

          const btn = target.closest(atc_button_selector);

          if (!btn) return;

          /* The click replayed below — let it run its normal course. */
          if (replaying) return;

          if (!belongsToThisProduct(btn)) return;
          if (btn.disabled || btn.classList.contains("disabled")) return;

          e.preventDefault();
          e.stopPropagation();

          /* A decision is already in flight — the shopper clicked again
                   while the modal was open, or before the plan request came
                   back. Swallow the click instead of opening a second modal
                   and adding the plan twice. */
          if (pending) return;

          pending = true;

          /* Resume on failure as well as success: a plan request that
                   fails must not leave the shopper with a dead button. */
          getPlanDecision().then(submitPlan).then(replayClick, replayClick);

          function replayClick() {
            pending = false;

            /* btn.click() dispatches synchronously, so every handler
                       has run by the time it returns. Clearing the flag here
                       rather than on re-entry keeps it correct even when a
                       theme's own capture listener stops propagation first and
                       this handler never sees the replayed click. */
            replaying = true;
            try {
              btn.click();
            } finally {
              replaying = false;
            }
          }
        }

        window.addEventListener("click", onAddToCartClick, true);

        removeAtcInterceptor = function () {
          window.removeEventListener("click", onAddToCartClick, true);
          removeAtcInterceptor = null;
        };
      }

      /**
       * Render or re-target the Extend offer for the selected variation.
       *
       * @param {number|string} variationId    Variation to cover.
       * @param {number}        variationPrice Variation price, in cents.
       */
      function showVariationOffer(variationId, variationPrice) {
        if (!variationId) return;

        const component = Extend.buttons.instance(".helloextend-offer");

        if (component) {
          Extend.setActiveProduct(".helloextend-offer", {
            referenceId: variationId,
            price: variationPrice,
            category: first_category,
          });
          return;
        }

        Extend.buttons.render(".helloextend-offer", {
          referenceId: variationId,
          price: variationPrice,
          category: first_category,
        });
      }

      /**
       * Track variation selection in the block add to cart.
       *
       * woocommerce/add-to-cart-with-options replaces the classic variation
       * form and fires no event on selection — its state lives in
       * Interactivity API stores WooCommerce registers as private. The hidden
       * input[name="variation_id"] it renders so the form still POSTs without
       * JS is the one piece of public markup that carries the resolved
       * variation, and the Interactivity runtime keeps its value attribute in
       * sync, so observing that attribute tracks the selection without
       * touching a store WooCommerce says will break between releases.
       *
       * The input carries an ID only, so prices come from the table built in
       * class-helloextend-protection-pdp-offer.php.
       */
      function watchBlockVariationSelector() {
        const input = document.querySelector('input[name="variation_id"]');

        /* Classic variation form — show_variation above already covers it. */
        if (!input) return;

        let lastVariationId = null;

        function syncOffer() {
          const variationId = input.value;

          /* The input renders with "0" until the shopper's attribute
                     choices resolve to a variation. That is a non-empty string,
                     so it has to be rejected by name or every unresolved state
                     warns about a variation that was never selected. */
          if (!variationId || variationId === "0") return;

          if (variationId === lastVariationId) return;

          const variationPrice = variations[variationId];

          if (variationPrice === undefined) {
            console.warn(
              "helloextend-pdp-offers.js error: no price for variation: ",
              variationId,
            );
            return;
          }

          lastVariationId = variationId;
          showVariationOffer(variationId, variationPrice);
        }

        /* A default or autoselected attribute can resolve a variation
               before any interaction, so read the current value up front. */
        syncOffer();

        new MutationObserver(syncOffer).observe(input, {
          attributes: true,
          attributeFilter: ["value"],
        });
      }

      if (product_type === "simple") {
        Extend.buttons.render(".helloextend-offer", {
          referenceId: reference_id,
          price: price,
          category: first_category,
        });

        handleAddToCartLogic();
      } else if (product_type === "variable") {
        $(".single_variation_wrap").on(
          "show_variation",
          function (event, variation) {
            showVariationOffer(
              variation.variation_id,
              Math.round(variation.display_price * 100),
            );
          },
        );

        watchBlockVariationSelector();

        handleAddToCartLogic();
      } else if (product_type === "composite") {
        Extend.buttons.render(".helloextend-offer", {
          referenceId: reference_id,
          price: price,
          category: first_category,
        });

        handleAddToCartLogic();

        // These two variables need to be settings in the plugin
        let compositeProductOptionsSelector = ".dd-option";
        let priceSelector = ".summary > .price > .woocommerce-Price-amount";

        $(compositeProductOptionsSelector).on("click", function () {
          const compositeProductPrice =
            parseFloat($(priceSelector).text().replace("$", "")) * 100;

          Extend.setActiveProduct(".helloextend-offer", {
            referenceId: reference_id,
            price: compositeProductPrice,
            category: first_category,
          });
        });
      } else {
        console.warn(
          "helloextend-pdp-offers.js error: Unsupported product type: ",
          product_type,
        );
      }
    });
})(jQuery);
