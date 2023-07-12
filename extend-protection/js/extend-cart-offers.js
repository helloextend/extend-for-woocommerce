// This script is used to handle the rendering and functionality of Extend offers in a WooCommerce cart.

// Wait until the document is fully loaded before running the script.
jQuery(document).ready(function() {
    // Check if necessary objects (ExtendWooCommerce and ExtendCartIntegration) exist.
    // If not, stop the execution of the script.
    if(!ExtendWooCommerce || !ExtendCartIntegration) {
        return;
    }

    // For each cart item:
    jQuery('.cart_item').each(function(ix, val){
        // Find the title and image of the product.
        var title = jQuery(val).find('.product-name');
        var image = jQuery(val).find('.product-thumbnail')

        // If the title includes 'Extend Protection Plan', disable pointer events for the image.
        // This could be used to prevent clicking on the image.
        if(title.text().indexOf('Extend Protection Plan') > -1){
            image.css('pointer-events', 'none')
        }
    })

    // For each Extend offer in the cart:
    document.querySelectorAll('.cart-extend-offer').forEach(function(val, ix){
        // Get the reference ID and quantity of the covered item
        let ref_id =  val.dataset.covered;
        let qty = jQuery(val).parents('.cart_item').find('input.qty').val();
        let price = jQuery(val).parents('.cart_item').find('.product-price').text().trim().replace(/[$,\.]/g, '')
        let extendPrice = parseFloat(price * 100)

        // If the warranty is already in the cart or if Extend offers are disabled, stop processing this item.
        if(ExtendWooCommerce.warrantyAlreadyInCart(ref_id, window.ExtendCartIntegration.cart) || ExtendCartIntegration.extend_enable_cart_offers !== '1'){
            return;
        }

        Extend.buttons.renderSimpleOffer(val, {
            referenceId: ref_id,
            price: extendPrice,
            onAddToCart: function({ plan, product }) {

                // On adding to the cart, if both plan and product exist:
                if (plan && product) {
                   // ExtendWooCommerce.extendAjaxLog('1 - OnAddToCart simple offer call with :', 'notice')
                    ExtendWooCommerce.extendAjaxLog(plan, 'notice');
                    ExtendWooCommerce.extendAjaxLog(product.toString(), 'notice');

                    // Create a copy of the plan, adding the reference ID of the covered product.
                    var planCopy = { ...plan, covered_product_id: ref_id }

                    var data = {
                        quantity: qty,
                        plan: planCopy
                    };

                    // Add the plan to the cart.
                    ExtendWooCommerce.addPlanToCart(data)
                        .then(() => {
                            // After adding the plan, enable the 'Update cart' button and trigger a click on it.
                            jQuery("[name='update_cart']").removeAttr('disabled');
                            jQuery("[name='update_cart']").trigger("click");
                        })
                } else {
                    ExtendWooCommerce.extendAjaxLog('onAddToCart failed: plan or product missing', 'error');
                }
            },
        });
    })
});

// When the cart totals are updated, re-render the Extend offers.
jQuery(document.body).on('updated_cart_totals', function () {

    // If necessary objects (ExtendWooCommerce and ExtendCartIntegration) do not exist, stop the execution of the script.
    if (!ExtendWooCommerce || !ExtendCartIntegration) {
        return;
    }

    // Iterate over each element with class 'cart-extend-offer'
    jQuery('.cart-extend-offer').each(function (ix, val) {
        let ref_id = jQuery(val).data('covered'); // Get the 'covered' data attribute value
        let qty = jQuery(val).parents('.cart_item').find('input.qty').val(); // Get the quantity value from the corresponding input field
        let price = jQuery(val).parents('.cart_item').find('.product-price').text().trim().replace(/[$,\.]/g, '')
        let extendPrice = parseFloat(price) * 100




        // Check if an Extend button instance exists for the current element
        if (Extend.buttons.instance('#' + val.id)) {
            Extend.buttons.instance('#' + val.id).destroy(); // Destroy the existing Extend button instance
        }

        // Retrieve the cart data from ExtendWooCommerce
        ExtendWooCommerce.getCart()
            .then(cart => {

                // Check if the warranty is already in the cart or if Extend cart offers are disabled
                if (ExtendWooCommerce.warrantyAlreadyInCart(ref_id, cart) || ExtendCartIntegration.extend_cart_offers_enabled === 'no') {
                    return; // Skip further processing
                }

                /** initialize offer */

                // Render a simple offer using Extend.buttons.renderSimpleOffer()
                Extend.buttons.renderSimpleOffer(val, {
                    referenceId: ref_id,
                    onAddToCart: function ({ plan, product }) {

                        if (plan && product) {
                            ExtendWooCommerce.extendAjaxLog('2 - OnAddToCart simple offer call with :', 'notice')
                            ExtendWooCommerce.extendAjaxLog(plan.toString(), 'notice');
                            ExtendWooCommerce.extendAjaxLog(product.toString(), 'notice');

                            var planCopy = { ...plan, covered_product_id: ref_id }; // Create a copy of the plan object with the 'covered_product_id' property set to ref_id

                            var data = {
                                quantity: qty,
                                plan: planCopy
                            };

                            // Add the plan to the cart using ExtendWooCommerce.addPlanToCart()
                            ExtendWooCommerce.addPlanToCart(data)
                                .then(() => {
                                    // Enable the 'update_cart' button and trigger a click event
                                    jQuery("[name='update_cart']").removeAttr('disabled');
                                    jQuery("[name='update_cart']").trigger("click");
                                });
                        }
                    },
                });
            });
    });
});
